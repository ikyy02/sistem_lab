<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Penanda akun yang wajib mengganti password awalnya (mis. mahasiswa hasil import dengan password = NIM).
    public function up(): void
    {
        Schema::table('akuns', function (Blueprint $table) {
            $table->boolean('wajib_ganti_password')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('akuns', function (Blueprint $table) {
            $table->dropColumn('wajib_ganti_password');
        });
    }
};
