<?php

use Illuminate\Database\Migrations\Migration;

/** Dibatalkan: pilihan Kategori/Status/Kondisi bersifat statis. Dipertahankan kosong agar riwayat migration tetap konsisten. */
return new class extends Migration
{
    // Sengaja kosong (rencana dibatalkan), dipertahankan supaya urutan migrasi konsisten.
    public function up(): void
    {
    }

    public function down(): void
    {
    }
};
