import makeWASocket, { useMultiFileAuthState, DisconnectReason, Browsers } from '@whiskeysockets/baileys';
import qrcode from 'qrcode-terminal';
import QRCode from 'qrcode';
import express from 'express';
import pino from 'pino';
import { readFileSync, readdirSync, rmSync } from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

// Load local .env without adding runtime dependency.
try {
  const envPath = path.join(path.dirname(fileURLToPath(import.meta.url)), '.env');
  const envFile = readFileSync(envPath, 'utf8');
  for (const line of envFile.split(/\r?\n/)) {
    const match = line.match(/^\s*([^#=]+?)\s*=\s*(.*?)\s*$/);
    if (match && process.env[match[1]] === undefined) {
      process.env[match[1]] = match[2].replace(/^['"]|['"]$/g, '');
    }
  }
} catch {
  // Environment variables may be supplied by the process manager.
}

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const logger = pino({ level: process.env.LOG_LEVEL || 'info' });

const PORT = process.env.WA_PORT || 3001;
const WA_GATEWAY_TOKEN = process.env.WA_GATEWAY_TOKEN;
if (!WA_GATEWAY_TOKEN || WA_GATEWAY_TOKEN === 'default-token-change-me') {
  logger.fatal('WA_GATEWAY_TOKEN kosong atau masih default. Isi token yang kuat di .env, lalu jalankan ulang.');
  process.exit(1);
}
const WA_MAX_PER_HARI = parseInt(process.env.WA_MAX_PER_HARI || '40', 10);

let sock = null;
let qrShown = false;
let currentQr = null;
const messageQueue = [];
let messageSentToday = 0;
let lastResetDate = new Date().toDateString();

const app = express();
app.use(express.json());

// Middleware: autentikasi (endpoint publik: /, /status, /health). /qr dan /pair memakai token lewat header Bearer atau ?token=.
app.use((req, res, next) => {
  const publicPaths = ['/', '/status', '/health'];
  if (publicPaths.includes(req.path)) {
    return next();
  }
  const auth = req.headers.authorization;
  const viaQuery = req.path === '/qr' || req.path === '/pair'; // dibuka dari browser, tidak bisa kirim header
  if (!(auth && auth.startsWith('Bearer ')) && !viaQuery) {
    return res.status(401).json({ error: 'Unauthorized' });
  }
  const token = auth && auth.startsWith('Bearer ') ? auth.slice(7) : req.query.token;
  if (token !== WA_GATEWAY_TOKEN) {
    return res.status(401).json({ error: 'Invalid token' });
  }
  next();
});

// Root: otomatis arahkan ke /qr
app.get('/', (req, res) => {
  res.redirect('/qr' + req.url.slice(1)); // pertahankan ?token=
});

// Utility: mask nomor (samarkan 4 digit tengah)
const maskPhone = (phone) => {
  if (phone.length < 8) return phone;
  const start = phone.slice(0, 2);
  const end = phone.slice(-2);
  return `${start}****${end}`;
};

// Utility: normalisasi nomor Indonesia
const normalizePhoneNumber = (phone) => {
  phone = phone.replace(/\D/g, '');
  
  if (phone.startsWith('08')) {
    phone = '62' + phone.slice(1);
  } else if (phone.startsWith('0')) {
    phone = '62' + phone.slice(1);
  } else if (!phone.startsWith('62')) {
    phone = '62' + phone;
  }
  
  return phone;
};

// Utility: reset counter harian
const resetDailyCounter = () => {
  const today = new Date().toDateString();
  if (today !== lastResetDate) {
    messageSentToday = 0;
    lastResetDate = today;
  }
};

// Process queue pesan dengan jeda random
let sedangJalan = false;
const processMessageQueue = async () => {
  if (sedangJalan) return; // tanpa ini setInterval menjalankan loop paralel dan jeda antar-pesan hilang
  sedangJalan = true;
  try {
  while (messageQueue.length > 0 && sock?.user) {
    resetDailyCounter();
    
    if (messageSentToday >= WA_MAX_PER_HARI) {
      logger.warn('Daily limit reached');
      break;
    }
    
    const { to, message, resolve, reject } = messageQueue.shift();
    
    try {
      const jid = to.includes('@') ? to : `${to}@s.whatsapp.net`;
      
      // Cek nomor terdaftar
      const [result] = await sock.onWhatsApp(to);
      if (!result?.exists) {
        logger.warn(`Number ${maskPhone(to)} not registered`);
        reject({ status: 422, error: 'Number not registered' });
        continue;
      }
      
      await sock.sendMessage(jid, { text: message });
      messageSentToday++;
      logger.info(`Message sent to ${maskPhone(to)}`);
      resolve({ success: true });
    } catch (err) {
      logger.error(`Failed to send to ${maskPhone(to)}: ${err.message}`);
      reject({ status: 500, error: err.message });
    }
    
    // Jeda acak 4-9 detik sebelum pesan berikutnya
    const delay = Math.random() * 5000 + 4000;
    await new Promise(resolve => setTimeout(resolve, delay));
  }
  } finally {
    sedangJalan = false;
  }
};

// Jalankan queue processor setiap 100ms
setInterval(processMessageQueue, 100);

// Inisialisasi Baileys
const initializeBaileys = async () => {
  try {
    const { state, saveCreds } = await useMultiFileAuthState(
      path.join(__dirname, 'auth_info')
    );
    
    sock = makeWASocket({
      auth: state,
      browser: Browsers.ubuntu('Chrome'),
      printQRInTerminal: false,
      logger: pino({ level: 'silent' }),
      shouldSyncHistoryMessage: () => false,
      syncFullHistory: false
    });
    
    // Event: connection update
    sock.ev.on('connection.update', async (update) => {
      const { connection, lastDisconnect, qr } = update;
      
      if (qr) {
        currentQr = qr;
        qrShown = false;
        qrcode.generate(qr, { small: true });
        QRCode.toFile(path.join(__dirname, 'qr.png'), qr).catch(() => {});
      }
      
      if (connection === 'open') {
        currentQr = null;
        qrShown = true;
        logger.info(`Connected as ${sock.user?.id}`);
      } else if (connection === 'close') {
        const statusCode = lastDisconnect?.error?.output?.statusCode;
        logger.warn(`Connection closed with status: ${statusCode}`);
        if (statusCode === DisconnectReason.loggedOut) {
          logger.info('Logged out. Clearing auth_info and restarting...');
          try {
            const files = readdirSync(path.join(__dirname, 'auth_info'));
            for (const f of files) {
              rmSync(path.join(__dirname, 'auth_info', f), { recursive: true, force: true });
            }
          } catch {}
          currentQr = null;
          sock = null;
          setTimeout(() => initializeBaileys(), 2000);
        } else {
          logger.warn('Reconnecting...');
          setTimeout(() => initializeBaileys(), 3000);
        }
      }
    });
    
    // Event: credentials update
    sock.ev.on('creds.update', saveCreds);
  } catch (err) {
    logger.error(`Baileys init error: ${err.message}`);
    setTimeout(() => initializeBaileys(), 3000);
  }
};

// Endpoint: status
app.get('/status', (req, res) => {
  const connected = sock?.user ? true : false;
  const nomor = sock?.user?.id?.split(':')[0] || null;
  res.json({ connected, nomor });
});

// Endpoint: send pesan
app.post('/send', (req, res) => {
  const { to, message } = req.body;
  
  if (!to || !message) {
    return res.status(400).json({ error: 'Missing to or message' });
  }
  
  if (!sock?.user) {
    return res.status(503).json({ error: 'Gateway not connected' });
  }
  
  resetDailyCounter();
  
  if (messageSentToday >= WA_MAX_PER_HARI) {
    return res.status(429).json({ error: 'Daily limit exceeded' });
  }
  
  const normalizedTo = normalizePhoneNumber(to);
  
  const promise = new Promise((resolve, reject) => {
    messageQueue.push({ to: normalizedTo, message, resolve, reject });
  });
  
  promise
    .then((result) => {
      res.json(result);
    })
    .catch((err) => {
      res.status(err.status || 500).json({ error: err.error });
    });
});

// Health check
app.get('/health', (req, res) => {
  res.json({ ok: true });
});

// Endpoint Pairing Code (tautkan dengan nomor telepon)
app.get('/pair', async (req, res) => {
  if (sock?.user) {
    return res.json({ connected: true, nomor: sock.user.id.split(':')[0] });
  }
  const cleanPhone = String(req.query.phone || process.env.WA_PAIR_PHONE || '').replace(/\D/g, '');
  if (!cleanPhone) {
    return res.status(400).json({ error: 'Isi ?phone= atau WA_PAIR_PHONE' });
  }
  try {
    const code = await sock.requestPairingCode(cleanPhone);
    res.json({ success: true, nomor: cleanPhone, code });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// Endpoint untuk scan QR di browser
app.get('/qr', async (req, res) => {
  if (sock?.user) {
    const nomor = sock.user.id.split(':')[0];
    return res.send(`
      <!DOCTYPE html>
      <html>
      <head><title>WhatsApp Gateway - Terhubung</title></head>
      <body style="display:flex;align-items:center;justify-content:center;height:90vh;font-family:sans-serif;background:#f0f2f5;">
        <div style="background:white;padding:36px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.1);text-align:center;">
          <h2 style="color:#25d366;margin:0 0 10px;">WhatsApp Gateway Terhubung!</h2>
          <p style="color:#333;font-size:18px;">Nomor Pengirim: <b>${nomor}</b></p>
          <p style="color:#777;font-size:14px;">Gateway siap mengirim notifikasi.</p>
        </div>
      </body>
      </html>
    `);
  }

  if (!currentQr) {
    return res.send(`
      <!DOCTYPE html>
      <html>
      <head><title>Menunggu QR WhatsApp</title><meta http-equiv="refresh" content="2"></head>
      <body style="display:flex;align-items:center;justify-content:center;height:90vh;font-family:sans-serif;background:#f0f2f5;">
        <div style="background:white;padding:36px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.1);text-align:center;">
          <h2 style="color:#555;">Menunggu QR Code dari WhatsApp...</h2>
          <p style="color:#888;">Halaman otomatis merefresh dalam 2 detik.</p>
        </div>
      </body>
      </html>
    `);
  }

  try {
    const dataUrl = await QRCode.toDataURL(currentQr, { width: 320, margin: 2 });
    res.send(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Scan QR WhatsApp Gateway</title>
        <meta http-equiv="refresh" content="6">
      </head>
      <body style="display:flex;align-items:center;justify-content:center;height:90vh;font-family:sans-serif;background:#f0f2f5;">
        <div style="background:white;padding:32px;border-radius:12px;box-shadow:0 4px 14px rgba(0,0,0,0.12);text-align:center;max-width:400px;">
          <h2 style="color:#075e54;margin:0 0 8px;">Scan QR WhatsApp</h2>
          <p style="color:#444;font-size:14px;margin-bottom:16px;">
            1. Buka WhatsApp di HP pengirim<br>
            2. Menu (titik tiga) &rarr; <b>Perangkat Tertaut</b><br>
            3. Pilih <b>Tautkan Perangkat</b> dan scan kode ini
          </p>
          <img src="${dataUrl}" alt="QR Code WhatsApp" style="width:280px;height:280px;border-radius:8px;border:1px solid #ddd;" />
          <p style="margin-top:14px;margin-bottom:6px;">
            <a href="/pair?token=${encodeURIComponent(String(req.query.token || ''))}" target="_blank" style="color:#075e54;font-size:13px;text-decoration:underline;font-weight:bold;">
              Atau klik di sini untuk pakai Kode Tautan (Pairing Code)
            </a>
          </p>
          <p style="color:#999;font-size:12px;margin:12px 0 0;">
            Halaman ini otomatis merefresh saat QR diperbarui atau setelah berhasil tersambung.
          </p>
        </div>
      </body>
      </html>
    `);
  } catch (err) {
    res.status(500).send('Error generating QR: ' + err.message);
  }
});

// Start server
app.listen(PORT, '127.0.0.1', () => {
  logger.info(`WA Gateway listening on 127.0.0.1:${PORT}`);
  initializeBaileys();
});
