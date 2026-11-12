# Tabel Skenario Pengujian — SIARKA
**Versi**: 1.0 | **Tanggal**: 11 November 2026
**Metode Pengujian**: Black Box Testing (Functional Testing)
**Aplikasi**: Sistem Informasi Ruang dan Alat-Bahan Laboratorium (SIARKA)

---

## Keterangan Kolom

| Kolom | Keterangan |
|---|---|
| **Tipe** | P = Positif (happy path), N = Negatif (error/edge case) |
| **Status** | ✓ = Lulus, ✗ = Gagal, — = Belum diuji |

---

## Tabel Skenario Pengujian

### 1. Autentikasi

| No | Nama Skenario | Modul | Tipe | Aktor | Pra-kondisi | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Hasil Aktual | Status |
|----|---------------|-------|------|-------|-------------|-------------------|----------|-----------------------|--------------|--------|
| 1 | Login berhasil dengan kredensial valid | Autentikasi | P | Mahasiswa | Akun mahasiswa terdaftar dan aktif di sistem | 1. Buka halaman `/login`. 2. Masukkan email dan password yang valid. 3. Klik tombol "Masuk". | Email: `mahasiswa@test.ac.id`, Password: `Password123!` | Pengguna diarahkan ke dashboard sesuai role (Mahasiswa). Session dibuat. Tidak ada pesan error. | — | — |
| 2 | Login gagal dengan password salah | Autentikasi | N | Mahasiswa | Akun mahasiswa terdaftar di sistem | 1. Buka halaman `/login`. 2. Masukkan email valid dan password yang salah. 3. Klik tombol "Masuk". | Email: `mahasiswa@test.ac.id`, Password: `PasswordSalah` | Pengguna tetap di halaman login. Muncul pesan error "Kredensial yang diberikan tidak cocok". Session tidak dibuat. | — | — |
| 3 | Login gagal dengan email tidak terdaftar | Autentikasi | N | - (Guest) | Tidak ada akun dengan email tersebut di database | 1. Buka halaman `/login`. 2. Masukkan email yang tidak terdaftar dan sembarang password. 3. Klik tombol "Masuk". | Email: `tidakada@contoh.com`, Password: `ApaAja123` | Pengguna tetap di halaman login. Muncul pesan error autentikasi generik. Session tidak dibuat. | — | — |
| 4 | Rate limiting login (5+ percobaan gagal) | Autentikasi | N | - (Guest) | Belum ada percobaan login dari IP/akun tersebut | 1. Buka halaman `/login`. 2. Masukkan email valid dengan password salah. 3. Ulangi langkah 2 sebanyak 5 kali berturut-turut. 4. Lakukan percobaan login ke-6. | Email: `mahasiswa@test.ac.id`, Password: `Salah` (diulangi 6×) | Setelah 5 percobaan gagal, sistem menampilkan pesan bahwa terlalu banyak percobaan dan meminta menunggu beberapa menit (throttle). HTTP 429 atau pesan lockout. | — | — |
| 5 | Logout berhasil | Autentikasi | P | Mahasiswa | Pengguna sudah login sebagai Mahasiswa | 1. Dari halaman dashboard, klik menu profil / nama pengguna. 2. Klik tombol "Keluar" / Logout. | — | Session dihapus. Pengguna diarahkan ke halaman login atau halaman publik. Jika URL dashboard diakses langsung, diarahkan ke login. | — | — |

---

### 2. Peminjaman Ruangan

