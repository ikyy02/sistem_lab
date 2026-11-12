<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sops', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10);
            $table->string('judul');
            $table->text('deskripsi');
            $table->text('tujuan');
            $table->json('poin');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();

            $table->unique('kode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sops');
    }
};
