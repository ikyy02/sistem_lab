<?php

namespace Tests\Feature;

use App\Models\AlatBahan;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Services\PeminjamanService;
use Carbon\Carbon;
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

    private function ajukan(array $items, string $email, array $o = []): Peminjaman
    {
        return app(PeminjamanService::class)->ajukan($email, $o + [
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

    public function test_dosen_form_otomatis_pembelajaran_satu_hari_dan_jam_kerja(): void
    {
        Carbon::setTestNow('2030-01-07 08:00:00'); // Senin
        try {
            $d = $this->dosen();
            $k = $this->katalog('alat', 5);
            $this->sebagai('dosen', $d)->get(route('peminjaman.index'))
                ->assertOk()
                ->assertSee('Pembelajaran')
                ->assertDontSee('Katalog Tersedia')
                ->assertDontSee('Keperluan Peminjaman');

            // Satu hari, dalam jam kerja -> sukses; jenis otomatis "Pembelajaran".
            $this->post(route('peminjaman.store'), [
                'tanggal_peminjaman' => '2030-01-08 09:00',
                'tanggal_rencana_kembali' => '2030-01-08 11:00',
                'detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 2]],
            ])->assertRedirect(route('peminjaman.riwayat'))->assertSessionHas('success');

            $p = Peminjaman::orderByDesc('id_peminjaman')->first();
            $this->assertSame('pembelajaran', $p->jenis_peminjaman);
            $this->assertNull($p->email_dosen);
            $this->assertTrue($p->tanggal_peminjaman->isSameDay($p->tanggal_rencana_kembali));

            // Beda hari -> ditolak backend.
            $this->post(route('peminjaman.store'), [
                'tanggal_peminjaman' => '2030-01-08 09:00',
                'tanggal_rencana_kembali' => '2030-01-09 09:00',
                'detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 1]],
            ])->assertSessionHasErrors('tanggal_rencana_kembali');

            // Di luar jam kerja -> ditolak backend.
            $this->post(route('peminjaman.store'), [
                'tanggal_peminjaman' => '2030-01-08 18:00',
                'tanggal_rencana_kembali' => '2030-01-08 20:00',
                'detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 1]],
            ])->assertSessionHasErrors('tanggal_peminjaman');
        } finally {
            Carbon::setTestNow();
        }
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
        // pengembalian khusus Laboran: Dosen ditolak lewat URL maupun aksi langsung
        $this->get(route('pengembalian.index'))->assertForbidden();
        $this->post(route('pengembalian.store', 1), [])->assertForbidden();
    }

    public function test_form_dosen_dan_mahasiswa_terpisah_tanpa_riwayat_dan_tanpa_jadwal_per_ruangan(): void
    {
        $this->sebagai('mahasiswa', $this->mahasiswa())->get(route('peminjaman.index'))->assertOk()
            ->assertSee('name="jenis_peminjaman"', false)->assertSee('Jadwal berlaku untuk semua', false)
            ->assertDontSee('Riwayat Peminjaman</a>', false)->assertDontSee('[tanggal_mulai]', false)
            ->assertSee('id="btnAjukan"', false)->assertSee('disabled', false)->assertSee('Pilih minimal satu ruangan, alat, atau bahan yang ingin dipinjam sebelum mengajukan peminjaman.');
        $this->sebagai('dosen', $this->dosen())->get(route('peminjaman.index'))->assertOk()
            ->assertDontSee('name="jenis_peminjaman"', false)->assertDontSee('name="email_dosen"', false);
    }

    public function test_profil_dinonaktifkan(): void
    {
        $this->sebagai('mahasiswa', $this->mahasiswa())->get('/profil')->assertNotFound();
        $this->get(route('dashboard'))->assertOk()->assertDontSee('Profil Saya');
    }

    public function test_pengajuan_hanya_ruangan_atau_hanya_alat_dan_dosen_otomatis_pembelajaran(): void
    {
        $m = $this->mahasiswa();
        $r = $this->ruangan();
        $k = $this->katalog('alat', 5);
        $waktu = ['tanggal_peminjaman' => now()->addDay()->format('Y-m-d H:i:s'), 'tanggal_rencana_kembali' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s')];

        $this->sebagai('mahasiswa', $m);
        $this->post(route('peminjaman.store'), $waktu + ['jenis_peminjaman' => 'pribadi', 'keterangan' => 'Rapat', 'ruangan' => [['id_ruangan' => $r->id_ruangan]]])
            ->assertSessionHasNoErrors();
        $this->post(route('peminjaman.store'), $waktu + ['jenis_peminjaman' => 'pribadi', 'keterangan' => 'Alat', 'detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 1]]])
            ->assertSessionHasNoErrors();
        $this->post(route('peminjaman.store'), $waktu + ['jenis_peminjaman' => 'pribadi', 'keterangan' => 'Kosong'])
            ->assertSessionHasErrors();
        $this->assertSame(2, Peminjaman::count());

        // baris placeholder/kosong tidak dihitung sebagai item; pesan sesuai ketentuan
        $this->post(route('peminjaman.store'), $waktu + ['jenis_peminjaman' => 'pribadi', 'keterangan' => 'Placeholder',
            'detail' => [['id_katalog' => '', 'jumlah' => 1]], 'ruangan' => [['id_ruangan' => '']]])
            ->assertSessionHasErrors(['detail' => \App\Http\Controllers\PeminjamanController::PESAN_MINIMAL_ITEM]);
        $this->assertSame(2, Peminjaman::count());

        // token form yang sama tidak membuat pengajuan ganda
        $kirim = $waktu + ['jenis_peminjaman' => 'pribadi', 'keterangan' => 'Ganda', 'token_pengajuan' => 'token-uji-1',
            'detail' => [['id_katalog' => $k->id_katalog, 'jumlah' => 1]]];
        $this->post(route('peminjaman.store'), $kirim)->assertSessionHasNoErrors();
        $this->post(route('peminjaman.store'), $kirim)->assertRedirect(route('peminjaman.riwayat'));
        $this->assertSame(3, Peminjaman::count());

        // pengajuan yang gagal tidak menghabiskan token
        $gagal = $kirim + [];
        $gagal['token_pengajuan'] = 'token-uji-2';
        $gagal['detail'] = [['id_katalog' => $k->id_katalog, 'jumlah' => 99]];
        $this->post(route('peminjaman.store'), $gagal)->assertSessionHasErrors('detail');
        $gagal['detail'] = [['id_katalog' => $k->id_katalog, 'jumlah' => 1]];
        $this->post(route('peminjaman.store'), $gagal)->assertSessionHasNoErrors();
        $this->assertSame(4, Peminjaman::count());
        $p = Peminjaman::with('ruangans')->has('ruangans')->first();
        $this->assertSame($waktu['tanggal_peminjaman'], $p->ruangans->first()->tanggal_mulai->format('Y-m-d H:i:s'));
    }

    // ------------------------------------------------------------------ persetujuan

    public function test_setujui_dan_tolak_lewat_http(): void
    {
        Carbon::setTestNow('2030-01-07 09:00:00'); // Senin
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
        $own = $this->ajukan([['id_katalog' => $k->id_katalog, 'jumlah' => 1]], $dosenSendiri->email, [
            'tanggal_peminjaman' => now()->addDay()->format('Y-m-d H:i:s'),
            'tanggal_rencana_kembali' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
        ]);
        $this->sebagai('dosen', $dosenSendiri);
        $this->post(route('pengajuan.setujui', $own->id_peminjaman))->assertSessionHasErrors('email_pemroses');
        $this->assertSame('menunggu', $own->fresh()->status);
        Carbon::setTestNow();
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