| No | Nama Skenario | Modul | Tipe | Aktor | Pra-kondisi | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Hasil Aktual | Status |
|----|---------------|-------|------|-------|-------------|-------------------|----------|-----------------------|--------------|--------|
| 6 | Mahasiswa mengajukan peminjaman ruangan berhasil | Peminjaman Ruangan | P | Mahasiswa | Mahasiswa login; tidak ada peminjaman ruangan aktif; ruangan tersedia pada jam yang dipilih | 1. Buka halaman peminjaman ruangan. 2. Pilih ruangan dari daftar. 3. Isi tanggal pinjam, jam mulai, jam selesai, tanggal kembali, dan keperluan. 4. Klik "Ajukan". | Ruangan: `Lab Komputer A`, Tanggal pinjam: besok (hari kerja), Jam mulai: `08:00`, Jam selesai: `10:00`, Tanggal kembali: lusa, Keperluan: `Praktikum Jaringan` | Peminjaman tersimpan dengan status "Menunggu". Mahasiswa diarahkan ke halaman daftar peminjaman. Muncul notifikasi sukses. | — | — |
| 7 | Mahasiswa tidak bisa mengajukan jika ada peminjaman ruangan aktif | Peminjaman Ruangan | N | Mahasiswa | Mahasiswa login; sudah memiliki satu peminjaman ruangan dengan status "Menunggu" atau "Disetujui" | 1. Buka halaman peminjaman ruangan. 2. Isi semua field dengan data valid. 3. Klik "Ajukan". | Ruangan: `Lab Fisika B`, Tanggal pinjam: besok, Jam: `10:00–12:00` | Sistem menolak pengajuan. Muncul pesan error bahwa mahasiswa masih memiliki peminjaman ruangan aktif. Record baru tidak dibuat. | — | — |
| 8 | Dosen mengajukan peminjaman ruangan (otomatis jenis Pembelajaran) | Peminjaman Ruangan | P | Dosen | Dosen login; tidak ada konflik jadwal ruangan yang dipilih | 1. Login sebagai Dosen. 2. Buka halaman peminjaman ruangan. 3. Isi form peminjaman dengan data valid. 4. Klik "Ajukan". | Ruangan: `Ruang Kuliah 301`, Tanggal pinjam: besok, Jam mulai: `13:00`, Jam selesai: `15:00` | Peminjaman tersimpan dengan status "Menunggu" dan field jenis secara otomatis terisi "Pembelajaran" (tidak perlu dipilih manual oleh Dosen). | — | — |
| 9 | Mahasiswa mengajukan peminjaman ruangan di luar jam operasional | Peminjaman Ruangan | N | Mahasiswa | Mahasiswa login; jam operasional sistem: 07:00–17:00 | 1. Buka halaman peminjaman ruangan. 2. Pilih ruangan. 3. Masukkan jam mulai di luar jam operasional (misal 06:00 atau 20:00). 4. Klik "Ajukan". | Ruangan: `Lab Komputer A`, Jam mulai: `06:00`, Jam selesai: `08:00` | Sistem menampilkan pesan validasi bahwa jam yang dipilih di luar jam operasional. Peminjaman tidak tersimpan. | — | — |
| 10 | Peminjaman ruangan dengan tanggal kembali sama dengan tanggal pinjam | Peminjaman Ruangan | N | Mahasiswa | Mahasiswa login; tidak ada peminjaman ruangan aktif | 1. Buka halaman peminjaman ruangan. 2. Pilih ruangan. 3. Isi tanggal pinjam dan tanggal kembali dengan tanggal yang sama. 4. Klik "Ajukan". | Tanggal pinjam: `2026-11-20`, Tanggal kembali: `2026-11-20` | Sistem menampilkan pesan validasi bahwa tanggal kembali harus lebih besar dari tanggal pinjam. Peminjaman tidak tersimpan. | — | — |

---

### 3. Peminjaman Alat & Bahan

