<?php

namespace Tests\Feature;

use App\Models\AlatBahan;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Services\PeminjamanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

/** Alur HTTP modul Peminjaman: pengajuan, persetujuan, pengembalian, serta pembatasan role & detail. */
class PeminjamanHttpTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private function sebagai(string $role, $profil): self
    {
        return $this->withSession(['silab_user' => [
            'role' => $role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $profil->email,
        ]]);
    }

    /** Input form pengajuan (dipakai controller maupun service). */
    private function dataPinjam(AlatBahan $k, int $jumlah, array $o = []): array
    {
        return $o + [
            'jenis_peminjaman' => 'pribadi',
            'tanggal_peminjaman' => now()->addDay()->format('Y-m-d H:i:s'),
            'tanggal_rencana_kembali' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'keterangan' => 'Praktikum jaringan',
            'detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => $jumlah]],
        ];
    }

    private function ajukan(array $items, string $email): Peminjaman
    {
        return app(PeminjamanService::class)->ajukan($email, [
            'jenis_peminjaman' => 'pribadi',
            'tanggal_peminjaman' => now()->addDay()->format('Y-m-d H:i:s'),
            'tanggal_rencana_kembali' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'keterangan' => 'Praktikum jaringan',
            'detail' => $items,
            'ruangan' => [],
        ]);
    }

    // ------------------------------------------------------------------ pengajuan & riwayat

    public function test_pengajuan_lewat_http_menunggu_dan_stok_belum_berubah(): void
    {
        $m = $this->mahasiswa();
        $k = $this->katalog('alat', 5);
        $this->sebagai('mahasiswa', $m)->get(route('peminjaman.index'))->assertOk();

        $this->post(route('peminjaman.store'), $this->dataPinjam($k, 3))
            ->assertRedirect(route('peminjaman.riwayat'))
            ->assertSessionHas('success');

        $p = Peminjaman::orderByDesc('id_peminjaman')->first();
        $this->assertSame(['menunggu', $m->email], [$p->status, $p->email_peminjam]);
        $this->assertSame(5, $k->fresh()->stok); // stok baru dipotong saat disetujui
        $this->get(route('peminjaman.riwayat'))->assertOk();

        // melebihi stok ditolak controller, transaksi tidak terbentuk
        $this->post(route('peminjaman.store'), $this->dataPinjam($k, 6))->assertSessionHasErrors('detail');
        $this->post(route('peminjaman.store'), $this->dataPinjam($k, 3, ['keterangan' => '']))->assertSessionHasErrors('keterangan');
        $this->assertSame(1, Peminjaman::count());
    }

    public function test_riwayat_hanya_milik_sendiri_dan_detail_dibatasi(): void
    {
        $k = $this->katalog('alat', 5);
        $pemilik = $this->mahasiswa();
        $p = $this->ajukan([['id_katalog' => $k->id_katalog, 'jumlah' => 1]], $pemilik->email);

        $this->sebagai('mahasiswa', $pemilik);
        $this->get(route('peminjaman.riwayat'))->assertOk()->assertSee(route('peminjaman.show', $p->id_peminjaman), false);
        $this->get(route('peminjaman.show', $p->id_peminjaman))->assertOk();

        // mahasiswa lain: riwayat kosong, detail ditolak
        $this->sebagai('mahasiswa', $this->mahasiswa());
        $this->get(route('peminjaman.riwayat'))->assertOk()->assertDontSee(route('peminjaman.show', $p->id_peminjaman), false);
        $this->get(route('peminjaman.show', $p->id_peminjaman))->assertForbidden();

        // dosen & laboran boleh membuka detail
        $this->sebagai('dosen', $this->dosen())->get(route('peminjaman.show', $p->id_peminjaman))->assertOk();
        $this->sebagai('laboran', $this->laboran())->get(route('peminjaman.show', $p->id_peminjaman))->assertOk();
    }

    // ------------------------------------------------------------------ pembatasan role

    public function test_akses_halaman_dibatasi_role(): void
    {
        $this->sebagai('laboran', $this->laboran());
        $this->get(route('peminjaman.index'))->assertForbidden();
        $this->get(route('peminjaman.riwayat'))->assertForbidden();

        $this->sebagai('mahasiswa', $this->mahasiswa());
        $this->get(route('pengajuan.index'))->assertForbidden();
        $this->get(route('pengembalian.index'))->assertForbidden();

        $this->sebagai('staff_prodi', $this->staff());
        $this->get(route('peminjaman.index'))->assertForbidden();
        $this->get(route('pengajuan.index'))->assertForbidden();
        $this->get(route('pengembalian.index'))->assertForbidden();

        // dosen dapat mengajukan sekaligus memproses
        $dosen = $this->dosen();
        $this->sebagai('dosen', $dosen);
        $this->get(route('peminjaman.index'))->assertOk();
        $this->get(route('pengajuan.index'))->assertOk();
        $this->get(route('pengembalian.index'))->assertOk();
    }

    // ------------------------------------------------------------------ persetujuan

    public function test_setujui_dan_tolak_lewat_http(): void
    {
        $k = $this->katalog('alat', 10);
        $p = $this->ajukan([['id_katalog' => $k->id_katalog, 'jumlah' => 4]], $this->mahasiswa()->email);
        $dosen = $this->dosen();

        $this->sebagai('dosen', $dosen)->get(route('pengajuan.index'))->assertOk();
        $this->post(route('pengajuan.setujui', $p->id_peminjaman))->assertSessionHas('success');
        $this->assertSame(['disetujui', $dosen->email], [$p->fresh()->status, $p->fresh()->email_pemroses]);
        $this->assertNotNull($p->fresh()->tanggal_persetujuan);
        $this->assertSame(6, $k->fresh()->stok);

        // tolak wajib beralasan
        $q = $this->ajukan([['id_katalog' => $k->id_katalog, 'jumlah' => 6]], $this->mahasiswa()->email);
        $this->post(route('pengajuan.tolak', $q->id_peminjaman), [])->assertSessionHasErrors('alasan');
        $this->post(route('pengajuan.tolak', $q->id_peminjaman), ['alasan' => 'Dipakai praktikum lain'])
            ->assertSessionHas('success');
        $qf = $q->fresh();
        $this->assertSame(['ditolak', 'Dipakai praktikum lain'], [$qf->status, $qf->alasan_ditolak]);
        $this->assertSame(6, $k->fresh()->stok); // penolakan tidak mengubah stok

        // pengaju tidak dapat memproses pengajuannya sendiri
        $dosenSendiri = $this->dosen();
        $own = $this->ajukan([['id_katalog' => $k->id_katalog, 'jumlah' => 1]], $dosenSendiri->email);
        $this->sebagai('dosen', $dosenSendiri);
        $this->post(route('pengajuan.setujui', $own->id_peminjaman))->assertSessionHasErrors('email_pemroses');
        $this->assertSame('menunggu', $own->fresh()->status);
    }

    // ------------------------------------------------------------------ dashboard

    public function test_dashboard_menampilkan_ringkasan_peminjaman(): void
    {
        $m = $this->mahasiswa();
        $k = $this->katalog('alat', 5);
        $p = $this->ajukan([['id_katalog' => $k->id_katalog, 'jumlah' => 2]], $m->email);

        $this->sebagai('mahasiswa', $m);
        $this->get(route('dashboard'))->assertOk()
            ->assertSee('Total riwayat Peminjaman')
            ->assertSee('Ajukan Peminjaman');

        $this->sebagai('laboran', $this->laboran());
        $this->get(route('dashboard'))->assertOk()
            ->assertSee('Pengajuan menunggu persetujuan')
            ->assertSee('Peminjaman sedang berlangsung');

        app(PeminjamanService::class)->setujui($p->id_peminjaman, $this->laboran()->email);
        $this->sebagai('dosen', $this->dosen());
        $this->get(route('dashboard'))->assertOk()->assertSee('Pengajuan terbaru');
    }

    // ------------------------------------------------------------------ pengembalian

    public function test_pengembalian_lewat_http_memperbarui_stok_dan_status(): void
    {
        $alat = $this->katalog('alat', 10);
        $lab = $this->laboran();
        $p = $this->ajukan([['id_katalog' => $alat->id_katalog, 'jumlah' => 3]], $this->mahasiswa()->email);
        app(PeminjamanService::class)->setujui($p->id_peminjaman, $lab->email);
        $idDetail = PeminjamanDetail::where('id_peminjaman', $p->id_peminjaman)->value('id_detail');
        $this->assertSame(7, $alat->fresh()->stok);

        $this->sebagai('laboran', $lab)->get(route('pengembalian.index'))->assertOk();
        $this->post(route('pengembalian.store', $p->id_peminjaman), [])->assertSessionHasErrors('jumlah');

        // pengembalian sebagian oleh laboran
        $this->post(route('pengembalian.store', $p->id_peminjaman), [
            'jumlah' => [$idDetail => 2], 'kondisi' => [$idDetail => 'baik'], 'keterangan' => 'Dikembalikan awal',
        ])->assertSessionHas('success');
        $this->assertSame(9, $alat->fresh()->stok); // 7 + 2
        $this->assertSame('disetujui', $p->fresh()->status);

        // melebihi sisa dipinjam ditolak
        $this->post(route('pengembalian.store', $p->id_peminjaman), [
            'jumlah' => [$idDetail => 2], 'kondisi' => [$idDetail => 'baik'],
        ])->assertSessionHasErrors('jumlah_dikembalikan');
        $this->assertSame(9, $alat->fresh()->stok);

        // sisa dikembalikan rusak -> selesai, stok tidak bertambah
        $this->post(route('pengembalian.store', $p->id_peminjaman), [
            'jumlah' => [$idDetail => 1], 'kondisi' => [$idDetail => 'rusak'],
        ])->assertSessionHas('success');
        $this->assertSame(['selesai', 9], [$p->fresh()->status, $alat->fresh()->stok]);
        $this->post(route('pengembalian.store', $p->id_peminjaman), [
            'jumlah' => [$idDetail => 1], 'kondisi' => [$idDetail => 'baik'],
        ])->assertSessionHasErrors('status'); // sudah selesai
    }
}
