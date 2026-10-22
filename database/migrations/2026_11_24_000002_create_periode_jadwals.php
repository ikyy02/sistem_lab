<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Periode berlaku jadwal perkuliahan. Setiap jadwal menunjuk satu periode; periode lama menjadi arsip (hanya-baca)
 * saat periode baru dimulai. Jadwal yang sudah ada dikelompokkan per (tahun akademik, semester); periode dengan
 * tanggal berakhir paling akhir menjadi periode berjalan, sisanya arsip.
 */
return new class extends Migration
{
    // Membuat tabel periode dan mengelompokkan jadwal lama ke periode otomatis.
    public function up(): void
    {
        Schema::create('periode_jadwals', function (Blueprint $t) {
            $t->id('id_periode');
            $t->string('nama', 100);
            $t->date('tanggal_mulai');
            $t->date('tanggal_berakhir');
            $t->enum('status', ['berjalan', 'arsip'])->default('berjalan');
            $t->index('status', 'ix_periode_jadwals_status');
        });

        if (! Schema::hasTable('jadwals')) {
            return;
        }
        Schema::table('jadwals', function (Blueprint $t) {
            $t->unsignedBigInteger('id_periode')->nullable();
            $t->index('id_periode', 'ix_jadwals_periode');
            if (in_array(DB::getDriverName(), ['mysql', 'mariadb', 'pgsql'], true)) {
                $t->foreign('id_periode', 'fk_jadwals_periode')->references('id_periode')->on('periode_jadwals')->restrictOnDelete()->cascadeOnUpdate();
            }
        });

        $kelompok = DB::table('jadwals')->select('tahun_akademik', 'semester')->distinct()->get();
        $dibuat = [];
        foreach ($kelompok as $k) {
            if (! preg_match('/^(\d{4})\/(\d{4})$/', (string) $k->tahun_akademik, $m)) {
                continue;
            }
            [$mulai, $berakhir] = $k->semester === 'ganjil'
                ? [$m[1] . '-08-01', $m[2] . '-01-31']
                : [$m[2] . '-02-01', $m[2] . '-07-31'];
            $id = DB::table('periode_jadwals')->insertGetId([
                'nama' => ucfirst($k->semester) . ' ' . $k->tahun_akademik,
                'tanggal_mulai' => $mulai, 'tanggal_berakhir' => $berakhir, 'status' => 'arsip',
            ], 'id_periode');
            DB::table('jadwals')->where('tahun_akademik', $k->tahun_akademik)->where('semester', $k->semester)->update(['id_periode' => $id]);
            $dibuat[$id] = $berakhir;
        }
        if ($dibuat) {
            arsort($dibuat);
            DB::table('periode_jadwals')->where('id_periode', array_key_first($dibuat))->update(['status' => 'berjalan']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('jadwals') && Schema::hasColumn('jadwals', 'id_periode')) {
            Schema::table('jadwals', function (Blueprint $t) {
                if (in_array(DB::getDriverName(), ['mysql', 'mariadb', 'pgsql'], true)) {
                    $t->dropForeign('fk_jadwals_periode');
                }
                $t->dropIndex('ix_jadwals_periode');
                $t->dropColumn('id_periode');
            });
        }
        Schema::dropIfExists('periode_jadwals');
    }
};
