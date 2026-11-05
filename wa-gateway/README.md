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

## Fitur

- **Auto-reconnect**: Reconnect otomatis bila koneksi terputus (kecuali logout manual)
- **Rate limiting**: Jeda acak 4-9 detik antar pesan, maksimal 40 pesan/hari
- **Queue system**: Satu pesan per kali, diproses berurutan
- **Validasi nomor**: Cek nomor terdaftar sebelum kirim
- **Privacy**: Nomor di log disamarkan (4 digit tengah)

## Troubleshooting

### QR tidak muncul
- Pastikan tidak ada session lama di folder `auth_info/`
- Hapus folder `auth_info/` dan restart

### "Logged out"
Scan ulang QR code. Session dihapus otomatis.

### Daily limit tercapai
Counter reset otomatis setiap hari. Naikkan `WA_MAX_PER_HARI` jika perlu.
