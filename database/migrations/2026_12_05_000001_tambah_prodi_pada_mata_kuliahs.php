<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Mata Kuliah dimiliki satu prodi (mata_kuliahs.id_prodi) agar Staf Prodi hanya mengelola miliknya.
 *
 * Pemetaan data lama hanya dari fakta di database (jadwals -> kelas -> prodi), tidak ada tebakan:
 *  - dipakai jadwal satu prodi         -> id_prodi diisi prodi itu;
 *  - dipakai jadwal beberapa prodi     -> baris asli untuk prodi berid terkecil, prodi lain mendapat salinan
 *                                         (kode, nama, SKS, semester sama) dan jadwalnya dipindah ke salinan itu;
 *  - tidak dipakai jadwal mana pun     -> id_prodi dibiarkan NULL (tidak diketahui), menunggu keputusan Laboran.
 * Tidak ada baris yang dihapus atau ditimpa. Kasus selain "satu prodi" dicatat di mata_kuliah_pemetaan_log
 * dan log aplikasi. Karena salinan lintas prodi memakai kode yang sama, keunikan kode berubah dari global
 * menjadi per prodi.
 */
return new class extends Migration
{
    private function punyaFk(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb', 'pgsql'], true);
    }

    public function up(): void
    {
        Schema::table('mata_kuliahs', function (Blueprint $t) {
            $t->unsignedBigInteger('id_prodi')->nullable();
            $t->index('id_prodi', 'ix_mata_kuliahs_prodi');
            if ($this->punyaFk()) {
                $t->foreign('id_prodi', 'fk_mata_kuliahs_prodi')->references('id_prodi')->on('prodis')->restrictOnDelete()->cascadeOnUpdate();
            }
        });
        Schema::table('mata_kuliahs', function (Blueprint $t) {
            $t->dropUnique('uq_mata_kuliahs_kode_mk');
            $t->unique(['id_prodi', 'kode_mk'], 'uq_mata_kuliahs_prodi_kode');
        });

        Schema::create('mata_kuliah_pemetaan_log', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('id_mata_kuliah');          // baris asli (sebelum dipecah)
            $t->unsignedBigInteger('id_mata_kuliah_baru')->nullable(); // salinan untuk prodi lain; NULL = baris asli
            $t->unsignedBigInteger('id_prodi')->nullable();
            $t->string('kode_mk', 15);
            $t->string('nama_mk', 100);
            $t->string('status', 20); // dipecah | tanpa_jadwal
            $t->unsignedInteger('jumlah_jadwal')->default(0);
            $t->string('keterangan', 255);
        });

        if (! Schema::hasColumn('jadwals', 'id_mata_kuliah')) {
            return;
        }
        DB::transaction(fn () => $this->petakan());
    }

    private function petakan(): void
    {
        foreach (DB::table('mata_kuliahs')->whereNull('id_prodi')->orderBy('id_mata_kuliah')->get() as $mk) {
            $dipakai = DB::table('jadwals')->join('kelas', 'kelas.id_kelas', '=', 'jadwals.id_kelas')
                ->where('jadwals.id_mata_kuliah', $mk->id_mata_kuliah)
                ->groupBy('kelas.id_prodi')->orderBy('kelas.id_prodi')
                ->selectRaw('kelas.id_prodi, COUNT(*) as jumlah')->get();

            if ($dipakai->isEmpty()) {
                $this->catat($mk, null, null, 'tanpa_jadwal', 0, 'Tidak dipakai jadwal; prodi tidak dapat ditentukan, id_prodi dibiarkan NULL.');
                Log::warning("Mata kuliah {$mk->kode_mk} (id {$mk->id_mata_kuliah}) tanpa jadwal: id_prodi dibiarkan NULL.");

                continue;
            }

            $asli = $dipakai->shift();
            DB::table('mata_kuliahs')->where('id_mata_kuliah', $mk->id_mata_kuliah)->update(['id_prodi' => $asli->id_prodi]);
            if ($dipakai->isEmpty()) {
                continue; // dipakai satu prodi: pemetaan pasti
            }

            $this->catat($mk, null, $asli->id_prodi, 'dipecah', $asli->jumlah, 'Dipakai beberapa prodi; baris asli dipertahankan untuk prodi ini.');
            foreach ($dipakai as $p) {
                $baru = (int) DB::table('mata_kuliahs')->insertGetId([
                    'kode_mk' => $mk->kode_mk, 'nama_mk' => $mk->nama_mk, 'sks' => $mk->sks, 'semester' => $mk->semester, 'id_prodi' => $p->id_prodi,
                ], 'id_mata_kuliah');
                DB::table('jadwals')->where('id_mata_kuliah', $mk->id_mata_kuliah)
                    ->whereIn('id_kelas', fn ($q) => $q->select('id_kelas')->from('kelas')->where('id_prodi', $p->id_prodi))
                    ->update(['id_mata_kuliah' => $baru]);
                $this->catat($mk, $baru, $p->id_prodi, 'dipecah', $p->jumlah, "Salinan untuk prodi {$p->id_prodi}; jadwal prodi ini dipindah ke id {$baru}.");
            }
            Log::warning("Mata kuliah {$mk->kode_mk} (id {$mk->id_mata_kuliah}) dipakai beberapa prodi: dipecah per prodi.");
        }
    }

    private function catat(object $mk, ?int $baru, ?int $prodi, string $status, int $jumlah, string $ket): void
    {
        DB::table('mata_kuliah_pemetaan_log')->insert([
            'id_mata_kuliah' => $mk->id_mata_kuliah, 'id_mata_kuliah_baru' => $baru, 'id_prodi' => $prodi,
            'kode_mk' => $mk->kode_mk, 'nama_mk' => $mk->nama_mk, 'status' => $status, 'jumlah_jadwal' => $jumlah, 'keterangan' => $ket,
        ]);
    }

    public function down(): void
    {
        // Gabungkan kembali salinan hasil pemecahan ke baris asli sebelum keunikan kode global dipulihkan.
        if (Schema::hasColumn('jadwals', 'id_mata_kuliah')) {
            foreach (DB::table('mata_kuliah_pemetaan_log')->whereNotNull('id_mata_kuliah_baru')->get() as $l) {
                DB::table('jadwals')->where('id_mata_kuliah', $l->id_mata_kuliah_baru)->update(['id_mata_kuliah' => $l->id_mata_kuliah]);
                DB::table('mata_kuliahs')->where('id_mata_kuliah', $l->id_mata_kuliah_baru)->delete();
            }
        }
        Schema::dropIfExists('mata_kuliah_pemetaan_log');
        Schema::table('mata_kuliahs', function (Blueprint $t) {
            $t->dropUnique('uq_mata_kuliahs_prodi_kode');
            if ($this->punyaFk()) {
                $t->dropForeign('fk_mata_kuliahs_prodi');
            }
            $t->dropIndex('ix_mata_kuliahs_prodi');
        });
        Schema::table('mata_kuliahs', function (Blueprint $t) {
            $t->dropColumn('id_prodi');
            $t->unique('kode_mk', 'uq_mata_kuliahs_kode_mk');
        });
    }
};
