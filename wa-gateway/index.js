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

// Tangani unhandled rejection / exception agar gateway tidak crash saat socket terputus/reconnect
process.on('unhandledRejection', (reason) => {
  logger.warn({ reason: reason?.message || reason }, 'Baileys Unhandled Rejection (diabaikan agar tetap online)');
});

process.on('uncaughtException', (err) => {
  logger.warn({ err: err.message }, 'Baileys Uncaught Exception (diabaikan agar tetap online)');
});

const PORT = process.env.WA_PORT || 3001;
const WA_GATEWAY_TOKEN = process.env.WA_GATEWAY_TOKEN;
if (!WA_GATEWAY_TOKEN || WA_GATEWAY_TOKEN === 'default-token-change-me') {
  logger.fatal('WA_GATEWAY_TOKEN kosong atau masih default. Isi token yang kuat di .env, lalu jalankan ulang.');
  process.exit(1);
}
const WA_MAX_PER_HARI = parseInt(process.env.WA_MAX_PER_HARI || '40', 10);
const WA_MAX_PER_JAM = parseInt(process.env.WA_MAX_PER_JAM || '15', 10);
const WA_MIN_DELAY = parseInt(process.env.WA_MIN_DELAY || '4000', 10);
const WA_MAX_DELAY = parseInt(process.env.WA_MAX_DELAY || '9000', 10);
const WA_RECIPIENT_COOLDOWN_SEC = parseInt(process.env.WA_RECIPIENT_COOLDOWN_SEC || '300', 10);

let sock = null;
let qrShown = false;
let currentQr = null;
const messageQueue = [];
let messageSentToday = 0;
let lastResetDate = new Date().toDateString();

let messageSentThisHour = 0;
let lastResetHour = new Date().getHours();
const lastSentPerRecipient = new Map();
const invalidNumbersCache = new Set();

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

// Utility: reset counter harian & jam
const resetDailyCounter = () => {
  const today = new Date().toDateString();
  if (today !== lastResetDate) {
    messageSentToday = 0;
    lastResetDate = today;
    lastSentPerRecipient.clear();
    invalidNumbersCache.clear();
  }
};

const resetHourlyCounter = () => {
  const now = new Date();
  const currentHour = now.getHours();
  const currentDate = now.toDateString();
  if (currentHour !== lastResetHour || currentDate !== lastResetDate) {
    messageSentThisHour = 0;
    lastResetHour = currentHour;
  }
};

