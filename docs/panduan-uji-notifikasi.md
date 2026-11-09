# Panduan Uji Manual Notifikasi (Email + Lonceng In-App)

Tujuan: memastikan notifikasi benar-benar terkirim saat aplikasi dipakai manual.
Uji dua tahap: **MAIL_MAILER=log** (tanpa internet, cek file log) lalu **SMTP Gmail** (cek inbox asli).

## 0. Persiapan

1. Migrasi database (butuh tabel `jobs`, `failed_jobs`, dan `notifications`):

   ```bash
   php artisan migrate
   ```

2. (Opsional) Arahkan akun uji ke inbox yang benar-benar bisa menerima email — tambahkan ke `.env`, lalu seed:

   ```env
   NOTIFIKASI_UJI_EMAIL_MHS=alamat-mhs@contoh.com
   NOTIFIKASI_UJI_EMAIL_DOSEN=alamat-dosen@contoh.com
   NOTIFIKASI_UJI_EMAIL_LABORAN=alamat-laboran@contoh.com
   NOTIFIKASI_UJI_PASSWORD=isi-password-uji
   ```

   ```bash
   php artisan config:clear
   php artisan db:seed --class=NotifikasiUjiSeeder
   ```

   Tanpa override, akun uji yang dibuat: mahasiswa uji (NIM 26990001), dosen uji (D9990001),
   laboran uji (L9990001) memakai alamat default di `config/notifikasi.php`, password
   password dari `NOTIFIKASI_UJI_PASSWORD`, plus katalog "Alat Uji Notifikasi" (stok 10). Seeder idempoten dan hanya
   jalan di `local`/`testing`; ubah alamat lalu seed ulang untuk memperbarui email akun.

3. Semua notifikasi berjalan lewat queue. Pastikan `.env` berisi `QUEUE_CONNECTION=database`
   dan **`php artisan queue:work` berjalan di terminal terpisah** selama pengujian
   (tanpa worker, job menumpuk dan email tidak terkirim).

## 1. Tahap 1 — MAIL_MAILER=log

```env
MAIL_MAILER=log
QUEUE_CONNECTION=database
```

```bash
php artisan config:clear
php artisan queue:work
```

Alur uji:

1. Login **laboran uji** → tambah akun baru (Kelola Data User) → email "Akun Terverifikasi" masuk ke log.
2. Login **mahasiswa uji** → Katalog → ajukan peminjaman "Alat Uji Notifikasi".
3. Login **laboran uji** → menu Pengajuan → Setujui.
4. Masih sebagai laboran → menu Pengembalian → catat pengembalian penuh (kondisi Baik).
5. Cek lonceng di topbar setiap akun: badge unread bertambah, dropdown menampilkan 10 notifikasi terbaru.

Verifikasi:

- Email: buka `storage/logs/laravel.log` di terminal — cari baris subject notifikasi
  (mis. `Peminjaman Disetujui`, `Pengembalian Dicatat`); isi markdown email tercatat lengkap.
- In-app: lonceng menyala, halaman `Notifikasi` menampilkan riwayat; tombol
  "Tandai dibaca"/"Tandai semua dibaca" berfungsi.
- Antrean: `php artisan queue:failed` harus kosong bila semuanya berhasil.

## 2. Tahap 2 — SMTP Gmail

Gmail menolak password akun biasa untuk SMTP; buat **App Password**:

1. Aktifkan verifikasi 2 langkah pada akun Google.
2. Buka `https://myaccount.google.com/apppasswords` → buat App Password 16 digit.

Isi `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_SCHEME=null
MAIL_USERNAME=akunanda@gmail.com
MAIL_PASSWORD=xxxxxxxxxxxxxxxx   # App Password 16 digit (tanpa spasi), bukan password akun
MAIL_FROM_ADDRESS=akunanda@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

```bash
php artisan config:clear
php artisan queue:work
```

(Penting: worker lama memakai konfigurasi saat boot — hentikan lalu jalankan ulang `queue:work`
setiap kali `.env` berubah.)

Ulangi alur uji pada bagian 1, lalu **cek inbox masing-masing akun**:

- Mahasiswa uji: "Peminjaman Diterima", "Peminjaman Disetujui", "Pengembalian Dicatat", "Peminjaman Selesai".
- Laboran uji: "Pengajuan Peminjaman Baru" (setelah mahasiswa mengajukan).
- Dosen uji: hanya untuk pengajuan bertipe "atas dosen" (dosen penanggung jawab).
- Cek juga folder Spam/Promotions bila tidak terlihat.

## 3. Opsional — Pengingat terjadwal

Pengingat H-1 dan keterlambatan berjalan pukul 07:00 setiap hari:

```bash
php artisan schedule:work
```

Sinkron status "selesai" otomatis berjalan tiap 5 menit (atau jalankan cron
`php artisan schedule:run` standar pada server).

## Catatan

- Tes otomatis terkait: `php artisan test --filter=Notifikasi` (fake Notification/Mailer;
  termasuk kasus error email yang tidak menggagalkan transaksi stok).
- Bila email gagal terkirim (SMTP salah, dsb.), job tercatat di `failed_jobs`
  (`php artisan queue:failed` / `queue:retry all`) dan transaksi tetap aman — kegagalan
  kirim dicatat sebagai warning di log aplikasi (`Notif gagal dikirim ke ...`).
