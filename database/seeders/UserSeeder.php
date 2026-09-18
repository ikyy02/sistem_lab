<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed 4 akun utama untuk setiap role.
     * Menggunakan updateOrCreate() agar aman dijalankan berulang kali.
     */
    public function run(): void
    {
        // ── 1. MAHASISWA ──────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'mahasiswa@labkom.test'],
            [
                'name'     => 'Mahasiswa Labkom',
                'username' => 'mahasiswa_labkom',
                'email'    => 'mahasiswa@labkom.test',
                'password' => Hash::make('password123'),
                'role'     => 'mahasiswa',
                'nim'      => '2301001',
                'nidn'     => null,
                'nip'      => null,
                'no_hp'    => '081111111111',
                'status'   => 'aktif',
            ]
        );

        // ── 2. DOSEN ──────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'dosen@labkom.test'],
            [
                'name'     => 'Dosen Labkom',
                'username' => 'dosen_labkom',
                'email'    => 'dosen@labkom.test',
                'password' => Hash::make('password123'),
                'role'     => 'dosen',
                'nim'      => null,
                'nidn'     => '0012345678',
                'nip'      => null,
                'no_hp'    => '082222222222',
                'status'   => 'aktif',
            ]
        );

        // ── 3. STAF PRODI ─────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'stafprodi@labkom.test'],
            [
                'name'     => 'Staf Prodi Labkom',
                'username' => 'stafprodi_labkom',
                'email'    => 'stafprodi@labkom.test',
                'password' => Hash::make('password123'),
                'role'     => 'staf_prodi',
                'nim'      => null,
                'nidn'     => null,
                'nip'      => '198001012010011001',
                'no_hp'    => '083333333333',
                'status'   => 'aktif',
            ]
        );

        // ── 4. LABORAN (Super Admin) ───────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'laboran@labkom.test'],
            [
                'name'     => 'Laboran Super Admin',
                'username' => 'laboran_labkom',
                'email'    => 'laboran@labkom.test',
                'password' => Hash::make('password123'),
                'role'     => 'laboran',
                'nim'      => null,
                'nidn'     => null,
                'nip'      => '198501012010011002',
                'no_hp'    => '084444444444',
                'status'   => 'aktif',
            ]
        );

        // ── 5. Akun nonaktif (untuk test penolakan login) ──────────────────
        User::updateOrCreate(
            ['email' => 'nonaktif@labkom.test'],
            [
                'name'     => 'User Nonaktif',
                'username' => 'nonaktif_labkom',
                'email'    => 'nonaktif@labkom.test',
                'password' => Hash::make('password123'),
                'role'     => 'mahasiswa',
                'nim'      => '2301099',
                'nidn'     => null,
                'nip'      => null,
                'no_hp'    => '085555555555',
                'status'   => 'nonaktif',
            ]
        );

        $this->command->info('');
        $this->command->info('✅ UserSeeder berhasil! Akun tersedia:');
        $this->command->table(
            ['No', 'Nama', 'Email', 'Password', 'Role', 'Identitas', 'Status'],
            [
                ['1', 'Mahasiswa Labkom',   'mahasiswa@labkom.test',  'password123', 'mahasiswa',  'NIM: 2301001',            'Aktif'],
                ['2', 'Dosen Labkom',       'dosen@labkom.test',      'password123', 'dosen',      'NIDN: 0012345678',        'Aktif'],
                ['3', 'Staf Prodi Labkom',  'stafprodi@labkom.test',  'password123', 'staf_prodi', 'NIP: 198001012010011001', 'Aktif'],
                ['4', 'Laboran Super Admin','laboran@labkom.test',    'password123', 'laboran',    'NIP: 198501012010011002', 'Aktif'],
                ['5', 'User Nonaktif',      'nonaktif@labkom.test',   'password123', 'mahasiswa',  'NIM: 2301099',            'Nonaktif'],
            ]
        );
    }
}