| No | Nama Skenario | Modul | Tipe | Aktor | Pra-kondisi | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Hasil Aktual | Status |
|----|---------------|-------|------|-------|-------------|-------------------|----------|-----------------------|--------------|--------|
| 11 | Mahasiswa mengajukan peminjaman alat berhasil | Peminjaman Alat & Bahan | P | Mahasiswa | Mahasiswa login; alat yang dipilih memiliki stok > 0; tidak ada peminjaman alat aktif | 1. Buka halaman peminjaman alat/bahan. 2. Pilih jenis "Alat". 3. Pilih alat dari dropdown dan isi jumlah. 4. Isi tanggal pinjam, tanggal kembali, dan keperluan. 5. Klik "Ajukan". | Alat: `Multimeter Digital`, Jumlah: `2`, Tanggal pinjam: besok, Tanggal kembali: 3 hari lagi, Keperluan: `Praktikum Elektronika` | Peminjaman tersimpan dengan status "Menunggu". Stok belum berkurang (berkurang hanya setelah disetujui). Muncul notifikasi sukses. | — | — |
| 12 | Jumlah alat yang diminta melebihi stok yang tersedia | Peminjaman Alat & Bahan | N | Mahasiswa | Mahasiswa login; alat `Osiloskop` memiliki stok = 3 | 1. Buka halaman peminjaman alat/bahan. 2. Pilih "Alat" → pilih `Osiloskop`. 3. Masukkan jumlah yang melebihi stok (misal 5). 4. Klik "Ajukan". | Alat: `Osiloskop`, Jumlah diminta: `5`, Stok tersedia: `3` | Sistem menampilkan pesan validasi bahwa jumlah yang diminta melebihi stok yang tersedia. Peminjaman tidak tersimpan. | — | — |
| 13 | Mahasiswa boleh punya peminjaman alat aktif sekaligus meminjam ruangan | Peminjaman Alat & Bahan | P | Mahasiswa | Mahasiswa login; sudah memiliki peminjaman alat dengan status "Disetujui"; tidak ada peminjaman ruangan aktif | 1. Buka halaman peminjaman ruangan. 2. Isi form peminjaman ruangan dengan data valid. 3. Klik "Ajukan". | Ruangan: `Lab Komputer B`, Tanggal pinjam: besok, Jam: `09:00–11:00` | Sistem menerima pengajuan peminjaman ruangan meskipun ada peminjaman alat aktif. Peminjaman ruangan tersimpan dengan status "Menunggu". | — | — |
| 14 | Alat dengan stok 0 tidak muncul di dropdown peminjaman | Peminjaman Alat & Bahan | N | Mahasiswa | Mahasiswa login; alat `Spektrofotometer` memiliki stok = 0 | 1. Buka halaman peminjaman alat/bahan. 2. Pilih jenis "Alat". 3. Buka dropdown daftar alat dan perhatikan isinya. | Alat dengan stok 0: `Spektrofotometer` | Alat `Spektrofotometer` tidak muncul pada dropdown pilihan alat. Hanya alat dengan stok > 0 yang ditampilkan. | — | — |
| 15 | Mahasiswa mengajukan peminjaman bahan berhasil | Peminjaman Alat & Bahan | P | Mahasiswa | Mahasiswa login; bahan yang dipilih memiliki stok mencukupi; tidak ada peminjaman alat/bahan aktif | 1. Buka halaman peminjaman alat/bahan. 2. Pilih jenis "Bahan". 3. Pilih bahan dari dropdown dan isi jumlah serta satuan. 4. Isi tanggal pinjam, tanggal kembali, dan keperluan. 5. Klik "Ajukan". | Bahan: `HCl (Asam Klorida)`, Jumlah: `50`, Satuan: `ml`, Keperluan: `Praktikum Kimia Dasar` | Peminjaman bahan tersimpan dengan status "Menunggu". Stok belum berkurang. Muncul notifikasi sukses. | — | — |

---

### 4. Persetujuan & Penolakan Laboran

