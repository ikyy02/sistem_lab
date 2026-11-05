<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SRS D.8: pengajuan Kuliah/Praktikum (atas_dosen) diputuskan Dosen lebih dulu, lalu diteruskan ke Laboran/Admin
 * untuk keputusan akhir. tanggal_persetujuan_dosen terisi saat Dosen menyetujui (pengajuan lalu menunggu Laboran);
 * status tetap "menunggu" sampai Laboran memutuskan. Kolom nullable: data lama tidak berubah.
 */
return new class extends Migration
{
    // Kolom waktu persetujuan dosen untuk alur dua tahap.
    public function up(): void
    {
        if (! Schema::hasTable('peminjamans') || Schema::hasColumn('peminjamans', 'tanggal_persetujuan_dosen')) {
            return;
        }
        Schema::table('peminjamans', function (Blueprint $t) {
            $t->dateTime('tanggal_persetujuan_dosen')->nullable()->after('tanggal_persetujuan');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('peminjamans') && Schema::hasColumn('peminjamans', 'tanggal_persetujuan_dosen')) {
            Schema::table('peminjamans', fn (Blueprint $t) => $t->dropColumn('tanggal_persetujuan_dosen'));
        }
    }
};
