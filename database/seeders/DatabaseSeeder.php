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
        // Production wajib ADMIN_PASSWORD; 'admin123' hanya untuk environment local.
        $passwordAdmin = config('app.admin_password') ?: (app()->environment('local') ? 'admin123' : null);
        if ($passwordAdmin === null) {
            throw new \RuntimeException('ADMIN_PASSWORD wajib diisi di .env sebelum db:seed.');
        }
        Akun::create(['email' => 'admin@silab.test', 'password' => $passwordAdmin, 'role' => 'laboran', 'wajib_ganti_password' => true]);
        Laboran::create(['id_pegawai' => 'LAB0001', 'nama' => 'Administrator', 'email' => 'admin@silab.test', 'no_whatsapp' => null]);
    }
}
