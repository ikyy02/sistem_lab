# SIARKA (Sistem Informasi Laboratorium)

## Deploy

1. `.env` produksi: salin `.env.example` (sudah `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=warning`), lalu isi `APP_KEY` (`php artisan key:generate`), `APP_URL`, DB, MAIL, `ADMIN_PASSWORD` (dipakai `db:seed` untuk akun admin), dan `WA_GATEWAY_TOKEN`.
2. `composer install --no-dev --optimize-autoloader`, lalu `php artisan migrate --force` dan `php artisan storage:link`.
3. Worker antrean (notifikasi email/WA lewat queue `database`): jalankan `php artisan queue:work --tries=3` di bawah supervisor atau systemd supaya hidup lagi bila mati. Setelah deploy: `php artisan queue:restart`.
4. Scheduler (status selesai otomatis, pengingat, WA terlambat): satu baris cron:
   ```
   * * * * * cd /path/ke/sistem_lab && php artisan schedule:run >> /dev/null 2>&1
   ```
5. WA gateway: di `wa-gateway/` jalankan `npm ci`, isi `.env` (`WA_GATEWAY_TOKEN` sama dengan Laravel, `WA_PORT`), lalu `pm2 start index.js --name wa-gateway` (atau unit systemd). Gateway berhenti bila token kosong/default. Tautkan WhatsApp lewat `/qr?token=<WA_GATEWAY_TOKEN>` atau `/pair?token=...&phone=<nomor>` (atau env `WA_PAIR_PHONE`).

Catatan migrasi: jangan ganti nama migrasi yang sudah ada. Migrasi baru harus bertanggal setelah `2026_12_10`.

## WhatsApp Gateway - Pengingat Keterlambatan

### Setup

1. Jalankan wa-gateway (lihat `wa-gateway/README.md`):
```bash
cd wa-gateway
npm install
node index.js
```
Scan QR dengan WhatsApp HP, lalu pastikan gateway terhubung:
```bash
curl -H "Authorization: Bearer <WA_GATEWAY_TOKEN>" http://127.0.0.1:3001/status
```

2. Konfigurasi `.env`:
```env
WA_ENABLED=true
WA_GATEWAY_TOKEN=secret-token-anda
WA_GATEWAY_URL=http://127.0.0.1:3001
WA_JAM_MULAI=08:00
WA_JAM_SELESAI=16:00
WA_MAKS_PENGINGAT=3
```
Jalankan `php artisan config:clear` setelah mengubah `.env`. Jam operasional mengikuti timezone aplikasi Laravel
(`config('app.timezone')`, default UTC), jadi sesuaikan timezone aplikasi atau nilai `WA_JAM_*`.

3. Pastikan tabel log sudah dibuat:
```bash
php artisan migrate
```

### Test otomatis

```bash
php artisan test tests/Feature/WhatsAppServiceTest.php tests/Feature/WaKirimTerlambatTest.php --no-coverage
```

Test memakai `Http::fake()`, sehingga gateway WhatsApp tidak perlu dijalankan. Cakupan test:

- `WhatsAppService`: kirim sukses, gateway mati (`ConnectionException` tidak dilempar), respons gateway gagal,
  nomor kosong/invalid dilewati tanpa request, normalisasi nomor.
- `wa:kirim-terlambat`: tidak mengirim ganda untuk `(id_peminjaman, ke_ke)` yang sama, berhenti setelah 3
  pengingat, tidak mengirim di luar jam operasional, melewati peminjaman berstatus `selesai`, peminjaman yang
  sudah dikembalikan, peminjaman yang melibatkan ruangan, dan peminjaman tanpa nomor WhatsApp; nomor tujuan
  diambil dari isian saat pengajuan (fallback nomor profil); `--dry-run` tidak mengirim dan tidak menulis
  `wa_logs`.
- Isi notifikasi WA: nomor tujuan ternormalisasi, nama peminjam (mahasiswa/dosen), ID peminjaman, barang,
  batas pengembalian, jumlah hari terlambat, dan tanda `SIARKA`; kegagalan gateway tetap dicatat `gagal`
  tanpa melempar exception.

### Uji manual

Nomor gateway `085751401610` adalah **pengirim** pesan (nomor yang di-scan ke Baileys). **Penerima** adalah nomor
yang diisi mahasiswa pada form Ajukan Peminjaman (otomatis terisi dari profil dan boleh diubah); gunakan nomor
WhatsApp lain yang aktif dan berbeda dari nomor gateway. Nomor itulah yang dipakai `wa:kirim-terlambat`. Untuk
pengujian, gunakan akun mahasiswa lain (bukan pemilik nomor gateway) agar alurnya sama seperti produksi.

