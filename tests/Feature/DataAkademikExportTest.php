<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\MataKuliah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class DataAkademikExportTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private function sebagai(string $role, $profil): self
    {
        return $this->withSession(['silab_user' => [
            'role' => $role,
            'key' => (string) $profil->getKey(),
            'nama' => $profil->nama,
            'email' => $profil->email,
        ]]);
    }

    public function test_laboran_dapat_export_mata_kuliah_dan_kelas(): void
    {
        $lab = $this->laboran();
        $prodi = $this->prodi('Teknik Informatika');
        MataKuliah::create([
            'kode_mk' => 'IF101',
            'nama_mk' => 'Dasar Pemrograman',
            'sks' => 3,
            'semester' => 'ganjil',
            'id_prodi' => $prodi->id_prodi,
        ]);
        Kelas::create([
            'nama_kelas' => 'TI-1A',
            'id_prodi' => $prodi->id_prodi,
        ]);

        $resMk = $this->sebagai('laboran', $lab)->get(route('data-akademik.export', ['tab' => 'mata-kuliah']));
        $resMk->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $resMk->headers->get('content-type'));

        $resKelas = $this->sebagai('laboran', $lab)->get(route('data-akademik.export', ['tab' => 'kelas']));
        $resKelas->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $resKelas->headers->get('content-type'));

        $resSem = $this->sebagai('laboran', $lab)->get(route('data-akademik.export', ['tab' => 'semester']));
        $resSem->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $resSem->headers->get('content-type'));

        // Laboran tidak mengelola tab dosen di Data Akademik -> 404
        $this->sebagai('laboran', $lab)->get(route('data-akademik.export', ['tab' => 'dosen']))->assertNotFound();
    }

    public function test_staf_prodi_dapat_export_dosen_prodinya(): void
    {
        $staff = $this->staff();
        $dosen = $this->dosen(['id_prodi' => $staff->id_prodi]);

        $res = $this->sebagai('staff_prodi', $staff)->get(route('data-akademik.export', ['tab' => 'dosen']));
        $res->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', (string) $res->headers->get('content-type'));
    }

    public function test_mahasiswa_tidak_dapat_akses_export_data_akademik(): void
    {
        $mhs = $this->mahasiswa();
        $this->sebagai('mahasiswa', $mhs)->get(route('data-akademik.export', ['tab' => 'mata-kuliah']))->assertForbidden();
    }
}
