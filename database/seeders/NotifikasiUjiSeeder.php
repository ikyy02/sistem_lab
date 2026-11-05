<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\AlatBahan;
use App\Models\Dosen;
use App\Models\Laboran;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\Ruangan;
use App\Models\Satuan;
use Illuminate\Database\Seeder;

/**
 * Data minimum untuk uji manual notifikasi (email + lonceng in-app). Idempoten, hanya local/testing.
 * Jalankan: php artisan db:seed --class=NotifikasiUjiSeeder
 *
 * Alamat email diambil dari config('notifikasi.uji') sehingga bisa diarahkan ke inbox asli
 * lewat .env (NOTIFIKASI_UJI_EMAIL_MHS/DOSEN/LABORAN, NOTIFIKASI_UJI_PASSWORD).
 * Password disimpan apa adanya mengikuti konvensi DatabaseSeeder (hanya untuk uji lokal).
 */
class NotifikasiUjiSeeder extends Seeder
{
    private const GAMBAR = '69be8357-025d-498e-bc3e-e4f18a03c7c9.png';

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $password = (string) config('notifikasi.uji.password');
        $prodi = Prodi::firstOrCreate(['nama_prodi' => 'Teknik Informatika']);
        $unit = Satuan::firstOrCreate(['nama_satuan' => 'unit']);
        $ruangan = Ruangan::firstOrCreate(['nama_ruangan' => 'Lab Uji Notifikasi']);

        AlatBahan::firstOrCreate(['nama' => 'Alat Uji Notifikasi'], [
            'jenis' => 'alat', 'id_satuan' => $unit->id_satuan, 'stok' => 10, 'harga' => 100000,
            'id_ruangan' => $ruangan->id_ruangan, 'gambar' => self::GAMBAR,
            'keterangan' => 'Barang contoh untuk uji manual notifikasi.',
        ]);

        $emailMhs = (string) config('notifikasi.uji.email_mahasiswa');
        Akun::updateOrCreate(['email' => $emailMhs], ['password' => $password, 'role' => 'mahasiswa']);
        Mahasiswa::updateOrCreate(['nim' => '26990001'], [
            'nama' => 'Mahasiswa Uji Notifikasi', 'id_prodi' => $prodi->id_prodi,
            'email' => $emailMhs, 'no_whatsapp' => null,
        ]);

        $emailDosen = (string) config('notifikasi.uji.email_dosen');
        Akun::updateOrCreate(['email' => $emailDosen], ['password' => $password, 'role' => 'dosen']);
        Dosen::updateOrCreate(['nuptk_nidn' => 'D9990001'], [
            'nama' => 'Dosen Uji Notifikasi', 'id_prodi' => $prodi->id_prodi,
            'email' => $emailDosen, 'no_whatsapp' => null,
        ]);

        $emailLab = (string) config('notifikasi.uji.email_laboran');
        Akun::updateOrCreate(['email' => $emailLab], ['password' => $password, 'role' => 'laboran']);
        Laboran::updateOrCreate(['id_pegawai' => 'L9990001'], [
            'nama' => 'Laboran Uji Notifikasi', 'email' => $emailLab, 'no_whatsapp' => null,
        ]);

        $this->command?->info('Akun uji notifikasi siap (password: '.$password.'):');
        $this->command?->line('  Mahasiswa : '.$emailMhs.' (NIM 26990001)');
        $this->command?->line('  Dosen     : '.$emailDosen.' (NIDN D9990001)');
        $this->command?->line('  Laboran   : '.$emailLab.' (L9990001)');
        $this->command?->line('  Katalog   : Alat Uji Notifikasi (stok 10, Lab Uji Notifikasi)');
    }
}
