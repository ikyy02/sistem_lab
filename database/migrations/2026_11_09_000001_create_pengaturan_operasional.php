<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Pengaturan operasional laboratorium: satu baris pengaturan (jam + hari) dan daftar tanggal libur. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_operasional', function (Blueprint $t) {
            $t->unsignedTinyInteger('id')->primary();
            $t->time('jam_buka');
            $t->time('jam_tutup');
            $t->string('hari_operasional', 100); // daftar hari dipisah koma, mis. senin,selasa,rabu
        });

        Schema::create('hari_libur', function (Blueprint $t) {
            $t->id('id_libur');
            $t->date('tanggal');
            $t->string('keterangan', 100);
            $t->unique('tanggal', 'uq_hari_libur_tanggal');
        });

        DB::table('pengaturan_operasional')->insert([
            'id' => 1, 'jam_buka' => '08:00:00', 'jam_tutup' => '16:00:00', 'hari_operasional' => 'senin,selasa,rabu,kamis,jumat',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hari_libur');
        Schema::dropIfExists('pengaturan_operasional');
    }
};
