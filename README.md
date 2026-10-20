# SIARKA — Sistem Informasi Laboratorium

Aplikasi Laravel 13 (PHP 8.3, MySQL) untuk peminjaman alat, bahan, dan ruangan lab, beserta jadwal kuliah dan pengembalian.

Role: `mahasiswa`, `dosen`, `staff_prodi`, `laboran` (admin). Alur persetujuan: Dosen (tahap 1, bila ada penanggung jawab) lalu Laboran (keputusan akhir, stok dipotong).

## Setup
```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
# isi DB_* di .env, lalu:
php artisan migrate --force
npm run build
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