| No | Nama Skenario | Modul | Tipe | Aktor | Pra-kondisi | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Hasil Aktual | Status |
|----|---------------|-------|------|-------|-------------|-------------------|----------|-----------------------|--------------|--------|
| 16 | Laboran menyetujui peminjaman alat, stok berkurang | Persetujuan Laboran | P | Laboran | Laboran login; ada pengajuan peminjaman alat dengan status "Menunggu"; stok alat mencukupi | 1. Login sebagai Laboran. 2. Buka halaman daftar persetujuan peminjaman. 3. Pilih pengajuan dengan status "Menunggu". 4. Klik "Setujui". 5. Konfirmasi persetujuan. | Pengajuan: Peminjaman `Multimeter Digital` (jumlah: 2), Stok sebelum: 10 | Status peminjaman berubah menjadi "Disetujui". Stok alat berkurang sesuai jumlah yang disetujui (dari 10 menjadi 8). Pemohon menerima notifikasi. | — | — |
| 17 | Laboran menolak peminjaman dengan alasan | Persetujuan Laboran | P | Laboran | Laboran login; ada pengajuan peminjaman dengan status "Menunggu" | 1. Login sebagai Laboran. 2. Buka halaman daftar persetujuan. 3. Pilih pengajuan yang ingin ditolak. 4. Klik "Tolak". 5. Isi alasan penolakan. 6. Konfirmasi penolakan. | Alasan penolakan: `Alat sedang dalam perbaikan` | Status peminjaman berubah menjadi "Ditolak". Alasan penolakan tersimpan dan dapat dilihat oleh pemohon. Stok tidak berkurang. Pemohon menerima notifikasi penolakan. | — | — |
| 18 | Laboran tidak bisa menyetujui jika stok tidak cukup saat approval | Persetujuan Laboran | N | Laboran | Laboran login; ada pengajuan peminjaman alat A (jumlah: 5); stok alat A saat ini = 3 (berubah setelah pengajuan dibuat) | 1. Login sebagai Laboran. 2. Buka halaman daftar persetujuan. 3. Pilih pengajuan alat A (jumlah: 5). 4. Klik "Setujui". | Alat: `Oscilloscope`, Jumlah diminta: `5`, Stok saat approval: `3` | Sistem menampilkan pesan error bahwa stok tidak mencukupi untuk menyetujui permintaan ini. Status tidak berubah. Laboran diminta menolak atau meminta pemohon merevisi jumlah. | — | — |
| 19 | Mahasiswa (non-Laboran) tidak bisa akses halaman persetujuan | Persetujuan Laboran | N | Mahasiswa | Mahasiswa login; URL halaman persetujuan diketahui | 1. Login sebagai Mahasiswa. 2. Navigasi langsung ke URL halaman persetujuan (misal `/admin/peminjaman/approval`). | URL: `/admin/peminjaman/approval` | Sistem mengembalikan HTTP 403 Forbidden atau mengarahkan ke halaman error. Halaman persetujuan tidak dapat diakses oleh Mahasiswa. | — | — |

---

### 5. Pengembalian

| No | Nama Skenario | Modul | Tipe | Aktor | Pra-kondisi | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Hasil Aktual | Status |
|----|---------------|-------|------|-------|-------------|-------------------|----------|-----------------------|--------------|--------|
| 20 | Laboran mencatat pengembalian alat, stok bertambah | Pengembalian | P | Laboran | Laboran login; ada peminjaman alat dengan status "Disetujui"; alat belum dikembalikan | 1. Login sebagai Laboran. 2. Buka halaman peminjaman yang aktif. 3. Pilih record peminjaman berstatus "Disetujui". 4. Klik "Catat Pengembalian". 5. Isi tanggal dan catatan kondisi alat. 6. Konfirmasi. | Peminjaman: `Multimeter Digital` (jumlah: 2), Stok sebelum dikembalikan: 8, Catatan kondisi: `Baik` | Status peminjaman berubah menjadi "Dikembalikan". Stok alat bertambah kembali sesuai jumlah yang dipinjam (dari 8 menjadi 10). Tanggal pengembalian aktual tersimpan. | — | — |
| 21 | Pengembalian tidak bisa dilakukan jika status peminjaman bukan "Disetujui" | Pengembalian | N | Laboran | Laboran login; ada peminjaman alat dengan status "Menunggu" | 1. Login sebagai Laboran. 2. Coba akses atau klik tombol catat pengembalian pada peminjaman berstatus "Menunggu". | Status peminjaman: `Menunggu` | Tombol "Catat Pengembalian" tidak tersedia atau tidak aktif pada peminjaman berstatus "Menunggu". Jika diakses via URL langsung, sistem mengembalikan error/redirect. | — | — |
| 22 | Mahasiswa membatalkan peminjaman yang masih berstatus "Menunggu" | Pengembalian | P | Mahasiswa | Mahasiswa login; memiliki peminjaman alat/ruangan dengan status "Menunggu" | 1. Login sebagai Mahasiswa. 2. Buka halaman riwayat peminjaman. 3. Temukan peminjaman berstatus "Menunggu". 4. Klik tombol "Batalkan". 5. Konfirmasi pembatalan. | — | Status peminjaman berubah menjadi "Dibatalkan". Stok tidak terpengaruh (karena belum disetujui). Peminjaman tidak lagi aktif, mahasiswa bisa mengajukan yang baru. | — | — |

