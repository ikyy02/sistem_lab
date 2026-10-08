<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penghubung Mahasiswa <-> Kelas per periode akademik (tahun_akademik + semester).
 *
 * Alasan struktur baru:
 * - Tabel mahasiswas sengaja TIDAK punya kolom kelas (dilarang StrukturDatabaseTest) dan kelas mahasiswa
 *   berpindah tiap semester, sehingga kepemilikan kelas tidak boleh disimpan sebagai kolom tetap.
 * - Jadwal bergantung pada (kelas, tahun_akademik, semester); tanpa relasi ini jadwal kuliah mahasiswa
 *   tidak dapat ditampilkan. Dengan tabel relasi, satu mahasiswa dapat punya kelas berbeda per semester.
 * - Unique (nim, tahun_akademik, semester) menjamin satu mahasiswa hanya punya satu kelas pada satu semester.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kelas_mahasiswas')) {
            return; // sudah dibuat (migrasi dijalankan berulang / terpisah)
        }
        Schema::create('kelas_mahasiswas', function (Blueprint $t) {
            $t->id('id_kelas_mahasiswa');
            $t->string('nim', 20);
            $t->unsignedBigInteger('id_kelas');
            $t->string('tahun_akademik', 9); // format 2026/2027
            $t->enum('semester', ['ganjil', 'genap']);
            $t->unique(['nim', 'tahun_akademik', 'semester'], 'uq_kelas_mahasiswas_nim_periode');
            $t->index('id_kelas', 'ix_kelas_mahasiswas_kelas');
            $t->foreign('nim', 'fk_kelas_mahasiswas_mahasiswa')->references('nim')->on('mahasiswas')->restrictOnDelete()->cascadeOnUpdate();
            $t->foreign('id_kelas', 'fk_kelas_mahasiswas_kelas')->references('id_kelas')->on('kelas')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas_mahasiswas');
    }
};
