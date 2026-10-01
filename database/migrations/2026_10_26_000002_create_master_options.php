<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kelola Data Master: pilihan tetap Kategori, Status (ruangan), Kondisi (alat/bahan) disimpan di master_options.
 * Satuan, Kelas, Prodi tetap di tabel masing-masing. Jenis (alat/bahan/ruangan) adalah struktur sistem.
 * alat_bahans.kategori = kategori barang (dropdown dari master Kategori).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_options', function (Blueprint $table) {
            $table->id();
            $table->string('tipe', 20);
            $table->string('grup', 20)->default('');
            $table->string('nama', 100);
            $table->timestamps();
            $table->unique(['tipe', 'grup', 'nama']);
        });

        Schema::table('alat_bahans', function (Blueprint $table) {
            $table->string('kategori', 100)->nullable()->after('nama');
        });

        $seed = [
            ['kondisi', 'alat', ['Baik', 'Rusak', 'Hilang']],
            ['kondisi', 'bahan', ['Baik', 'Rusak', 'Kadaluarsa']],
            ['status', 'ruangan', ['Tersedia', 'Tidak Tersedia']],
        ];
        foreach ($seed as [$tipe, $grup, $names]) {
            $names = collect($names)
                ->merge(DB::table('alat_bahans')->where('jenis', $grup)->whereNotNull('kondisi')->where('kondisi', '!=', '')->distinct()->pluck('kondisi'))
                ->map(fn ($n) => trim($n))->filter()->unique(fn ($n) => mb_strtolower($n));
            foreach ($names as $nama) {
                DB::table('master_options')->insert(['tipe' => $tipe, 'grup' => $grup, 'nama' => $nama, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('alat_bahans', fn (Blueprint $t) => $t->dropColumn('kategori'));
        Schema::dropIfExists('master_options');
    }
};
