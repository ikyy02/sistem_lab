<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    private const DATA = [
        [
            'kategori' => 'Akun',
            'pertanyaan' => 'Bagaimana cara mahasiswa memperoleh akun SIARKA?',
            'jawaban' => 'Akun mahasiswa dibuatkan secara terpusat oleh staf prodi atau laboran berdasarkan data registrasi akademik awal semester. Gunakan email institusi mahasiswa Anda untuk masuk ke SIARKA.',
            'urutan' => 1,
        ],
        [
            'kategori' => 'Peminjaman',
            'pertanyaan' => 'Berapa hari batas waktu pengajuan peminjaman laboratorium?',
            'jawaban' => 'Permohonan peminjaman mandiri dianjurkan diajukan paling lambat 2 (dua) hari kerja sebelum tanggal pelaksanaan kegiatan agar laboran dan dosen penanggung jawab dapat memverifikasi ketersediaan ruang.',
            'urutan' => 2,
        ],
        [
            'kategori' => 'Peminjaman',
            'pertanyaan' => 'Mengapa tombol Ajukan Peminjaman tidak bisa dipakai?',
            'jawaban' => 'Satu akun hanya boleh memiliki satu peminjaman aktif per hari. Ajukan peminjaman baru setelah peminjaman sebelumnya berstatus Selesai atau Dibatalkan. Periksa menu Peminjaman Saya untuk melihat pengajuan yang masih menunggu.',
            'urutan' => 3,
        ],
        [
            'kategori' => 'Peminjaman',
            'pertanyaan' => 'Apakah mahasiswa boleh menggunakan laboratorium di akhir pekan?',
            'jawaban' => 'Peminjaman di akhir pekan hanya diperkenankan untuk kegiatan resmi institusi, perlombaan akademik berskala nasional, atau penelitian tugas akhir khusus dengan surat rekomendasi resmi dosen pembimbing dan persetujuan Ketua Jurusan.',
            'urutan' => 4,
        ],
        [
            'kategori' => 'Peminjaman',
            'pertanyaan' => 'Apa perbedaan Peminjaman Saya dan Riwayat Peminjaman?',
            'jawaban' => 'Peminjaman Saya menampilkan pengajuan yang belum diputus (status Menunggu Persetujuan). Riwayat Peminjaman menampilkan pengajuan yang sudah diputus: Disetujui, Selesai, Ditolak, atau Dibatalkan.',
            'urutan' => 5,
        ],
        [
            'kategori' => 'Fasilitas',
            'pertanyaan' => 'Apa yang harus dilakukan jika terjadi kerusakan perangkat saat praktikum?',
            'jawaban' => 'Segera laporkan kepada laboran atau asisten piket di meja layanan. Jangan mencoba memperbaiki perangkat keras kelistrikan sendiri tanpa pendampingan teknisi resmi laboratorium. Kejadian dicatat pada incident log.',
            'urutan' => 6,
        ],
        [
            'kategori' => 'Akun',
            'pertanyaan' => 'Bagaimana cara mengganti password akun SIARKA?',
            'jawaban' => 'Buka menu Profil lalu pilih bagian ganti password. Jika akun baru dibuatkan dan ditandai wajib ganti password, SIARKA akan meminta penggantian password sebelum menu lain dapat diakses.',
            'urutan' => 7,
        ],
        [
            'kategori' => 'Fasilitas',
            'pertanyaan' => 'Ke mana harus menghubungi jika ada kendala teknis di laboratorium?',
            'jawaban' => 'Hubungi laboran melalui meja layanan pada jam kerja (Senin–Kamis 08.00–16.00, Jumat 08.00–11.30 dan 13.30–16.30 WITA) atau gunakan kanal pengaduan yang tersedia di laboratorium.',
            'urutan' => 8,
        ],
    ];

    public function run(): void
    {
        foreach (self::DATA as $row) {
            Faq::updateOrCreate(
                ['pertanyaan' => $row['pertanyaan']],
                $row + ['aktif' => true]
            );
        }
    }
}
