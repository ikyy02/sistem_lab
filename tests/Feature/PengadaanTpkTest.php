<?php

namespace Tests\Feature;

use App\Models\AlatBahan;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Support\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class PengadaanTpkTest extends TestCase
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

    public function test_hanya_laboran_yang_dapat_mengakses_halaman_pengadaan_tpk(): void
    {
        // Tamu tanpa login diarahkan ke login
        $this->get(route('pengadaan-tpk.index'))->assertRedirect(route('login'));

        // Mahasiswa dilarang (403)
        $mhs = $this->mahasiswa();
        $this->sebagai(Role::MAHASISWA, $mhs)
            ->get(route('pengadaan-tpk.index'))
            ->assertForbidden();

        // Dosen dilarang (403)
        $dsn = $this->dosen();
        $this->sebagai(Role::DOSEN, $dsn)
            ->get(route('pengadaan-tpk.index'))
            ->assertForbidden();

        // Laboran dapat mengakses (200)
        $lab = $this->laboran();
        $this->sebagai(Role::LABORAN, $lab)
            ->get(route('pengadaan-tpk.index'))
            ->assertOk()
            ->assertSee('Pengadaan TPK')
            ->assertSee('Simple Additive Weighting');
    }

    public function test_halaman_pengadaan_tpk_menampilkan_hasil_analisis_saw(): void
    {
        $lab = $this->laboran();
        $mhs = $this->mahasiswa();

        $k1 = $this->katalog('alat', 5, ['nama' => 'Multimeter Digital', 'harga' => 250000]);
        $k2 = $this->katalog('bahan', 2, ['nama' => 'Timah Solder', 'harga' => 35000]);

        // Simulasikan peminjaman yang disetujui
        $p = Peminjaman::create([
            'email_peminjam' => $mhs->email,
            'jenis_peminjaman' => 'pribadi',
            'tanggal_pengajuan' => now()->format('Y-m-d H:i:s'),
            'tanggal_peminjaman' => now()->format('Y-m-d H:i:s'),
            'tanggal_rencana_kembali' => now()->addDay()->format('Y-m-d H:i:s'),
            'status' => 'disetujui',
            'email_pemroses' => $lab->email,
        ]);
        PeminjamanDetail::create([
            'id_peminjaman' => $p->id_peminjaman,
            'id_katalog' => $k2->id_katalog,
            'jumlah' => 1,
        ]);

        $res = $this->sebagai(Role::LABORAN, $lab)
            ->get(route('pengadaan-tpk.index', ['bulan' => now()->format('Y-m')]))
            ->assertOk()
            ->assertSee('Multimeter Digital')
            ->assertSee('Timah Solder')
            ->assertSee('Skor Preferensi (V)')
            ->assertSee('Matriks Keputusan');

        $this->assertCount(2, $res->viewData('items'));
    }

    public function test_pengadaan_tpk_dengan_parameter_custom_bobot_dan_filter_kategori(): void
    {
        $lab = $this->laboran();
        $this->katalog('alat', 10, ['nama' => 'Oscilloscope', 'harga' => 3000000]);
        $this->katalog('bahan', 50, ['nama' => 'Kabel UTP', 'harga' => 8000]);

        // Filter khusus bahan dengan preset
        $res = $this->sebagai(Role::LABORAN, $lab)
            ->get(route('pengadaan-tpk.index', [
                'jenis' => 'bahan',
                'w1' => 0.60,
                'w2' => 0.25,
                'w3' => 0.15,
            ]))
            ->assertOk();

        $items = $res->viewData('items');
        $this->assertCount(1, $items);
        $this->assertSame('Kabel UTP', $items[0]['nama']);
    }

    public function test_cetak_laporan_pengadaan_tpk(): void
    {
        $lab = $this->laboran();
        $this->katalog('alat', 3, ['nama' => 'Router Mikrotik', 'harga' => 750000]);

        $this->sebagai(Role::LABORAN, $lab)
            ->get(route('pengadaan-tpk.cetak'))
            ->assertOk()
            ->assertSee('POLITEKNIK NEGERI TANAH LAUT')
            ->assertSee('REKOMENDASI PRIORITAS PENGADAAN ALAT DAN BAHAN LABORATORIUM')
            ->assertSee('Router Mikrotik');
    }

    public function test_export_csv_pengadaan_tpk(): void
    {
        $lab = $this->laboran();
        $this->katalog('bahan', 20, ['nama' => 'Konektor RJ45', 'harga' => 2500]);

        $res = $this->sebagai(Role::LABORAN, $lab)
            ->get(route('pengadaan-tpk.export-csv'))
            ->assertOk();

        $this->assertStringContainsString('text/csv', $res->headers->get('content-type'));
        $this->assertStringContainsString('Rekomendasi_Pengadaan_TPK', $res->headers->get('content-disposition'));
    }
}
