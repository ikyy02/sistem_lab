<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Konversi satuan asli -> unit, khusus untuk perhitungan TPK/SPK (SRS D.2).
 * Katalog & stok tetap memakai satuan asli; kolom ini tidak mengubah tampilan Katalog.
 * Hanya relevan untuk jenis Alat/Bahan (Ruangan tidak ikut TPK/SPK).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alat_bahans', function (Blueprint $table) {
            $table->decimal('per_unit', 12, 2)->nullable()->after('satuan')
                ->comment('Jumlah satuan asli untuk 1 unit, mis. 5 (meter) per unit');
            $table->decimal('harga_total', 14, 2)->nullable()->after('keterangan')
                ->comment('Harga aktual/pasar untuk sejumlah unit_dasar_harga');
            $table->decimal('unit_dasar_harga', 12, 2)->nullable()->after('harga_total')
                ->comment('Jumlah unit yang menjadi dasar harga_total');
        });
    }

    public function down(): void
    {
        Schema::table('alat_bahans', function (Blueprint $table) {
            $table->dropColumn(['per_unit', 'harga_total', 'unit_dasar_harga']);
        });
    }
};