1. Siapkan akun dan katalog uji:
```bash
php artisan db:seed --class=NotifikasiUjiSeeder
php artisan tinker
```
```php
$mhs = \App\Models\Mahasiswa::where('nim', '26990001')->firstOrFail();
$mhs->update(['no_whatsapp' => '08xxxxxxxxxx']); // nomor penerima (WA lain), bukan 085751401610
$alat = \App\Models\AlatBahan::where('nama', 'Alat Uji Notifikasi')->firstOrFail();

$p = \App\Models\Peminjaman::create([
    'email_peminjam' => $mhs->email,
    'no_whatsapp' => '08xxxxxxxxxx', // nomor penerima pengingat (WA lain), bukan nomor gateway
    'jenis_peminjaman' => 'pribadi',
    'status' => 'disetujui',
    'tanggal_pengajuan' => now()->subDays(10),
    'tanggal_peminjaman' => now()->subDays(10),
    'tanggal_rencana_kembali' => now()->subDays(5),
]);
$p->details()->create(['id_katalog' => $alat->id_katalog, 'jumlah' => 1]);
echo "ID peminjaman: {$p->id_peminjaman}\n";
```
Catat `id_peminjaman` yang tampil. Hapus log lama bila mengulang uji:
```php
\App\Models\WaLog::where('id_peminjaman', <id_peminjaman>)->delete();
```

2. Uji `--dry-run` (tidak mengirim dan tidak menulis log):
```bash
php artisan wa:kirim-terlambat --dry-run
```
Output berisi daftar peminjaman yang akan diproses. Tidak boleh ada pesan masuk dan tidak ada baris baru di
`wa_logs`.

3. Uji pengiriman nyata (jalankan pada jam operasional, mis. 08:00-16:00):
```bash
php artisan wa:kirim-terlambat
```
Pesan harus masuk ke WhatsApp tujuan dan `wa_logs` bertambah satu baris dengan `ke_ke = 1` dan
`status = terkirim`. Periksa isi pesan: nama peminjam, ID peminjaman, barang, batas pengembalian, jumlah hari
terlambat, dan tanda `SIARKA`.

4. Uji tidak mengirim ganda:
```bash
php artisan wa:kirim-terlambat
```
Output `Selesai: 0 pengingat diproses.` dan jumlah baris `wa_logs` untuk `ke_ke = 1` tetap satu. Pengingat
berikutnya baru dikirim setelah 24 jam.

5. Uji batas 3 pengingat (percepat dengan memundurkan `dikirim_at`):
```bash
php artisan tinker
```
```php
\App\Models\WaLog::where('id_peminjaman', <id_peminjaman>)->update(['dikirim_at' => now()->subDay()]);
```
Jalankan `php artisan wa:kirim-terlambat`, ulangi langkah ini sampai `ke_ke = 3`. Setelah itu, memundurkan
waktu dan menjalankan perintah lagi tidak boleh membuat `ke_ke = 4`.

6. Uji di luar jam operasional. Sementara ubah `.env` menjadi:
```env
WA_JAM_MULAI=00:00
WA_JAM_SELESAI=00:01
```
Jalankan `php artisan config:clear` lalu `php artisan wa:kirim-terlambat`. Output harus
`Diluar jam operasional (00:00-00:01). Berhenti.` dan tidak ada log/pesan baru. Kembalikan nilai `.env` setelah
selesai.

7. Uji peminjaman berstatus selesai:
```php
\App\Models\Peminjaman::findOrFail(<id_peminjaman>)->update(['status' => 'selesai']);
```
Jalankan `php artisan wa:kirim-terlambat`; tidak boleh ada log/pesan baru.

8. Uji peminjaman yang sudah dikembalikan (status masih `disetujui`):
```php
$p = \App\Models\Peminjaman::findOrFail(<id_peminjaman>);
$lab = \App\Models\Laboran::firstOrFail();
$pg = \App\Models\Pengembalian::create([
    'id_peminjaman' => $p->id_peminjaman,
    'tanggal_pengembalian' => now(),
    'email_penerima' => $lab->email,
]);
foreach ($p->details as $d) {
    \App\Models\PengembalianDetail::create([
        'id_pengembalian' => $pg->id_pengembalian,
        'id_detail_peminjaman' => $d->id_detail,
        'jumlah_dikembalikan' => $d->jumlah,
        'kondisi' => 'baik',
    ]);
}
$p->update(['status' => 'disetujui']);
\App\Models\WaLog::where('id_peminjaman', $p->id_peminjaman)->delete();
```
Jalankan `php artisan wa:kirim-terlambat`; tidak boleh ada log/pesan baru karena seluruh alat sudah kembali.

9. Periksa isi log:
```sql
SELECT id_peminjaman, ke_ke, nomor, status, dikirim_at
FROM wa_logs ORDER BY id DESC LIMIT 10;
```
Nomor pada log adalah nomor asli; nomor hanya disamarkan saat ditampilkan pada output `--dry-run`.

### Fitur

- Nomor WhatsApp diisi saat mengajukan peminjaman (otomatis terisi dari profil, bisa diubah)
- Pengingat otomatis setiap jam (08:00-16:00) lewat scheduler `wa:kirim-terlambat`
- Maksimal 3 pengingat per peminjaman (jeda 24 jam)
- Normalisasi nomor (08xx, +62xx, 62xx)
- Dilewati jika nomor kosong (dicatat `dilewati` di `wa_logs`)
- Tidak kirim untuk peminjaman dengan ruangan, status selesai, atau yang seluruh alatnya sudah dikembalikan
- Log semua percobaan ke tabel `wa_logs`
