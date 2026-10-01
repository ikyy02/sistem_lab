<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Kategori/Status/Kondisi/Jenis/Prodi bersifat statis: tabel master_options dan kolom kategori dibuang. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('master_options');
        if (Schema::hasColumn('alat_bahans', 'kategori')) {
            Schema::table('alat_bahans', fn (Blueprint $t) => $t->dropColumn('kategori'));
        }
    }

    public function down(): void
    {
    }
};
