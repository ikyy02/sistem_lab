<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_dosen', function (Blueprint $table) {
            $table->id('id_dosen');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nip_nidn', 50)->unique();
            $table->string('nama', 255);
            $table->string('program_studi', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_dosen');
    }
};
