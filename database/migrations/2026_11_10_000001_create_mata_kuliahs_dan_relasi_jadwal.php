<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel master Mata Kuliah + relasi ke Jadwal Perkuliahan.
 *
 * Sebelumnya tidak ada tabel mata kuliah: jadwal hanya menunjuk kelas (rombel), sehingga
 * daftar jadwal tidak bisa menampilkan nama mata kuliah. Kolom jadwals.id_mata_kuliah dibuat
 * nullable agar jadwal lama (dan data uji) tetap valid tanpa mata kuliah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_kuliahs', function (Blueprint $t) {
            $t->id('id_mata_kuliah');
            $t->string('kode_mk', 15);
            $t->string('nama_mk', 100);
            $t->unsignedTinyInteger('sks');
            $t->enum('semester', ['ganjil', 'genap']);
            $t->unique('kode_mk', 'uq_mata_kuliahs_kode_mk');
            $t->index('nama_mk', 'ix_mata_kuliahs_nama');
        });

        // Struktur jadwals dibuat oleh migration revisi final; dilewati bila belum tersedia
        // (pengujian migrasi data lama menjalankan migration ini sebelum struktur final).
        if (Schema::hasTable('jadwals')) {
            Schema::table('jadwals', function (Blueprint $t) {
                $t->unsignedBigInteger('id_mata_kuliah')->nullable();
                $t->index('id_mata_kuliah', 'ix_jadwals_matakuliah');
                // SQLite tidak mendukung penambahan FK pada tabel yang sudah ada (dipakai untuk pengujian).
                if (in_array(DB::getDriverName(), ['mysql', 'mariadb', 'pgsql'], true)) {
                    $t->foreign('id_mata_kuliah', 'fk_jadwals_matakuliah')
                        ->references('id_mata_kuliah')->on('mata_kuliahs')->restrictOnDelete()->cascadeOnUpdate();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('jadwals') && Schema::hasColumn('jadwals', 'id_mata_kuliah')) {
            Schema::table('jadwals', function (Blueprint $t) {
                if (in_array(DB::getDriverName(), ['mysql', 'mariadb', 'pgsql'], true)) {
                    $t->dropForeign('fk_jadwals_matakuliah');
                }
                $t->dropIndex('ix_jadwals_matakuliah');
                $t->dropColumn('id_mata_kuliah');
            });
        }
        Schema::dropIfExists('mata_kuliahs');
    }
};
