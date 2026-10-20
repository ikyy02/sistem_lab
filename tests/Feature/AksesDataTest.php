<?php

namespace Tests\Feature;

use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/**
 * Pembatasan data & aksi per role pada transaksi peminjaman:
 * Peminjaman Mandiri hanya untuk Laboran (dan peminjamnya); Dosen hanya memegang pengajuan yang menunjuk dirinya.
 */
class AksesDataTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private function sebagai(string $role, $profil): self
    {
        return $this->withSession(['silab_user' => [
            'role' => $role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $profil->email,
        ]]);
    }

    private function hariKerja(int $jam): string
    {
        $t = now()->addDay()->startOfDay();
        while ($t->isWeekend()) {
            $t->addDay();
        }

        return $t->setTime($jam, 0)->format('Y-m-d H:i:s');
    }

    private function ajukan(string $email, array $o = []): Peminjaman
    {
        $k = $this->katalog('alat', 10);

        return app(PeminjamanService::class)->ajukan($email, $o + [
            'jenis_peminjaman' => 'pribadi',
            'tanggal_peminjaman' => $this->hariKerja(9),
            'tanggal_rencana_kembali' => $this->hariKerja(11),
            'keterangan' => 'Praktikum',
            'detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 1]],
            'ruangan' => [],
        ]);
    }

    /** Tiga jenis transaksi + dosen berwenang dan dosen lain. */
    private function skenario(): array
    {
        $dosenA = $this->dosen(['nama' => 'Dosen Penanggung Aaa']);
        $dosenB = $this->dosen(['nama' => 'Dosen Lain Bbb']);
        $mhsMandiri = $this->mahasiswa(['nama' => 'Zulkarnain Mandiri']);
        $mhsAtas = $this->mahasiswa(['nama' => 'Yusuf AtasDosen']);

        return [
            'dosenA' => $dosenA, 'dosenB' => $dosenB, 'mhsMandiri' => $mhsMandiri,
            'mandiri' => $this->ajukan($mhsMandiri->email),
            'atasA' => $this->ajukan($mhsAtas->email, ['jenis_peminjaman' => 'atas_dosen', 'email_dosen' => $dosenA->email]),
            'atasB' => $this->ajukan($mhsAtas->email, ['jenis_peminjaman' => 'atas_dosen', 'email_dosen' => $dosenB->email]),
            'belajarB' => $this->ajukan($dosenB->email),
        ];
    }

    // ------------------------------------------------------------------ daftar pengajuan

    public function test_dosen_hanya_menerima_pengajuan_yang_menunjuk_dirinya(): void
    {
        $s = $this->skenario();

        $res = $this->sebagai('dosen', $s['dosenA'])->get(route('pengajuan.index', ['status' => 'semua']))->assertOk();
        $res->assertSee('Yusuf AtasDosen')
            ->assertDontSee('Zulkarnain Mandiri')
            ->assertDontSee('Dosen Lain Bbb')
            ->assertDontSee(Peminjaman::LABEL_JENIS['pribadi'])
            ->assertViewHas('data', fn ($d) => $d->total() === 1 && $d->first()->id_peminjaman === $s['atasA']->id_peminjaman)
            ->assertViewHas('jumlahMenunggu', 1);
    }

    public function test_pencarian_filter_dan_halaman_dosen_tidak_membocorkan_mandiri(): void
    {
        $s = $this->skenario();
        $dosen = $this->sebagai('dosen', $s['dosenA']);

        // cari langsung nama/email peminjam mandiri
        $dosen->get(route('pengajuan.index', ['status' => 'semua', 'search' => 'Zulkarnain']))
            ->assertOk()->assertViewHas('data', fn ($d) => $d->total() === 0)->assertDontSee('Zulkarnain Mandiri');
        $dosen->get(route('pengajuan.index', ['status' => 'semua', 'search' => $s['mandiri']->email_peminjam]))
            ->assertOk()->assertViewHas('data', fn ($d) => $d->total() === 0);
        // filter status + ukuran halaman + nomor halaman
        $dosen->get(route('pengajuan.index', ['status' => 'menunggu', 'per_page' => 100, 'page' => 1]))
            ->assertOk()->assertViewHas('data', fn ($d) => $d->total() === 1);
        // dosen yang tidak ditunjuk siapa pun: kosong untuk semua filter/halaman
        $this->sebagai('dosen', $this->dosen())->get(route('pengajuan.index', ['status' => 'semua', 'per_page' => 100, 'page' => 1]))
            ->assertOk()->assertViewHas('data', fn ($d) => $d->total() === 0)->assertDontSee('Zulkarnain Mandiri');
    }

    public function test_laboran_melihat_semua_jenis(): void
    {
        $s = $this->skenario();

        $this->sebagai('laboran', $this->laboran())->get(route('pengajuan.index', ['status' => 'semua']))->assertOk()
            ->assertSee('Zulkarnain Mandiri')->assertSee('Yusuf AtasDosen')->assertSee('Dosen Lain Bbb')
            ->assertViewHas('data', fn ($d) => $d->total() === 4)
            ->assertViewHas('jumlahMenunggu', 4); // Laboran (Super Admin) dapat memutuskan semua yang menunggu, termasuk tahap Dosen
    }

    // ------------------------------------------------------------------ detail (ganti ID di URL)

    public function test_detail_dibatasi_per_kewenangan(): void
    {
        $s = $this->skenario();
        $dosenA = $this->sebagai('dosen', $s['dosenA']);

        $dosenA->get(route('peminjaman.show', $s['mandiri']->id_peminjaman))->assertForbidden();
        $dosenA->get(route('peminjaman.show', $s['atasB']->id_peminjaman))->assertForbidden();
        $dosenA->get(route('peminjaman.show', $s['belajarB']->id_peminjaman))->assertForbidden();
        $dosenA->get(route('peminjaman.show', $s['atasA']->id_peminjaman))->assertOk()->assertSee('Yusuf AtasDosen');

        // pemilik: boleh miliknya, tidak boleh milik orang lain; staf prodi tidak boleh apa pun
        $pemilik = $s['mhsMandiri'];
        $this->sebagai('mahasiswa', $pemilik)->get(route('peminjaman.show', $s['mandiri']->id_peminjaman))->assertOk();
        $this->sebagai('mahasiswa', $pemilik)->get(route('peminjaman.show', $s['atasA']->id_peminjaman))->assertForbidden();
        $this->sebagai('staff_prodi', $this->staff())->get(route('peminjaman.show', $s['mandiri']->id_peminjaman))->assertForbidden();

        $lab = $this->sebagai('laboran', $this->laboran());
        foreach (['mandiri', 'atasA', 'atasB', 'belajarB'] as $k) {
            $lab->get(route('peminjaman.show', $s[$k]->id_peminjaman))->assertOk();
        }
    }

    public function test_tombol_proses_di_detail_hanya_untuk_yang_berwenang(): void
    {
        $s = $this->skenario();

        $this->sebagai('dosen', $s['dosenA'])->get(route('peminjaman.show', $s['atasA']->id_peminjaman))
            ->assertViewHas('dapatMemproses', true);
        $this->sebagai('laboran', $this->laboran())->get(route('peminjaman.show', $s['mandiri']->id_peminjaman))
            ->assertViewHas('dapatMemproses', true);
        // Laboran (Super Admin) dapat memproses Kuliah/Praktikum meski Dosen belum memutuskan
        $this->get(route('peminjaman.show', $s['atasA']->id_peminjaman))->assertOk()->assertViewHas('dapatMemproses', true);
    }

    // ------------------------------------------------------------------ alur dua tahap (SRS D.8)

    public function test_kuliah_praktikum_diputuskan_dosen_lalu_laboran(): void
    {
        $s = $this->skenario();
        $lab = $this->laboran();
        $id = $s['atasA']->id_peminjaman;
        $stok = fn () => \App\Models\AlatBahan::find($s['atasA']->details()->first()->id_katalog)->stok;

        $this->assertSame('menunggu', $s['atasA']->fresh()->status);
        $this->assertNull($s['atasA']->fresh()->tanggal_persetujuan_dosen);

        // Dosen menyetujui: diteruskan, stok belum berubah, tidak bisa diulang
        $this->sebagai('dosen', $s['dosenA'])->post(route('pengajuan.setujui', $id))->assertSessionHas('success');
        $this->assertSame('menunggu', $s['atasA']->fresh()->status);
        $this->assertSame(10, $stok());
        $this->post(route('pengajuan.setujui', $id))->assertSessionHasErrors('status');
        $this->post(route('pengajuan.tolak', $id), ['alasan' => 'x'])->assertSessionHasErrors('status');

        // Kini masuk antrean Laboran; keputusan akhir ada di tangan Laboran
        $this->sebagai('laboran', $lab)->get(route('pengajuan.index'))->assertOk()
            ->assertViewHas('data', fn ($d) => $d->pluck('id_peminjaman')->contains($id));
        $this->post(route('pengajuan.setujui', $id))->assertSessionHas('success');
        $a = $s['atasA']->fresh();
        $this->assertSame(['disetujui', $lab->email], [$a->status, $a->email_pemroses]);
        $this->assertSame(9, $stok());
    }

    public function test_dosen_menolak_kuliah_praktikum_dengan_alasan(): void
    {
        $s = $this->skenario();
        $id = $s['atasB']->id_peminjaman;

        $this->sebagai('dosen', $s['dosenB'])->post(route('pengajuan.tolak', $id), [])->assertSessionHasErrors('alasan');
        $this->post(route('pengajuan.tolak', $id), ['alasan' => 'Bukan jadwal praktikum'])->assertSessionHas('success');
        $b = $s['atasB']->fresh();
        $this->assertSame(['ditolak', 'Bukan jadwal praktikum', $s['dosenB']->email], [$b->status, $b->alasan_ditolak, $b->email_pemroses]);
        $this->sebagai('laboran', $this->laboran())->post(route('pengajuan.setujui', $id))->assertSessionHasErrors('status'); // sudah ditolak
    }

    public function test_laboran_super_admin_dapat_memutuskan_sebelum_dosen(): void
    {
        $s = $this->skenario();
        $this->sebagai('laboran', $lab = $this->laboran())->post(route('pengajuan.setujui', $s['atasA']->id_peminjaman))->assertSessionHas('success');
        $this->assertSame(['disetujui', $lab->email], [$s['atasA']->fresh()->status, $s['atasA']->fresh()->email_pemroses]);
    }

    // ------------------------------------------------------------------ aksi backend

    public function test_dosen_tidak_dapat_menyetujui_atau_menolak_di_luar_kewenangan(): void
    {
        $s = $this->skenario();
        $dosenA = $this->sebagai('dosen', $s['dosenA']);

        foreach (['mandiri', 'atasB', 'belajarB'] as $k) {
            $id = $s[$k]->id_peminjaman;
            $dosenA->post(route('pengajuan.setujui', $id))->assertForbidden();
            $dosenA->post(route('pengajuan.tolak', $id), ['alasan' => 'x'])->assertForbidden();
            $this->assertSame('menunggu', $s[$k]->fresh()->status);
        }
        $this->assertSame(10, \App\Models\AlatBahan::first()->stok); // stok tidak berubah

        // yang menunjuk dirinya tetap bisa diproses (tahap 1: hanya diteruskan, bukan keputusan akhir)
        $dosenA->post(route('pengajuan.setujui', $s['atasA']->id_peminjaman))->assertSessionHas('success');
        $a = $s['atasA']->fresh();
        $this->assertSame('menunggu', $a->status);
        $this->assertNull($a->email_pemroses);
        $this->assertNotNull($a->tanggal_persetujuan_dosen);
        $dosenA->post(route('pengajuan.tolak', $s['atasB']->id_peminjaman), ['alasan' => 'x'])->assertForbidden();
    }

    public function test_service_menolak_dosen_di_luar_kewenangan_tanpa_lewat_controller(): void
    {
        $s = $this->skenario();
        $svc = app(PeminjamanService::class);

        foreach (['mandiri', 'atasB', 'belajarB'] as $k) {
            try {
                $svc->setujui($s[$k]->id_peminjaman, $s['dosenA']->email);
                $this->fail('Dosen tidak berwenang seharusnya ditolak untuk '.$k);
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertArrayHasKey('email_pemroses', $e->errors());
            }
            $this->assertSame('menunggu', $s[$k]->fresh()->status);
        }
    }

    public function test_laboran_tetap_dapat_memproses_mandiri(): void
    {
        $s = $this->skenario();
        $lab = $this->laboran();

        $this->sebagai('laboran', $lab)->post(route('pengajuan.setujui', $s['mandiri']->id_peminjaman))->assertSessionHas('success');
        $this->assertSame('disetujui', $s['mandiri']->fresh()->status);
        $this->post(route('pengajuan.tolak', $s['belajarB']->id_peminjaman), ['alasan' => 'Jadwal penuh'])->assertSessionHas('success');
        $this->assertSame('ditolak', $s['belajarB']->fresh()->status);
    }

    public function test_role_lain_tidak_dapat_memproses_atau_mengembalikan(): void
    {
        $s = $this->skenario();
        $id = $s['mandiri']->id_peminjaman;

        foreach ([['mahasiswa', $this->mahasiswa()], ['staff_prodi', $this->staff()]] as [$role, $profil]) {
            $this->sebagai($role, $profil);
            $this->post(route('pengajuan.setujui', $id))->assertForbidden();
            $this->post(route('pengajuan.tolak', $id), ['alasan' => 'x'])->assertForbidden();
            $this->post(route('pengembalian.store', $id), ['jumlah' => [1 => 1]])->assertForbidden();
            $this->get(route('pengajuan.index'))->assertForbidden();
            $this->get(route('pengembalian.index'))->assertForbidden();
        }
        $this->sebagai('dosen', $s['dosenA'])->post(route('pengembalian.store', $id), ['jumlah' => [1 => 1]])->assertForbidden();
        $this->assertSame('menunggu', $s['mandiri']->fresh()->status);
    }

    public function test_batal_dan_selesai_milik_orang_lain_ditolak(): void
    {
        $s = $this->skenario();
        $penyusup = $this->mahasiswa();

        $this->sebagai('mahasiswa', $penyusup)->post(route('peminjaman.batal', $s['mandiri']->id_peminjaman))->assertSessionHasErrors();
        $this->post(route('peminjaman.selesai', $s['mandiri']->id_peminjaman))->assertSessionHasErrors();
        $this->sebagai('dosen', $s['dosenA'])->post(route('peminjaman.selesai', $s['mandiri']->id_peminjaman))->assertSessionHasErrors();
        $this->assertSame('menunggu', $s['mandiri']->fresh()->status);
    }

    // ------------------------------------------------------------------ dashboard / statistik

    public function test_dashboard_dosen_menghitung_hanya_pengajuan_miliknya(): void
    {
        $s = $this->skenario();

        $this->sebagai('dosen', $s['dosenA'])->get(route('dashboard'))->assertOk()->assertViewHas('menunggu', 1);
        $this->sebagai('dosen', $this->dosen())->get(route('dashboard'))->assertOk()->assertViewHas('menunggu', 0);
        $this->sebagai('laboran', $this->laboran())->get(route('dashboard'))->assertOk()->assertViewHas('menunggu', 4);
    }

    public function test_riwayat_dan_dashboard_mahasiswa_hanya_milik_sendiri(): void
    {
        $s = $this->skenario();
        $pemilik = $s['mhsMandiri'];

        $this->sebagai('mahasiswa', $pemilik)->get(route('peminjaman.riwayat', ['status' => 'menunggu', 'per_page' => 100]))->assertOk()
            ->assertViewHas('data', fn ($d) => $d->total() === 1 && $d->first()->id_peminjaman === $s['mandiri']->id_peminjaman);
        $this->sebagai('mahasiswa', $pemilik)->get(route('dashboard'))->assertOk()
            ->assertViewHas('ringkasan', fn ($r) => $r['menunggu'] === 1);
    }
}
