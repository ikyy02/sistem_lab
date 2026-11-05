<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alat_bahans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jenis')->default('alat')->index();
            $table->string('satuan');
            $table->integer('stok')->default(0);
            $table->string('kondisi')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alat_bahans');
    }
};