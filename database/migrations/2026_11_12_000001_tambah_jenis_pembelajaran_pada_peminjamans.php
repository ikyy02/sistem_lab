<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Jenis peminjaman "pembelajaran" untuk pengajuan yang dibuat Dosen
 * (nilai enum ditambahkan, kolom & data lama tidak diubah).
 *
 * - MySQL/MariaDB : enum didefinisikan saat pembuatan tabel, jadi perlu ALTER.
 * - SQLite        : enum berupa CHECK inline yang sudah mengikuti migrasi pembuatan tabel.
 */
return new class extends Migration
{
    private const NILAI = "'pribadi','atas_dosen','pembelajaran'";

    public function up(): void
    {
        if (! Schema::hasTable('peminjamans') || ! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }
        DB::statement('ALTER TABLE peminjamans MODIFY jenis_peminjaman ENUM({self::NILAI}) NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('peminjamans') || ! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }
        // Kembalikan ke enum lama; baris berjenis "pembelajaran" harus diubah/dihapus manual.
        DB::statement("ALTER TABLE peminjamans MODIFY jenis_peminjaman ENUM('pribadi','atas_dosen') NOT NULL");
    }
};
