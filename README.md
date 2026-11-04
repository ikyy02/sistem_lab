# SIARKA — Sistem Informasi Laboratorium

Aplikasi Laravel 13 (PHP 8.4, MySQL) untuk peminjaman alat, bahan, dan ruangan lab, beserta jadwal kuliah dan pengembalian.

Role: `mahasiswa`, `dosen`, `staff_prodi`, `laboran` (admin). Alur persetujuan: Dosen (tahap 1, bila ada penanggung jawab) lalu Laboran (keputusan akhir, stok dipotong).

## Setup
```bash
composer install
cp .env.example .env && php artisan key:generate
# isi DB_* dan ADMIN_PASSWORD di .env, lalu:
php artisan migrate --force
php artisan db:seed
php artisan storage:link
```

## Wajib di server: cron scheduler
Status peminjaman "selesai" diperbarui otomatis tiap 5 menit oleh scheduler. Tanpa cron ini peminjaman bahan tidak pernah selesai sendiri:
```
* * * * * cd /path/ke/SIARKA && php artisan schedule:run >> /dev/null 2>&1
```

## Tes
```bash
php artisan test
```
`phpunit.xml` memakai SQLite. Untuk menjalankan di MySQL: `DB_CONNECTION=mysql php artisan test` (isi juga `DB_DATABASE` dengan database khusus tes, karena tes mengosongkan isinya).
