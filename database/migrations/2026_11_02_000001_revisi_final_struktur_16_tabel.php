<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Revisi final: struktur domain tepat 16 tabel.
 *
 * akuns, mahasiswas, dosens, staff_prodis, laborans, prodis, kelas, satuans, ruangans, alat_bahans,
 * peminjamans, peminjaman_details, peminjaman_ruangans, pengembalians, pengembalian_details, jadwals.
 *
 * Migration lama tidak diubah. Tabel lama diganti nama menjadi *_lama, tabel baru dibuat, data disalin
 * (email+password -> akuns [password apa adanya], nama prodi -> id_prodi, Ruangan dipisah dari alat_bahans),
 * lalu tabel lama dibuang. Tabel bawaan `users` dan `password_reset_tokens` tidak dipakai dan dibuang.
 */
return new class extends Migration
{
    private const LAMA = ['mahasiswas', 'dosens', 'staff_prodis', 'laborans', 'prodis', 'kelas', 'satuans', 'alat_bahans'];

    public function up(): void
    {
        foreach (self::LAMA as $t) {
            Schema::rename($t, $t . '_lama');
        }

        $this->buatTabel();
        $this->salinData();

        foreach (self::LAMA as $t) {
            Schema::drop($t . '_lama');
        }
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');

        $this->pasangCheck();

        $lama = public_path('uploads/inventaris');
        $baru = public_path('uploads/katalog');
        if (File::isDirectory($lama) && ! File::exists($baru)) {
            File::moveDirectory($lama, $baru);
        }
    }

    public function down(): void
    {
        throw new \RuntimeException('Migration revisi final tidak dapat di-rollback. Pulihkan dari backup database.');
    }

    private function buatTabel(): void
    {
        Schema::create('akuns', function (Blueprint $t) {
            $t->string('email', 50)->primary();
            $t->string('password', 255);
            $t->enum('role', ['mahasiswa', 'dosen', 'staff_prodi', 'laboran']);
        });

        Schema::create('prodis', function (Blueprint $t) {
            $t->id('id_prodi');
            $t->string('nama_prodi', 50);
            $t->unique('nama_prodi', 'uq_prodis_nama_prodi');
        });

        Schema::create('kelas', function (Blueprint $t) {
            $t->id('id_kelas');
            $t->string('nama_kelas', 100);
            $t->unsignedBigInteger('id_prodi');
            $t->foreign('id_prodi', 'fk_kelas_prodi')->references('id_prodi')->on('prodis')->restrictOnDelete()->cascadeOnUpdate();
            $t->unique(['id_prodi', 'nama_kelas'], 'uq_kelas_prodi_nama');
        });

        Schema::create('satuans', function (Blueprint $t) {
            $t->id('id_satuan');
            $t->string('nama_satuan', 50);
            $t->unique('nama_satuan', 'uq_satuans_nama_satuan');
        });

        Schema::create('ruangans', function (Blueprint $t) {
            $t->id('id_ruangan');
            $t->string('nama_ruangan', 50);
            $t->text('keterangan')->nullable();
            $t->unique('nama_ruangan', 'uq_ruangans_nama_ruangan');
        });

        Schema::create('alat_bahans', function (Blueprint $t) {
            $t->id('id_katalog');
            $t->string('nama', 50);
            $t->enum('jenis', ['alat', 'bahan']);
            $t->unsignedBigInteger('id_satuan')->index('ix_alat_bahans_satuan');
            $t->unsignedInteger('stok');
            $t->decimal('harga', 15, 2);
            $t->unsignedBigInteger('id_ruangan')->index('ix_alat_bahans_ruangan');
            $t->string('gambar', 255);
            $t->text('keterangan')->nullable();
            $t->unique('nama', 'uq_alat_bahans_nama');
            $t->index('jenis', 'ix_alat_bahans_jenis');
            $t->foreign('id_satuan', 'fk_alat_bahans_satuan')->references('id_satuan')->on('satuans')->restrictOnDelete()->cascadeOnUpdate();
            $t->foreign('id_ruangan', 'fk_alat_bahans_ruangan')->references('id_ruangan')->on('ruangans')->restrictOnDelete()->cascadeOnUpdate();
        });

        $profil = function (string $tabel, string $pk, int $pkLen, bool $prodi) {
            Schema::create($tabel, function (Blueprint $t) use ($tabel, $pk, $pkLen, $prodi) {
                $t->string($pk, $pkLen)->primary();
                $t->string('nama', 100);
                if ($prodi) {
                    $t->unsignedBigInteger('id_prodi')->index("ix_{$tabel}_prodi");
                    $t->foreign('id_prodi', "fk_{$tabel}_prodi")->references('id_prodi')->on('prodis')->restrictOnDelete()->cascadeOnUpdate();
                }
                $t->string('email', 50);
                $t->string('no_whatsapp', 20)->nullable();
                $t->unique('email', "uq_{$tabel}_email");
                $t->foreign('email', "fk_{$tabel}_akun")->references('email')->on('akuns')->restrictOnDelete()->cascadeOnUpdate();
            });
        };
        $profil('mahasiswas', 'nim', 20, true);
        $profil('dosens', 'nuptk_nidn', 30, true);
        $profil('staff_prodis', 'id_pegawai', 30, true);
        $profil('laborans', 'id_pegawai', 30, false);

        Schema::create('peminjamans', function (Blueprint $t) {
            $t->id('id_peminjaman');
            $t->string('email_peminjam', 50)->index('ix_peminjamans_peminjam');
            $t->enum('jenis_peminjaman', ['pribadi', 'atas_dosen']);
            $t->string('email_dosen', 50)->nullable()->index('ix_peminjamans_dosen');
            $t->dateTime('tanggal_pengajuan');
            $t->dateTime('tanggal_peminjaman');
            $t->dateTime('tanggal_rencana_kembali');
            $t->enum('status', ['menunggu', 'disetujui', 'ditolak', 'selesai']);
            $t->text('keterangan')->nullable();
            $t->string('email_pemroses', 50)->nullable()->index('ix_peminjamans_pemroses');
            $t->index('status', 'ix_peminjamans_status');
            $t->index('tanggal_peminjaman', 'ix_peminjamans_tgl_pinjam');
            $t->foreign('email_peminjam', 'fk_peminjamans_peminjam')->references('email')->on('akuns')->restrictOnDelete()->cascadeOnUpdate();
            $t->foreign('email_dosen', 'fk_peminjamans_dosen')->references('email')->on('akuns')->restrictOnDelete()->cascadeOnUpdate();
            $t->foreign('email_pemroses', 'fk_peminjamans_pemroses')->references('email')->on('akuns')->restrictOnDelete()->cascadeOnUpdate();
        });

        Schema::create('peminjaman_details', function (Blueprint $t) {
            $t->id('id_detail');
            $t->unsignedBigInteger('id_peminjaman');
            $t->unsignedBigInteger('id_katalog')->index('ix_pdetail_katalog');
            $t->unsignedInteger('jumlah');
            $t->unique(['id_peminjaman', 'id_katalog'], 'uq_pdetail_peminjaman_katalog');
            $t->foreign('id_peminjaman', 'fk_pdetail_peminjaman')->references('id_peminjaman')->on('peminjamans')->restrictOnDelete()->cascadeOnUpdate();
            $t->foreign('id_katalog', 'fk_pdetail_katalog')->references('id_katalog')->on('alat_bahans')->restrictOnDelete()->cascadeOnUpdate();
        });

        Schema::create('peminjaman_ruangans', function (Blueprint $t) {
            $t->id('id_peminjaman_ruangan');
            $t->unsignedBigInteger('id_peminjaman');
            $t->unsignedBigInteger('id_ruangan')->index('ix_pruangan_ruangan');
            $t->dateTime('tanggal_mulai');
            $t->dateTime('tanggal_selesai');
            $t->text('keterangan')->nullable();
            $t->unique(['id_peminjaman', 'id_ruangan'], 'uq_pruangan_peminjaman_ruangan');
            $t->foreign('id_peminjaman', 'fk_pruangan_peminjaman')->references('id_peminjaman')->on('peminjamans')->restrictOnDelete()->cascadeOnUpdate();
            $t->foreign('id_ruangan', 'fk_pruangan_ruangan')->references('id_ruangan')->on('ruangans')->restrictOnDelete()->cascadeOnUpdate();
        });

        Schema::create('pengembalians', function (Blueprint $t) {
            $t->id('id_pengembalian');
            $t->unsignedBigInteger('id_peminjaman')->index('ix_pengembalians_peminjaman');
            $t->dateTime('tanggal_pengembalian');
            $t->string('email_penerima', 50)->index('ix_pengembalians_penerima');
            $t->text('keterangan')->nullable();
            $t->foreign('id_peminjaman', 'fk_pengembalians_peminjaman')->references('id_peminjaman')->on('peminjamans')->restrictOnDelete()->cascadeOnUpdate();
            $t->foreign('email_penerima', 'fk_pengembalians_penerima')->references('email')->on('akuns')->restrictOnDelete()->cascadeOnUpdate();
        });

        Schema::create('pengembalian_details', function (Blueprint $t) {
            $t->id('id_detail_pengembalian');
            $t->unsignedBigInteger('id_pengembalian')->index('ix_pgdetail_pengembalian');
            $t->unsignedBigInteger('id_detail_peminjaman')->index('ix_pgdetail_detail_pinjam');
            $t->unsignedInteger('jumlah_dikembalikan');
            $t->enum('kondisi', ['baik', 'rusak'])->nullable();
            $t->text('keterangan')->nullable();
            $t->foreign('id_pengembalian', 'fk_pgdetail_pengembalian')->references('id_pengembalian')->on('pengembalians')->restrictOnDelete()->cascadeOnUpdate();
            $t->foreign('id_detail_peminjaman', 'fk_pgdetail_detail_pinjam')->references('id_detail')->on('peminjaman_details')->restrictOnDelete()->cascadeOnUpdate();
        });

        Schema::create('jadwals', function (Blueprint $t) {
            $t->id('id_jadwal');
            $t->unsignedBigInteger('id_kelas')->index('ix_jadwals_kelas');
            $t->string('nuptk_nidn', 30)->index('ix_jadwals_dosen');
            $t->unsignedBigInteger('id_ruangan');
            $t->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
            $t->time('jam_mulai');
            $t->time('jam_selesai');
            $t->enum('status', ['aktif', 'dibatalkan']);
            $t->string('tahun_akademik', 9);
            $t->enum('semester', ['ganjil', 'genap']);
            $t->index(['id_ruangan', 'hari', 'tahun_akademik', 'semester'], 'ix_jadwals_bentrok');
            $t->foreign('id_kelas', 'fk_jadwals_kelas')->references('id_kelas')->on('kelas')->restrictOnDelete()->cascadeOnUpdate();
            $t->foreign('nuptk_nidn', 'fk_jadwals_dosen')->references('nuptk_nidn')->on('dosens')->restrictOnDelete()->cascadeOnUpdate();
            $t->foreign('id_ruangan', 'fk_jadwals_ruangan')->references('id_ruangan')->on('ruangans')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    private function salinData(): void
    {
        // PRODI (id dipertahankan); prodi yang hanya muncul di data profil ditambahkan otomatis.
        $prodi = [];
        foreach (DB::table('prodis_lama')->orderBy('id')->get() as $r) {
            $nama = mb_substr(trim($r->nama), 0, 50);
            if ($nama === '' || isset($prodi[mb_strtolower($nama)])) {
                continue;
            }
            DB::table('prodis')->insert(['id_prodi' => $r->id, 'nama_prodi' => $nama]);
            $prodi[mb_strtolower($nama)] = (int) $r->id;
        }
        $idProdi = function (?string $nama) use (&$prodi): int {
            $nama = mb_substr(trim((string) $nama), 0, 50) ?: 'Belum Ditentukan';

            return $prodi[mb_strtolower($nama)] ??= (int) DB::table('prodis')->insertGetId(['nama_prodi' => $nama], 'id_prodi');
        };

        // KELAS lama tidak punya prodi -> disalin untuk setiap prodi.
        $namaKelas = DB::table('kelas_lama')->orderBy('id')->pluck('nama');
        foreach ($prodi as $id) {
            foreach ($namaKelas as $nama) {
                DB::table('kelas')->insert(['nama_kelas' => $nama, 'id_prodi' => $id]);
            }
        }

        // SATUAN
        $satuan = [];
        foreach (DB::table('satuans_lama')->orderBy('id')->get() as $r) {
            $nama = mb_substr(trim($r->nama), 0, 50);
            if ($nama === '' || isset($satuan[mb_strtolower($nama)])) {
                continue;
            }
            DB::table('satuans')->insert(['id_satuan' => $r->id, 'nama_satuan' => $nama]);
            $satuan[mb_strtolower($nama)] = (int) $r->id;
        }
        $idSatuan = function (?string $nama) use (&$satuan): int {
            $nama = mb_substr(trim((string) $nama), 0, 50) ?: 'Unit';

            return $satuan[mb_strtolower($nama)] ??= (int) DB::table('satuans')->insertGetId(['nama_satuan' => $nama], 'id_satuan');
        };

        // RUANGAN dipisah dari alat_bahans lama (jenis = ruangan). Kapasitas/status tidak ada pada struktur final.
        $ruangan = [];
        foreach (DB::table('alat_bahans_lama')->where('jenis', 'ruangan')->orderBy('id')->get() as $r) {
            $nama = mb_substr(trim($r->nama), 0, 50);
            if ($nama === '' || isset($ruangan[mb_strtolower($nama)])) {
                continue;
            }
            $ruangan[mb_strtolower($nama)] = (int) DB::table('ruangans')->insertGetId(
                ['nama_ruangan' => $nama, 'keterangan' => $r->keterangan], 'id_ruangan'
            );
        }

        // KATALOG (alat & bahan). Harga per 1 satuan = (harga_total / unit_dasar_harga) / per_unit; tanpa data harga -> 1.00.
        $dipakai = [];
        $ruanganDefault = null;
        foreach (DB::table('alat_bahans_lama')->where('jenis', '!=', 'ruangan')->orderBy('id')->get() as $r) {
            $n = mb_substr(trim($r->nama), 0, 50);
            if (isset($dipakai[mb_strtolower($n)])) {
                $n = mb_substr($n, 0, 40) . ' (' . $r->id . ')';
            }
            $dipakai[mb_strtolower($n)] = true;
            $harga = 1.00;
            if ($r->harga_total !== null && (float) $r->unit_dasar_harga > 0) {
                $h = (float) $r->harga_total / (float) $r->unit_dasar_harga / max(1.0, (float) ($r->per_unit ?: 1));
                $harga = max(0.01, round($h, 2));
            }
            $ruanganDefault ??= ($ruangan['belum ditentukan'] ??= (int) DB::table('ruangans')->insertGetId(
                ['nama_ruangan' => 'Belum Ditentukan', 'keterangan' => null], 'id_ruangan'
            ));
            DB::table('alat_bahans')->insert([
                'id_katalog' => $r->id,
                'nama' => $n,
                'jenis' => $r->jenis === 'bahan' ? 'bahan' : 'alat',
                'id_satuan' => $idSatuan($r->satuan),
                'stok' => max(0, (int) $r->stok),
                'harga' => $harga,
                'id_ruangan' => $ruanganDefault,
                'gambar' => (string) ($r->gambar ?? ''),
                'keterangan' => $r->keterangan,
            ]);
        }

        // AKUN + PROFIL. Password disalin apa adanya (tanpa hash).
        $emails = [];
        $simpan = function (string $role, object $r, string $tabel, array $profil, string $cadangan) use (&$emails): void {
            $email = mb_strtolower(trim((string) $r->email));
            if ($email === '' || isset($emails[$email])) {
                return; // email ganda antar-tabel: akun pertama dipertahankan
            }
            $emails[$email] = true;
            DB::table('akuns')->insert(['email' => $email, 'password' => (string) ($r->password ?: $cadangan), 'role' => $role]);
            DB::table($tabel)->insert($profil + ['email' => $email]);
        };
        foreach (DB::table('laborans_lama')->get() as $r) {
            $simpan('laboran', $r, 'laborans', ['id_pegawai' => $r->id_pegawai, 'nama' => mb_substr($r->nama, 0, 100), 'no_whatsapp' => $r->no_whatsapp], $r->id_pegawai);
        }
        foreach (DB::table('staff_prodis_lama')->get() as $r) {
            $simpan('staff_prodi', $r, 'staff_prodis', ['id_pegawai' => $r->id_pegawai, 'nama' => mb_substr($r->nama, 0, 100), 'id_prodi' => $idProdi($r->program_studi), 'no_whatsapp' => $r->no_whatsapp], $r->id_pegawai);
        }
        foreach (DB::table('dosens_lama')->get() as $r) {
            $simpan('dosen', $r, 'dosens', ['nuptk_nidn' => $r->nuptk_nidn, 'nama' => mb_substr($r->nama, 0, 100), 'id_prodi' => $idProdi($r->program_studi), 'no_whatsapp' => $r->no_whatsapp], $r->nuptk_nidn);
        }
        foreach (DB::table('mahasiswas_lama')->get() as $r) {
            $simpan('mahasiswa', $r, 'mahasiswas', ['nim' => $r->nim, 'nama' => mb_substr($r->nama, 0, 100), 'id_prodi' => $idProdi($r->program_studi), 'no_whatsapp' => $r->no_whatsapp], $r->nim);
        }
    }

    /** CHECK constraint hanya untuk database yang mendukung; validasi aplikasi tetap berlaku di semua driver. */
    private function pasangCheck(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb', 'pgsql'], true)) {
            return;
        }
        $checks = [
            ['alat_bahans', 'ck_alat_bahans_harga', 'harga > 0'],
            ['peminjaman_details', 'ck_pdetail_jumlah', 'jumlah > 0'],
            ['peminjaman_ruangans', 'ck_pruangan_waktu', 'tanggal_mulai < tanggal_selesai'],
            ['peminjamans', 'ck_peminjamans_tanggal', 'tanggal_pengajuan <= tanggal_peminjaman AND tanggal_peminjaman <= tanggal_rencana_kembali'],
            ['pengembalian_details', 'ck_pgdetail_jumlah', 'jumlah_dikembalikan > 0'],
            ['jadwals', 'ck_jadwals_jam', 'jam_mulai < jam_selesai'],
        ];
        foreach ($checks as [$tabel, $nama, $expr]) {
            DB::statement("ALTER TABLE {$tabel} ADD CONSTRAINT {$nama} CHECK ({$expr})");
        }
    }
};
