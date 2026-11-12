<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Laboran;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Akun awal Laboran/Admin, Data Master Ruangan, Dosen, Staf Prodi, dan Katalog Alat/Bahan. */
    public function run(): void
    {
        $this->call(RuanganSeeder::class);
        $this->call(DosenSeeder::class);
        $this->call(StaffProdiSeeder::class);
        $this->call(KatalogSeeder::class);
        $this->call(SopSeeder::class);
        $this->call(FaqSeeder::class);

        if (Akun::where('email', 'admin@silab.test')->exists()) {
            return;
        }
        Akun::create(['email' => 'admin@silab.test', 'password' => 'admin123', 'role' => 'laboran']);
        Laboran::create(['id_pegawai' => 'LAB0001', 'nama' => 'Administrator', 'email' => 'admin@silab.test', 'no_whatsapp' => null]);
    }
}
