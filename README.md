<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

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
