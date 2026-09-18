<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Jalankan UserSeeder terlebih dahulu (4 akun resmi sesuai requirement)
        $this->call(UserSeeder::class);

        // ── Akun tambahan (legacy / demo tambahan) ─────────────────────────

        // ── Laboran (Super Admin) ──────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'laboran@lab.id'],
            [
                'name'     => 'Budi Santoso',
                'username' => 'laboran',
                'email'    => 'laboran@lab.id',
                'password' => Hash::make('password123'),
                'role'     => 'laboran',
                'nip'      => '198501012010011001',
                'no_hp'    => '081234567890',
                'status'   => 'aktif',
            ]
        );

        // ── Staf Prodi ────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'staf@lab.id'],
            [
                'name'     => 'Siti Rahayu',
                'username' => 'staf_prodi',
                'email'    => 'staf@lab.id',
                'password' => Hash::make('password123'),
                'role'     => 'staf_prodi',
                'nip'      => '199003152015032002',
                'no_hp'    => '082345678901',
                'status'   => 'aktif',
            ]
        );

        // ── Dosen ─────────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'dosen@lab.id'],
            [
                'name'     => 'Dr. Ahmad Fauzi, M.T.',
                'username' => 'dosen_ahmad',
                'email'    => 'dosen@lab.id',
                'password' => Hash::make('password123'),
                'role'     => 'dosen',
                'nidn'     => '0101018501',
                'no_hp'    => '083456789012',
                'status'   => 'aktif',
            ]
        );

        // ── Mahasiswa ─────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'mahasiswa@lab.id'],
            [
                'name'     => 'Andi Pratama',
                'username' => 'andi.pratama',
                'email'    => 'mahasiswa@lab.id',
                'password' => Hash::make('password123'),
                'role'     => 'mahasiswa',
                'nim'      => '2021310001',
                'no_hp'    => '084567890123',
                'status'   => 'aktif',
            ]
        );

        // ── Mahasiswa tambahan ────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'dewi@lab.id'],
            [
                'name'     => 'Dewi Lestari',
                'username' => 'dewi.lestari',
                'email'    => 'dewi@lab.id',
                'password' => Hash::make('password123'),
                'role'     => 'mahasiswa',
                'nim'      => '2021310002',
                'no_hp'    => '085678901234',
                'status'   => 'aktif',
            ]
        );

        // ── User nonaktif (contoh) ─────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'nonaktif@lab.id'],
            [
                'name'     => 'Rudi Hermawan',
                'username' => 'rudi.nonaktif',
                'email'    => 'nonaktif@lab.id',
                'password' => Hash::make('password123'),
                'role'     => 'mahasiswa',
                'nim'      => '2019310099',
                'no_hp'    => '086789012345',
                'status'   => 'nonaktif',
            ]
        );

        $this->command->info('✅ Seeder berhasil dijalankan! Akun tersedia:');
        $this->command->table(
            ['Role', 'Email', 'Password', 'Status'],
            [
                ['Laboran (Super Admin)', 'laboran@lab.id',    'password123', 'Aktif'],
                ['Staf Prodi',            'staf@lab.id',       'password123', 'Aktif'],
                ['Dosen',                 'dosen@lab.id',      'password123', 'Aktif'],
                ['Mahasiswa',             'mahasiswa@lab.id',  'password123', 'Aktif'],
                ['Mahasiswa',             'dewi@lab.id',       'password123', 'Aktif'],
                ['Mahasiswa (Nonaktif)',   'nonaktif@lab.id',  'password123', 'Nonaktif'],
            ]
        );
    }
}
