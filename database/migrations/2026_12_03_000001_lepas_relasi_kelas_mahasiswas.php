<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mahasiswa tidak lagi terhubung ke Kelas/Mata Kuliah (kelas_mahasiswas tidak dipakai kode mana pun).
 * Karena FK-nya RESTRICT, baris lama akan memblokir penghapusan Mahasiswa maupun Kelas.
 * Aman untuk data berjalan:
 * - tabel kosong  -> dibuang;
 * - tabel berisi  -> data DIPERTAHANKAN sebagai arsip, hanya kedua FK-nya dilepas (tidak ada baris yang dihapus).
 * Tabel kelas, mata_kuliahs, dan jadwals tidak disentuh.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('kelas_mahasiswas')) {
            return;
        }
        if (DB::table('kelas_mahasiswas')->doesntExist()) {
            Schema::drop('kelas_mahasiswas');

            return;
        }
        // SQLite hanya mendukung drop FK lewat kolom; MySQL memakai nama constraint dari migrasi pembuatnya.
        $sqlite = DB::getDriverName() === 'sqlite';
        Schema::table('kelas_mahasiswas', function (Blueprint $t) use ($sqlite) {
            $t->dropForeign($sqlite ? ['nim'] : 'fk_kelas_mahasiswas_mahasiswa');
            $t->dropForeign($sqlite ? ['id_kelas'] : 'fk_kelas_mahasiswas_kelas');
        });
    }

    public function down(): void
    {
        // Sengaja kosong: relasi ini dihentikan; struktur lama masih dibuat/dibuang oleh migrasi 2026_11_17.
    }
};
