<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Prodi;
use App\Models\StaffProdi;
use App\Support\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StaffProdiSeeder extends Seeder
{
    public function run(): void
    {
        $prodiTI = Prodi::firstOrCreate(['nama_prodi' => 'D3 Teknologi Informasi']);

        Akun::firstOrCreate(
            ['email' => 'staff@silab.test'],
            ['password' => app()->environment('local') ? 'staff123' : Str::random(16), 'role' => Role::STAFF]
        );

        StaffProdi::firstOrCreate(
            ['id_pegawai' => 'STF0001'],
            [
                'nama' => 'Staf Administrasi TI',
                'id_prodi' => $prodiTI->id_prodi,
                'email' => 'staff@silab.test',
                'no_whatsapp' => '081234567899',
            ]
        );
    }
}
