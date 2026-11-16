# WA Gateway

Service WhatsApp Gateway untuk SIARKA menggunakan Baileys.

## Instalasi

```bash
cd wa-gateway
npm install
```

## Konfigurasi

Salin `.env.example` ke `.env` dan sesuaikan:

```bash
cp .env.example .env
```

Variabel environment:
- `WA_PORT`: Port server (default 3001)
- `WA_GATEWAY_TOKEN`: Token autentikasi untuk semua endpoint
- `WA_MAX_PER_HARI`: Batas maksimal pesan per hari (default 40)
- `WA_MAX_PER_JAM`: Batas maksimal pesan per jam (default 15) untuk mencegah lonjakan/burst
- `WA_MIN_DELAY`: Jeda minimum antar-pesan dalam ms (default 4000)
- `WA_MAX_DELAY`: Jeda maksimum antar-pesan dalam ms (default 9000)
- `WA_RECIPIENT_COOLDOWN_SEC`: Cooldown anti-flood per nomor penerima dalam detik (default 300)

## Scan QR Code

Jalankan service:

```bash
node index.js
```

QR code akan muncul di terminal. Scan dengan WhatsApp:
1. Buka WhatsApp di HP
2. Menu → Perangkat Tertaut
3. Tautkan Perangkat
4. Scan QR code yang muncul di terminal

## Penggunaan

### Status Koneksi

```bash
curl -H "Authorization: Bearer your-token" \
  http://127.0.0.1:3001/status
```

Response:
```json
{
  "connected": true,
  "nomor": "628123456789"
}
```

### Kirim Pesan

```bash
curl -X POST http://127.0.0.1:3001/send \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "08123456789",
    "message": "Halo, ini pesan dari gateway"
  }'
```

Format nomor yang didukung:
- `08123456789`
- `8123456789`
- `+628123456789`
- `628123456789`

Response sukses:
```json
{
  "success": true
}
```

Response error:
- `401`: Token tidak valid
- `422`: Nomor tidak terdaftar di WhatsApp
- `429`: Batas harian tercapai
- `503`: Gateway belum terkoneksi

## Fitur Anti-Spam & Anti-Ban

- **Human Presence Emulation**: Simulasi kehadiran manusiawi dengan mengirim sinyal presence `composing` (mengetik) selama 1.2 - 2.5 detik sebelum pesan dikirim.
- **Dynamic Jitter & Delay**: Jeda acak 4-9 detik (dapat diatur) antar pengiriman pesan untuk menghindari pola frekuensi konstan bot.
- **Rate Limiting Bertingkat**: Pembatasan harian (default 40 pesan/hari) dan per jam (default 15 pesan/jam) untuk mencegah pengiriman serentak/burst.
- **Recipient Cooldown**: Mencegah spam berulang ke nomor penerima yang sama dalam interval singkat (default 300 detik).
- **Negative Cache Nomor**: Nomor yang tidak terdaftar di WhatsApp disimpan dalam cache agar tidak membebani pengecekan berulang ke server WhatsApp.
- **Queue System**: Antrean pesan terurut (satu pesan per siklus).
- **Privacy & Masking**: Nomor HP pada log disamarkan untuk privasi pengguna.

## Troubleshooting

### QR tidak muncul
- Pastikan tidak ada session lama di folder `auth_info/`
- Hapus folder `auth_info/` dan restart

### "Logged out"
Scan ulang QR code. Session dihapus otomatis.

### Daily limit tercapai
Counter reset otomatis setiap hari. Naikkan `WA_MAX_PER_HARI` jika perlu.