// Process queue pesan dengan simulasi perilaku manusiawi (human presence, typing, jeda acak)
let sedangJalan = false;
const processMessageQueue = async () => {
  if (sedangJalan) return; // tanpa ini setInterval menjalankan loop paralel dan jeda antar-pesan hilang
  sedangJalan = true;
  try {
  while (messageQueue.length > 0 && sock?.user) {
    resetDailyCounter();
    resetHourlyCounter();
    
    if (messageSentToday >= WA_MAX_PER_HARI) {
      logger.warn('Anti-Spam Filter: Batas harian tercapai (' + WA_MAX_PER_HARI + '/hari)');
      break;
    }

    if (messageSentThisHour >= WA_MAX_PER_JAM) {
      logger.warn('Anti-Spam Filter: Batas per jam tercapai (' + WA_MAX_PER_JAM + '/jam)');
      break;
    }
    
    const { to, message, resolve, reject } = messageQueue.shift();
    
    try {
      const jid = to.includes('@') ? to : `${to}@s.whatsapp.net`;
      
      // 1. Cek cache nomor tidak terdaftar untuk kurangi query ke server WA
      if (invalidNumbersCache.has(to)) {
        logger.warn(`Anti-Spam Filter: Nomor ${maskPhone(to)} terdeteksi tidak terdaftar (cached)`);
        reject({ status: 422, error: 'Number not registered' });
        continue;
      }

      // Cek nomor terdaftar di WhatsApp
      const [result] = await sock.onWhatsApp(to);
      if (!result?.exists) {
        invalidNumbersCache.add(to);
        logger.warn(`Number ${maskPhone(to)} not registered on WhatsApp`);
        reject({ status: 422, error: 'Number not registered' });
        continue;
      }
      
      // 2. Simulasi Human Emulation (Presence & Typing) agar tidak dicap bot spam
      try {
        await sock.presenceSubscribe(jid);
        await sock.sendPresenceUpdate('composing', jid);

        // Durasi mengetik wajar proporsional dengan panjang pesan (1.2 - 2.5 detik)
        const typingDuration = Math.min(Math.max((message.length || 0) * 12, 1200), 2500);
        await new Promise((r) => setTimeout(r, typingDuration));

        await sock.sendPresenceUpdate('paused', jid);
      } catch (presenceErr) {
        // Abaikan error pada presence, lanjutkan kirim
      }

      await sock.sendMessage(jid, { text: message });
      messageSentToday++;
      messageSentThisHour++;
      lastSentPerRecipient.set(to, Date.now());

      logger.info(`Message sent to ${maskPhone(to)} (Hari ini: ${messageSentToday}/${WA_MAX_PER_HARI}, Jam ini: ${messageSentThisHour}/${WA_MAX_PER_JAM})`);
      resolve({ success: true });
    } catch (err) {
      logger.error(`Failed to send to ${maskPhone(to)}: ${err.message}`);
      reject({ status: 500, error: err.message });
    }
    
    // Jeda acak (jitter) antar pesan (default 4-9 detik) untuk mencegah deteksi frekuensi konstan bot
    const minDelay = Math.max(WA_MIN_DELAY, 2000);
    const maxDelay = Math.max(WA_MAX_DELAY, minDelay + 1000);
    const delay = Math.floor(Math.random() * (maxDelay - minDelay + 1)) + minDelay;
    logger.debug(`Anti-Spam Delay: Menunggu ${(delay / 1000).toFixed(1)}s sebelum pesan berikutnya...`);
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

// Endpoint: status dengan info anti-spam
app.get('/status', (req, res) => {
  resetDailyCounter();
  resetHourlyCounter();
  const connected = sock?.user ? true : false;
  const nomor = sock?.user?.id?.split(':')[0] || null;
  res.json({
    connected,
    nomor,
    anti_spam: {
      sent_today: messageSentToday,
      max_per_hari: WA_MAX_PER_HARI,
      sent_this_hour: messageSentThisHour,
      max_per_jam: WA_MAX_PER_JAM,
      min_delay_ms: WA_MIN_DELAY,
      max_delay_ms: WA_MAX_DELAY,
      recipient_cooldown_sec: WA_RECIPIENT_COOLDOWN_SEC,
      cached_invalid_numbers: invalidNumbersCache.size,
      queue_length: messageQueue.length,
    },
  });
});

// Endpoint: send pesan dengan validasi filter anti-spam
app.post('/send', (req, res) => {
  const { to, message } = req.body;
  
  if (!to || !message) {
    return res.status(400).json({ error: 'Missing to or message' });
  }

  if (typeof message !== 'string' || message.trim().length === 0) {
    return res.status(400).json({ error: 'Message cannot be empty' });
  }

  if (message.length > 2500) {
    return res.status(400).json({ error: 'Message is too long (maximum 2500 characters)' });
  }
  
  if (!sock?.user) {
    return res.status(503).json({ error: 'Gateway not connected' });
  }
  
  resetDailyCounter();
  resetHourlyCounter();
  
  if (messageSentToday >= WA_MAX_PER_HARI) {
    return res.status(429).json({ error: 'Daily limit exceeded' });
  }

  if (messageSentThisHour >= WA_MAX_PER_JAM) {
    return res.status(429).json({ error: 'Hourly limit exceeded. Anti-spam throttle active.' });
  }
  
  const normalizedTo = normalizePhoneNumber(to);

  // Anti-Spam: Cek apakah nomor ada di cache nomor tidak terdaftar
  if (invalidNumbersCache.has(normalizedTo)) {
    return res.status(422).json({ error: 'Number not registered' });
  }

  // Anti-Spam: Cek cooldown per penerima
  if (lastSentPerRecipient.has(normalizedTo)) {
    const elapsedSec = (Date.now() - lastSentPerRecipient.get(normalizedTo)) / 1000;
    if (elapsedSec < WA_RECIPIENT_COOLDOWN_SEC) {
      const waitRemaining = Math.ceil(WA_RECIPIENT_COOLDOWN_SEC - elapsedSec);
      return res.status(429).json({
        error: `Nomor masih dalam masa cooldown anti-spam. Harap tunggu ${waitRemaining} detik lagi.`,
      });
    }
  }
  
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