---

### 6. Pengadaan TPK / SPK SAW+AHP

| No | Nama Skenario | Modul | Tipe | Aktor | Pra-kondisi | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Hasil Aktual | Status |
|----|---------------|-------|------|-------|-------------|-------------------|----------|-----------------------|--------------|--------|
| 23 | Laboran mengakses halaman pengadaan TPK berhasil | Pengadaan TPK | P | Laboran | Laboran login; terdapat data alat/bahan di sistem | 1. Login sebagai Laboran. 2. Navigasi ke menu "Pengadaan TPK" dari sidebar. | — | Halaman pengadaan TPK tampil dengan benar. Daftar barang/alat yang perlu diadakan ditampilkan. Komponen filter dan pengaturan bobot SAW tersedia. | — | — |
| 24 | Mahasiswa mencoba akses halaman pengadaan TPK (harus 403) | Pengadaan TPK | N | Mahasiswa | Mahasiswa login; URL halaman pengadaan TPK diketahui | 1. Login sebagai Mahasiswa. 2. Navigasi langsung ke URL halaman pengadaan TPK (misal `/pengadaan-tpk`). | URL: `/pengadaan-tpk` | Sistem mengembalikan HTTP 403 Forbidden atau mengarahkan ke halaman error akses ditolak. Mahasiswa tidak dapat melihat data pengadaan. | — | — |
| 25 | TPK menampilkan ranking barang berdasarkan bobot SAW yang valid | Pengadaan TPK | P | Laboran | Laboran login di halaman TPK; total bobot semua kriteria = 100% | 1. Login sebagai Laboran. 2. Buka halaman pengadaan TPK. 3. Set bobot kriteria: Harga=30%, Stok=40%, Prioritas=30% (total=100%). 4. Klik tombol "Terapkan". | Bobot: Harga=30, Stok=40, Prioritas=30 | Tabel ranking barang ditampilkan dengan urutan berdasarkan skor SAW dari tertinggi ke terendah. Skor SAW terhitung dan ditampilkan pada setiap baris. | — | — |
| 26 | Bobot SAW tidak valid (total ≠ 100%), tombol Terapkan tidak aktif | Pengadaan TPK | N | Laboran | Laboran login di halaman TPK | 1. Login sebagai Laboran. 2. Buka halaman pengadaan TPK. 3. Set bobot kriteria sehingga totalnya tidak 100% (misal Harga=20%, Stok=30%, Prioritas=30%, total=80%). 4. Perhatikan keadaan tombol "Terapkan". | Bobot: Harga=20, Stok=30, Prioritas=30 (total=80%) | Tombol "Terapkan" dalam keadaan disabled (tidak dapat diklik) selama total bobot ≠ 100%. Muncul indikator atau peringatan bahwa total bobot belum mencapai 100%. | — | — |
| 27 | Filter berdasarkan kategori "Alat" menampilkan hanya alat | Pengadaan TPK | P | Laboran | Laboran login di halaman TPK; terdapat data alat dan bahan di sistem | 1. Login sebagai Laboran. 2. Buka halaman pengadaan TPK. 3. Pilih filter kategori = "Alat". 4. Klik "Terapkan" (atau filter otomatis). | Filter kategori: `Alat` | Hanya item berkategori "Alat" yang ditampilkan pada tabel ranking. Item berjenis "Bahan" tidak muncul dalam hasil. | — | — |

---

### 7. Notifikasi & Lain-lain

