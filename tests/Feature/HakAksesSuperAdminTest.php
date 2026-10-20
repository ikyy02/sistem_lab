<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/** Laboran = Super Admin; role lain tetap terbatas (dicek di backend, bukan hanya menu). */
class HakAksesSuperAdminTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private function sebagai(string $role)
    {
        $p = match ($role) {
            'laboran' => $this->laboran(), 'staff_prodi' => $this->staff(),
            'dosen' => $this->dosen(), default => $this->mahasiswa(),
        };

        return $this->withSession(['silab_user' => ['role' => $role, 'key' => (string) $p->getKey(), 'nama' => $p->nama, 'email' => $p->email]]);
    }

    /** route => role yang boleh (selain itu harus 403) */
    private function matriks(): array
    {
        $semua = ['laboran', 'staff_prodi', 'dosen', 'mahasiswa'];

        return [
            'dashboard' => $semua, 'katalog' => $semua,
            'data-akademik.index' => ['laboran', 'staff_prodi'],
            'jadwal.index' => ['laboran', 'staff_prodi'],
            'jadwal.create' => ['laboran', 'staff_prodi'],
            'jadwal.periode.index' => ['laboran', 'staff_prodi'],
            'pengajuan.index' => ['laboran', 'dosen'],
            'peminjaman.index' => ['dosen', 'mahasiswa'],
            'peminjaman.riwayat' => ['dosen', 'mahasiswa'],
            'pengembalian.index' => ['laboran'],
            'kelola-katalog.index' => ['laboran'],
            'kelola-user.index' => ['laboran'],
            'pengaturan-lab.index' => ['laboran'],
            'mahasiswa.template' => ['laboran'],
        ];
    }

    public function test_matriks_akses_halaman_semua_role(): void
    {
        foreach ($this->matriks() as $route => $boleh) {
            foreach (['laboran', 'staff_prodi', 'dosen', 'mahasiswa'] as $role) {
                $res = $this->sebagai($role)->get(route($route));
                in_array($role, $boleh, true)
                    ? $this->assertNotSame(403, $res->baseResponse->getStatusCode(), "$role harus boleh $route")
                    : $res->assertForbidden();
            }
        }
    }

    public function test_aksi_tulis_admin_hanya_laboran(): void
    {
        $post = [
            ['post', 'kelola-user.store', ['kategori' => 'laboran']],
            ['post', 'pengaturan-lab.satuan.store', []],
            ['put', 'pengaturan-lab.operasional.update', []],
            ['post', 'kelola-katalog.store', ['kategori' => 'alat']],
            ['post', 'mahasiswa.import', []],
        ];
        foreach (['staff_prodi', 'dosen', 'mahasiswa'] as $role) {
            foreach ($post as [$m, $r, $prm]) {
                $this->sebagai($role)->{$m}(route($r, $prm))->assertForbidden();
            }
        }
        foreach ($post as [$m, $r, $prm]) {
            $this->assertNotSame(403, $this->sebagai('laboran')->{$m}(route($r, $prm))->status(), "laboran $r");
        }
    }

    public function test_laboran_mengelola_data_akademik_dan_jadwal_tulis(): void
    {
        $this->assertNotSame(403, $this->sebagai('laboran')->post(route('data-akademik.store', 'mata-kuliah'), [])->status());
        $this->assertNotSame(403, $this->sebagai('laboran')->post(route('jadwal.store'), [])->status());
        $this->assertNotSame(403, $this->sebagai('laboran')->post(route('jadwal.periode.store'), [])->status());
        $this->sebagai('mahasiswa')->post(route('jadwal.store'), [])->assertForbidden();
        $this->sebagai('dosen')->post(route('data-akademik.store', 'mata-kuliah'), [])->assertForbidden();
    }

    public function test_tidak_ada_eskalasi_role_lewat_input(): void
    {
        $lab = $this->laboran();
        $this->sebagai('laboran')->post(route('kelola-user.store', 'dosen'), [
            'nuptk_nidn' => '1234567890', 'nama' => 'Uji Role', 'id_prodi' => $this->prodi()->id_prodi,
            'email' => 'uji.role@uji.test', 'password' => 'rahasia', 'role' => 'laboran',
        ]);
        $this->assertDatabaseHas('akuns', ['email' => 'uji.role@uji.test', 'role' => 'dosen']);
        $this->sebagai('dosen')->post(route('kelola-user.store', 'laboran'), ['role' => 'laboran'])->assertForbidden();
        $this->sebagai('mahasiswa')->put(route('kelola-user.update', ['laboran', $lab->id_pegawai]), [])->assertForbidden();
    }
}
