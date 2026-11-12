<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Dosen;
use App\Models\Prodi;
use App\Support\Role;
use Illuminate\Database\Seeder;

class DosenSeeder extends Seeder
{
    public function run(): void
    {
        $prodiTI = Prodi::firstOrCreate(['nama_prodi' => 'D3 Teknologi Informasi']);
        $prodiTRKJ = Prodi::firstOrCreate(['nama_prodi' => 'D4 Teknologi Rekayasa Komputer Jaringan']);
        $prodiBD = Prodi::firstOrCreate(['nama_prodi' => 'D4 Bisnis Digital']);

        $dosens = [
            [
                'nuptk_nidn' => '0012058501',
                'nama' => 'Dr. Surya Wijaya, M.Kom.',
                'id_prodi' => $prodiTRKJ->id_prodi,
                'email' => 'dosen1@silab.test',
                'no_whatsapp' => '081234567890',
                'status' => 'aktif',
            ],
            [
                'nuptk_nidn' => '0015088201',
                'nama' => 'Dr. Ahmad Fauzi, M.T.',
                'id_prodi' => $prodiTI->id_prodi,
                'email' => 'ahmad.fauzi@politala.ac.id',
                'no_whatsapp' => '082155667788',
                'status' => 'aktif',
            ],
            [
                'nuptk_nidn' => '0021048802',
                'nama' => 'Rina Marlina, M.Cs.',
                'id_prodi' => $prodiTI->id_prodi,
                'email' => 'rina.marlina@politala.ac.id',
                'no_whatsapp' => '085244332211',
                'status' => 'aktif',
            ],
            [
                'nuptk_nidn' => '0018099003',
                'nama' => 'Muhammad Ghalih, M.Kom.',
                'id_prodi' => $prodiBD->id_prodi,
                'email' => 'muhammad.ghalih@politala.ac.id',
                'no_whatsapp' => '081399887766',
                'status' => 'aktif',
            ],
        ];

        foreach ($dosens as $d) {
            Akun::firstOrCreate(
                ['email' => $d['email']],
                ['password' => 'dosen123', 'role' => Role::DOSEN]
            );

            Dosen::firstOrCreate(
                ['nuptk_nidn' => $d['nuptk_nidn']],
                $d
            );
        }
    }
}
