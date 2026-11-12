<?php

namespace Database\Seeders;

use App\Models\Sop;
use Illuminate\Database\Seeder;

class SopSeeder extends Seeder
{
    private const DATA = [
        [
            'kode' => 'SOP-01',
            'judul' => 'Tata Tertib Ruang Laboratorium',
            'deskripsi' => 'Ketentuan berpakaian rapi, larangan membawa makanan/minuman, dan kewajiban menjaga kebersihan ruangan praktikum.',
            'tujuan' => 'Menjaga ketertiban, kebersihan, dan keamanan perangkat laboratorium selama kegiatan praktikum berlangsung.',
            'poin' => [
                'Berpakaian rapi dan sopan (tanpa sandal jepit).',
                'Dilarang membawa makanan atau minuman ke dalam ruangan laboratorium.',
                'Tas diletakkan di tempat yang telah disediakan.',
                'Dilarang mengubah pengaturan sistem atau menginstal software tanpa izin laboran.',
                'Ponsel diatur dalam mode senyap selama kegiatan berlangsung.',
                'Rapikan meja dan kursi sebelum meninggalkan ruangan.',
                'Hanya pengguna yang tercatat dalam peminjaman yang boleh berada di ruangan.',
            ],
        ],
        [
            'kode' => 'SOP-02',
            'judul' => 'Peminjaman Ruang & Alat',
            'deskripsi' => 'Mekanisme reservasi online SIARKA, verifikasi dosen penanggung jawab, serta penyerahan kunci ruangan.',
            'tujuan' => 'Menetapkan alur pengajuan dan persetujuan peminjaman agar ketersediaan fasilitas tercatat dan tidak berbenturan jadwal.',
            'poin' => [
                'Ajukan peminjaman melalui menu Ajukan Peminjaman pada SIARKA.',
                'Dosen penanggung jawab memverifikasi pengajuan mahasiswa.',
                'Laboran memeriksa ketersediaan alat/bahan dan benturan jadwal ruangan.',
                'Ambil kunci ruangan dengan menunjukkan bukti persetujuan dan identitas.',
                'Satu akun hanya boleh memiliki satu peminjaman aktif per hari.',
                'Peminjaman tidak boleh dialihkan kepada pihak lain.',
                'Peminjaman dibatalkan otomatis apabila kunci tidak diambil sesuai jadwal.',
            ],
        ],
        [
            'kode' => 'SOP-03',
            'judul' => 'Keselamatan & Kesehatan Kerja',
            'deskripsi' => 'Prosedur penanganan darurat kelistrikan, evakuasi kebakaran, dan pertolongan pertama di area laboratorium.',
            'tujuan' => 'Memberi panduan tindakan cepat dan tepat pada kondisi darurat sehingga risiko cedera dan kerusakan dapat ditekan.',
            'poin' => [
                'Periksa kabel dan stop kontak sebelum perangkat digunakan.',
                'Bila terjadi percikan api atau bau gosong: matikan perangkat dan segera lapor laboran.',
                'Bila terjadi kebakaran: matikan sumber listrik, lakukan evakuasi, dan berkumpul di titik kumpul.',
                'Ketahui lokasi APAR, kotak P3K, dan pintu darurat sebelum memulai kegiatan.',
                'Setiap cedera wajib dicatat pada incident log laboratorium.',
            ],
        ],
        [
            'kode' => 'SOP-04',
            'judul' => 'Pengembalian & Pelaporan',
            'deskripsi' => 'Pemeriksaan fisik kondisi perangkat pasca-penggunaan, pencatatan insiden log, dan verifikasi akhir laboran.',
            'tujuan' => 'Memastikan fasilitas kembali dalam kondisi tercatat sehingga kerusakan dapat ditelusuri dan ditindaklanjuti.',
            'poin' => [
                'Periksa kondisi komputer dan kebersihan ruangan setelah digunakan.',
                'Matikan perangkat, AC, dan lampu sebelum meninggalkan ruangan.',
                'Catat kerusakan atau kejadian tidak biasa pada incident log.',
                'Serahkan kunci ruangan kembali kepada laboran.',
                'Laboran menutup peminjaman dengan status Selesai.',
                'Kerusakan akibat kelalaian menjadi tanggung jawab peminjam.',
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::DATA as $i => $row) {
            Sop::updateOrCreate(
                ['kode' => $row['kode']],
                $row + ['urutan' => $i + 1]
            );
        }
    }
}
