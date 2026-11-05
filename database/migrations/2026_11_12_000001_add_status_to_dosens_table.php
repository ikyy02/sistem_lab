<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom status pada tabel dosens (aktif/nonaktif).
     * Struktur tabel dosens yang sudah ada tidak diubah, hanya ditambah kolom default
     * sehingga data yang sudah tersimpan dan relasi jadwals tetap aman.
     */
    public function up(): void
    {
        Schema::table('dosens', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('no_whatsapp');
        });
    }

    public function down(): void
    {
        Schema::table('dosens', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};