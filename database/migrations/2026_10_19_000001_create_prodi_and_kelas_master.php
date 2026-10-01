<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Master data Prodi & Kelas untuk dropdown pada Kelola Data User (Mahasiswa/Dosen/Staff Prodi).
 * Sebelumnya daftar tetap di app/Support/Options.php -> dipindah ke tabel agar bisa dikelola Admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prodis', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->timestamps();
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 20)->unique();
            $table->timestamps();
        });

        foreach ([
            'D3 Teknologi Informasi',
            'D4 Teknologi Rekayasa Komputer Jaringan',
            'D3 Akuntansi',
            'D4 Bisnis Digital',
        ] as $nama) {
            DB::table('prodis')->insert(['nama' => $nama, 'created_at' => now(), 'updated_at' => now()]);
        }

        foreach ([1, 2, 3, 4] as $tingkat) {
            foreach (['A', 'B', 'C', 'D', 'E'] as $rombel) {
                DB::table('kelas')->insert(['nama' => $tingkat . $rombel, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('prodis');
    }
};
