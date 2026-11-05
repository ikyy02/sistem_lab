<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_peminjaman')->index();
            $table->enum('jenis', ['terlambat'])->default('terlambat');
            $table->tinyInteger('ke_ke'); // 1, 2, 3, ...
            $table->string('nomor', 20); // Nomor WhatsApp (disamarkan saat tampil)
            $table->enum('status', ['terkirim', 'gagal', 'dilewati'])->default('dilewati');
            $table->dateTime('dikirim_at')->nullable();
            $table->timestamps();

            $table->unique(['id_peminjaman', 'ke_ke']);
            $table->foreign('id_peminjaman')->references('id_peminjaman')->on('peminjamans')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_logs');
    }
};