| No | Nama Skenario | Modul | Tipe | Aktor | Pra-kondisi | Langkah Pengujian | Data Uji | Hasil yang Diharapkan | Hasil Aktual | Status |
|----|---------------|-------|------|-------|-------------|-------------------|----------|-----------------------|--------------|--------|
| 28 | Laboran mendapat notifikasi WhatsApp saat ada pengajuan peminjaman baru | Notifikasi WA | P | Laboran | Nomor WA Laboran terdaftar; layanan notifikasi WA aktif; ada mahasiswa yang baru mengajukan peminjaman | 1. Mahasiswa mengajukan peminjaman alat (skenario No.11). 2. Tunggu beberapa saat. 3. Periksa WhatsApp Laboran. | Pengajuan oleh: `mahasiswa@test.ac.id` | Laboran menerima pesan WhatsApp yang berisi informasi pengajuan baru: nama pemohon, alat yang diminta, jumlah, dan tanggal. | — | — |
| 29 | Mahasiswa mendapat notifikasi WhatsApp saat peminjaman disetujui | Notifikasi WA | P | Mahasiswa | Nomor WA Mahasiswa terdaftar; layanan notifikasi WA aktif; Laboran baru menyetujui peminjaman mahasiswa tersebut | 1. Laboran menyetujui peminjaman (skenario No.16). 2. Tunggu beberapa saat. 3. Periksa WhatsApp Mahasiswa. | Peminjaman yang disetujui milik: `mahasiswa@test.ac.id` | Mahasiswa menerima pesan WhatsApp yang berisi konfirmasi persetujuan: nama alat/ruangan, tanggal pinjam, dan petunjuk pengambilan/penggunaan. | — | — |
| 30 | Halaman profil dapat diakses oleh semua role | Kelola User | P | Mahasiswa, Dosen, Laboran, Staf Prodi | Masing-masing pengguna sudah login | 1. Login dengan akun Mahasiswa → akses halaman `/profil`. 2. Logout, login dengan akun Dosen → akses `/profil`. 3. Ulangi untuk Laboran dan Staf Prodi. | — | Semua role dapat mengakses halaman profil masing-masing. Data profil (nama, email, nomor WA, foto) ditampilkan dengan benar sesuai akun yang login. | — | — |
| 31 | Staf Prodi dapat melihat data akademik | Data Akademik | P | Staf Prodi | Staf Prodi login; data akademik (prodi, mata kuliah, jadwal) tersedia | 1. Login sebagai Staf Prodi. 2. Navigasi ke menu "Data Akademik" dari sidebar. 3. Verifikasi tampilan data. | — | Halaman data akademik tampil. Staf Prodi dapat melihat daftar program studi, mata kuliah, dan/atau jadwal terkait. Data tersaji lengkap dan dapat di-filter/dicari. | — | — |
| 32 | Data akademik tidak bisa diakses oleh Mahasiswa biasa (403) | Data Akademik | N | Mahasiswa | Mahasiswa login; URL halaman data akademik diketahui | 1. Login sebagai Mahasiswa. 2. Navigasi langsung ke URL halaman data akademik (misal `/data-akademik`). | URL: `/data-akademik` | Sistem mengembalikan HTTP 403 Forbidden atau mengarahkan ke halaman error. Menu "Data Akademik" juga tidak tampil di sidebar Mahasiswa. | — | — |

---

## Ringkasan Cakupan Skenario

| Modul | Jumlah Skenario | Positif | Negatif |
|-------|----------------|---------|---------|
| Autentikasi | 5 | 2 | 3 |
| Peminjaman Ruangan | 5 | 2 | 3 |
| Peminjaman Alat & Bahan | 5 | 3 | 2 |
| Persetujuan Laboran | 4 | 2 | 2 |
| Pengembalian | 3 | 2 | 1 |
| Pengadaan TPK / SPK SAW | 5 | 3 | 2 |
| Notifikasi WA | 2 | 2 | 0 |
| Kelola User / Profil | 1 | 1 | 0 |
| Data Akademik | 2 | 1 | 1 |
| **Total** | **32** | **18** | **14** |

---

*Dokumen ini dibuat secara otomatis untuk keperluan pengujian sistem SIARKA. Kolom "Hasil Aktual" dan "Status" diisi pada saat pelaksanaan pengujian.*
