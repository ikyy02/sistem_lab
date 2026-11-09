<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Laboran;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Akun awal Laboran/Admin dan Data Master Ruangan Lab. */
    public function run(): void
    {
        $password = (string) env('ADMIN_PASSWORD');
        if ($password === '') {
            throw new \RuntimeException('ADMIN_PASSWORD kosong. Isi ADMIN_PASSWORD di .env sebelum menjalankan db:seed.');
        }

        $this->call(RuanganSeeder::class);

        if (Akun::where('email', 'admin@silab.test')->exists()) {
            return;
        }
        Akun::create(['email' => 'admin@silab.test', 'password' => $password, 'role' => 'laboran']);
        Laboran::create(['id_pegawai' => 'LAB0001', 'nama' => 'Administrator', 'email' => 'admin@silab.test', 'no_whatsapp' => null]);
    }
}
