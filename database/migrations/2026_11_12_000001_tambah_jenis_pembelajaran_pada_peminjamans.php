<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Jenis peminjaman "pembelajaran" untuk pengajuan yang dibuat Dosen
 * (nilai enum ditambahkan, kolom & data lama tidak diubah).
 *
 * - MySQL/MariaDB : enum didefinisikan saat pembuatan tabel, jadi database lama perlu ALTER.
 * - SQLite        : enum berupa CHECK inline yang sudah mengikuti migrasi pembuatan tabel.
 */
return new class extends Migration
{
    /** Nilai enum jenis_peminjaman setelah migrasi ini. */
    private const BARU = ['pribadi', 'atas_dosen', 'pembelajaran'];

    /** Nilai enum sebelum migrasi ini. */
    private const LAMA = ['pribadi', 'atas_dosen'];

    public function up(): void
    {
        if ($this->lewati()) {
            return;
        }
        // MODIFY hanya memperluas daftar nilai: seluruh data lama tetap valid dan tidak diubah.
        DB::statement('ALTER TABLE peminjamans MODIFY jenis_peminjaman ENUM('.$this->daftar(self::BARU).') NOT NULL');
    }

    public function down(): void
    {
        if ($this->lewati()) {
            return;
        }
        // "pembelajaran" tidak ada pada enum lama; padanan terdekat adalah "pribadi" (tanpa dosen penanggung jawab),
        // sehingga baris tidak hilang dan tidak melanggar enum saat daftar nilai dipersempit.
        DB::table('peminjamans')->where('jenis_peminjaman', 'pembelajaran')->update(['jenis_peminjaman' => 'pribadi']);
        DB::statement('ALTER TABLE peminjamans MODIFY jenis_peminjaman ENUM('.$this->daftar(self::LAMA).') NOT NULL');
    }

    private function lewati(): bool
    {
        return ! Schema::hasTable('peminjamans') || ! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }

    /** ['a','b'] -> 'a','b' (untuk isi ENUM(...)). */
    private function daftar(array $nilai): string
    {
        return "'".implode("','", $nilai)."'";
    }
};
