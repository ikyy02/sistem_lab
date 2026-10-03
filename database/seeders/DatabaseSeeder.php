<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Laboran;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Akun awal Laboran/Admin (ganti passwordnya setelah login). */
    public function run(): void
    {
        if (Akun::where('email', 'admin@silab.test')->exists()) {
            return;
        }
        Akun::create(['email' => 'admin@silab.test', 'password' => 'admin123', 'role' => 'laboran']);
        Laboran::create(['id_pegawai' => 'LAB0001', 'nama' => 'Administrator', 'email' => 'admin@silab.test', 'no_whatsapp' => null]);
    }
}
