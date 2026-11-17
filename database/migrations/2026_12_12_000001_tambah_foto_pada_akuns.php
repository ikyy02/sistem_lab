<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Foto profil pengguna: nama file disimpan pada akuns, filenya di disk 'public' (storage/app/public/profil). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akuns', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('akuns', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
