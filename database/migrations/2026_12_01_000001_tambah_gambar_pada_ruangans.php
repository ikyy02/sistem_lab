<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Foto ruangan/laboratorium untuk landing page; bersumber dari database, bukan dari situs luar. */
return new class extends Migration
{
    // Kolom gambar ruangan (nama file saja; lokasi file lihat GambarKatalog).
    public function up(): void
    {
        if (! Schema::hasColumn('ruangans', 'gambar')) {
            Schema::table('ruangans', function (Blueprint $t) {
                $t->string('gambar', 100)->nullable()->after('keterangan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ruangans', 'gambar')) {
            Schema::table('ruangans', function (Blueprint $t) {
                $t->dropColumn('gambar');
            });
        }
    }
};
