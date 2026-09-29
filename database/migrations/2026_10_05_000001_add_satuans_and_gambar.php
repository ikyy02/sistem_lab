<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Part 2: tabel satuan (dipakai dropdown Alat/Bahan) + kolom gambar pada alat_bahans.
 * Lokasi barang tetap disimpan di kolom `keterangan` yang sudah ada.
 * Ruangan memakai kolom yang sama: `stok` = kapasitas, `kondisi` = status ketersediaan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satuans', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->unique();
            $table->timestamps();
        });

        Schema::table('alat_bahans', function (Blueprint $table) {
            $table->string('gambar')->nullable()->after('keterangan');
        });

        // Satuan awal + satuan yang sudah dipakai data lama.
        $names = collect(['Unit', 'Buah', 'Pcs', 'Set', 'Botol', 'Pak', 'Meter', 'Liter', 'Kilogram', 'Gram'])
            ->merge(DB::table('alat_bahans')->where('jenis', '!=', 'ruangan')->whereNotNull('satuan')->where('satuan', '!=', '')->distinct()->pluck('satuan'))
            ->map(fn ($n) => trim($n))
            ->filter()
            ->unique(fn ($n) => mb_strtolower($n));

        foreach ($names as $nama) {
            DB::table('satuans')->insert(['nama' => $nama, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('alat_bahans', fn (Blueprint $t) => $t->dropColumn('gambar'));
        Schema::dropIfExists('satuans');
    }
};
