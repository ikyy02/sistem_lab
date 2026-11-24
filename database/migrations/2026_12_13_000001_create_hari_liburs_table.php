<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hari_liburs')) {
            Schema::create('hari_liburs', function (Blueprint $table) {
                $table->id('id_libur');
                $table->date('tanggal')->unique();
                $table->string('keterangan', 150);
                $table->enum('jenis', ['libur_nasional', 'cuti_bersama', 'libur_khusus'])->default('libur_nasional');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hari_liburs');
    }
};
