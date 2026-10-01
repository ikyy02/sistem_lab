<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Primary key data user diganti dari `id` auto-increment ke identitas nyata:
 * mahasiswas.nim | dosens.nuptk_nidn | staff_prodis.id_pegawai | laborans.id_pegawai.
 * Data lama dipertahankan (tabel dibangun ulang lalu disalin). Email tetap UNIQUE, bukan primary key.
 * Dosen: kolom nidn + nip digabung menjadi nuptk_nidn (nidn diutamakan, nip sebagai cadangan).
 * Staff/Laboran lama belum punya ID Pegawai -> diisi otomatis STF0001 / LAB0001 (dapat diedit di Kelola Data User).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->rebuild('mahasiswas', function (Blueprint $t) {
            $t->string('nim', 20)->primary();
            $t->string('nama');
            $t->string('kelas', 50)->nullable();
            $t->string('program_studi');
            $t->string('email');
            $t->string('no_whatsapp');
            $t->string('password')->nullable();
            $t->timestamps();
        }, fn ($r) => [
            'nim' => $r->nim, 'nama' => $r->nama, 'kelas' => $r->kelas, 'program_studi' => $r->program_studi,
            'email' => $r->email, 'no_whatsapp' => $r->no_whatsapp, 'password' => $r->password,
            'created_at' => $r->created_at, 'updated_at' => $r->updated_at,
        ]);

        $this->rebuild('dosens', function (Blueprint $t) {
            $t->string('nuptk_nidn', 30)->primary();
            $t->string('nama');
            $t->string('program_studi');
            $t->string('email');
            $t->string('no_whatsapp');
            $t->string('password')->nullable();
            $t->timestamps();
        }, fn ($r) => [
            'nuptk_nidn' => $r->nidn ?: $r->nip, 'nama' => $r->nama, 'program_studi' => $r->program_studi,
            'email' => $r->email, 'no_whatsapp' => $r->no_whatsapp, 'password' => $r->password,
            'created_at' => $r->created_at, 'updated_at' => $r->updated_at,
        ]);

        $this->rebuild('staff_prodis', function (Blueprint $t) {
            $t->string('id_pegawai', 30)->primary();
            $t->string('nama');
            $t->string('program_studi');
            $t->string('email');
            $t->string('no_whatsapp', 20);
            $t->string('password');
            $t->timestamps();
        }, fn ($r) => [
            'id_pegawai' => 'STF' . str_pad((string) $r->id, 4, '0', STR_PAD_LEFT), 'nama' => $r->nama,
            'program_studi' => $r->program_studi, 'email' => $r->email, 'no_whatsapp' => $r->no_whatsapp,
            'password' => $r->password, 'created_at' => $r->created_at, 'updated_at' => $r->updated_at,
        ]);

        $this->rebuild('laborans', function (Blueprint $t) {
            $t->string('id_pegawai', 30)->primary();
            $t->string('nama');
            $t->string('email');
            $t->string('no_whatsapp', 20);
            $t->string('password');
            $t->timestamps();
        }, fn ($r) => [
            'id_pegawai' => 'LAB' . str_pad((string) $r->id, 4, '0', STR_PAD_LEFT), 'nama' => $r->nama,
            'email' => $r->email, 'no_whatsapp' => $r->no_whatsapp, 'password' => $r->password,
            'created_at' => $r->created_at, 'updated_at' => $r->updated_at,
        ]);
    }

    public function down(): void
    {
        $this->rebuild('mahasiswas', function (Blueprint $t) {
            $t->id();
            $t->string('nim');
            $t->string('nama');
            $t->string('kelas', 50)->nullable();
            $t->string('program_studi');
            $t->string('email');
            $t->string('no_whatsapp');
            $t->string('password')->nullable();
            $t->timestamps();
        }, fn ($r) => (array) $r, ['nim', 'email']);

        $this->rebuild('dosens', function (Blueprint $t) {
            $t->id();
            $t->string('nidn');
            $t->string('nip')->nullable();
            $t->string('nama');
            $t->string('program_studi');
            $t->string('email');
            $t->string('no_whatsapp');
            $t->string('password')->nullable();
            $t->timestamps();
        }, fn ($r) => [
            'nidn' => $r->nuptk_nidn, 'nama' => $r->nama, 'program_studi' => $r->program_studi, 'email' => $r->email,
            'no_whatsapp' => $r->no_whatsapp, 'password' => $r->password, 'created_at' => $r->created_at, 'updated_at' => $r->updated_at,
        ], ['nidn', 'nip', 'email']);

        foreach (['staff_prodis', 'laborans'] as $table) {
            $this->rebuild($table, function (Blueprint $t) use ($table) {
                $t->id();
                $t->string('nama');
                if ($table === 'staff_prodis') {
                    $t->string('program_studi');
                }
                $t->string('email');
                $t->string('no_whatsapp', 20);
                $t->string('password');
                $t->timestamps();
            }, function ($r) {
                $row = (array) $r;
                unset($row['id_pegawai']);

                return $row;
            }, ['email']);
        }
    }

    /** Bangun ulang tabel: buat tabel baru, salin data, ganti tabel lama, lalu pasang index UNIQUE. */
    private function rebuild(string $table, \Closure $define, \Closure $map, array $unique = ['email']): void
    {
        $tmp = $table . '_new';
        Schema::dropIfExists($tmp);
        Schema::create($tmp, $define);

        foreach (DB::table($table)->get()->chunk(200) as $chunk) {
            DB::table($tmp)->insert($chunk->map($map)->all());
        }

        Schema::drop($table);
        Schema::rename($tmp, $table);

        Schema::table($table, function (Blueprint $t) use ($unique) {
            foreach ($unique as $col) {
                $t->unique($col);
            }
        });
    }
};
