<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Buang tabel hari_libur: fitur Hari/Tanggal Libur dihentikan karena tidak diperlukan.
 * Tabel dibuat oleh migration pengaturan operasional (2026_11_09) dan sudah berjalan,
 * sehingga pembuangan dilakukan lewat migration terpisah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('hari_libur');
    }

    public function down(): void
    {
        if (Schema::hasTable('hari_libur')) {
            return;
        }

        Schema::create('hari_libur', function (Blueprint $t) {
            $t->id('id_libur');
            $t->date('tanggal');
            $t->string('keterangan', 100);
            $t->unique('tanggal', 'uq_hari_libur_tanggal');
        });
    }
};
