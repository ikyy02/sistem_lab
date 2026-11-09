<?php

namespace Tests\Feature;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\PengembalianDetail;
use App\Models\WaLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class WaKirimTerlambatTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set([
            'notifikasi.wa.enabled' => true,
            'notifikasi.wa.gateway_url' => 'http://127.0.0.1:3001',
            'notifikasi.wa.gateway_token' => 'test-token',
            'notifikasi.wa.jam_mulai' => '08:00',
            'notifikasi.wa.jam_selesai' => '16:00',
            'notifikasi.wa.maks_pengingat' => 3,
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'http://127.0.0.1:3001/send' => Http::response(['success' => true], 200),
        ]);
    }

    public function test_tidak_mengirim_ganda_untuk_id_peminjaman_dan_ke_ke_yang_sama(): void
    {
        $this->travelTo('2026-10-19 10:00:00');
        $p = $this->peminjamanTerlambat();
        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);

        $this->assertSame(1, WaLog::where('id_peminjaman', $p->id_peminjaman)->count());
        $this->assertDatabaseHas('wa_logs', [
            'id_peminjaman' => $p->id_peminjaman,
            'ke_ke' => 1,
            'status' => 'terkirim',
        ]);

        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);

        $this->assertSame(1, WaLog::where('id_peminjaman', $p->id_peminjaman)->count());
        $this->assertDatabaseMissing('wa_logs', [
            'id_peminjaman' => $p->id_peminjaman,
            'ke_ke' => 2,
        ]);
        Http::assertSentCount(1);
    }

    public function test_notifikasi_wa_berisi_data_peminjaman_dan_nomor_ternormalisasi(): void
    {
        $this->travelTo('2026-10-19 10:00:00');
        $p = $this->peminjamanTerlambat();
        $p->loadMissing(Peminjaman::withProfil());

        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);

        $nama = $p->mahasiswaPeminjam->nama;
        $barang = $p->details->first()->katalog->nama;
        $batas = $p->tanggal_rencana_kembali->copy()->setTimezone(config('app.timezone'));

        Http::assertSent(function (Request $request) use ($nama, $barang, $batas) {
            $pesan = $request['message'];

            return $request->url() === 'http://127.0.0.1:3001/send'
                && $request['to'] === '628123456789'
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && str_contains($pesan, $nama)
                && str_contains($pesan, $barang)
                && str_contains($pesan, 'Batas kembali: '.$batas->locale('id')->translatedFormat('l, j F Y').' pukul '.$batas->format('H.i').' WITA')
                && str_contains($pesan, 'Terlambat: 5 hari')
                && str_contains($pesan, 'SIARKA');
        });
    }

    public function test_mengirim_ke_nomor_wa_yang_diisi_saat_pengajuan(): void
    {
        $this->travelTo('2026-10-19 10:00:00');
        $p = $this->peminjamanTerlambat(['no_whatsapp' => '085751401610'], '08123456789');

        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);

        Http::assertSent(fn (Request $request) => $request['to'] === '6285751401610');
        $this->assertDatabaseHas('wa_logs', [
            'id_peminjaman' => $p->id_peminjaman,
            'ke_ke' => 1,
            'nomor' => '085751401610',
            'status' => 'terkirim',
        ]);
    }

    public function test_notifikasi_wa_dikirim_ke_dosen_peminjam(): void
    {
        $this->travelTo('2026-10-19 10:00:00');
        $dosen = $this->dosen(['no_whatsapp' => '08987654321']);
        $katalog = $this->katalog('alat', 10);

        $p = Peminjaman::create([
            'email_peminjam' => $dosen->email,
            'jenis_peminjaman' => 'pembelajaran',
            'status' => 'disetujui',
            'tanggal_pengajuan' => now()->subDays(10),
            'tanggal_peminjaman' => now()->subDays(10),
            'tanggal_rencana_kembali' => now()->subDays(5),
        ]);
        $p->details()->create(['id_katalog' => $katalog->id_katalog, 'jumlah' => 1]);

        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);

        Http::assertSent(function (Request $request) use ($dosen) {
            return $request['to'] === '628987654321'
                && str_contains($request['message'], $dosen->nama);
        });
    }

    public function test_notifikasi_wa_gateway_mati_dicatat_gagal_tanpa_exception(): void
    {
        $this->travelTo('2026-10-19 10:00:00');
        $p = $this->peminjamanTerlambat();

        Http::fake(fn () => throw new ConnectionException('Gateway mati'));

        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);

        $this->assertDatabaseHas('wa_logs', [
            'id_peminjaman' => $p->id_peminjaman,
            'ke_ke' => 1,
            'status' => 'gagal',
            'dikirim_at' => null,
        ]);
    }

    public function test_gateway_mati_tiga_jam_berturut_tidak_menghabiskan_jatah(): void
    {
        $this->travelTo('2026-10-19 09:00:00');
        $p = $this->peminjamanTerlambat();
        $mati = true;
        Http::fake(function () use (&$mati) {
            return $mati ? throw new ConnectionException('Gateway mati') : Http::response(['success' => true], 200);
        });

        foreach (['10:00', '11:00', '12:00'] as $jam) {
            $this->travelTo("2026-10-19 {$jam}:00");
            $this->artisan('wa:kirim-terlambat')->assertExitCode(0);
        }

        $this->assertSame(1, WaLog::where('id_peminjaman', $p->id_peminjaman)->count());
        $this->assertDatabaseHas('wa_logs', ['id_peminjaman' => $p->id_peminjaman, 'ke_ke' => 1, 'status' => 'gagal']);

        // Gateway pulih: tetap pengingat ke-1, lalu tidak dikirim lagi dalam 24 jam.
        $mati = false;
        $this->travelTo('2026-10-19 13:00:00');
        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);
        $this->travelTo('2026-10-19 14:00:00');
        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);

        $this->assertSame(1, WaLog::where('id_peminjaman', $p->id_peminjaman)->count());
        $this->assertDatabaseHas('wa_logs', ['id_peminjaman' => $p->id_peminjaman, 'ke_ke' => 1, 'status' => 'terkirim']);
        $this->assertSame('13:00', WaLog::where('id_peminjaman', $p->id_peminjaman)->first()->dikirim_at->format('H:i')); // jam 14:00 tidak kirim ulang
    }

    public function test_berhenti_setelah_tiga_pengingat(): void
    {
        $this->travelTo('2026-10-19 10:00:00');
        $p = $this->peminjamanTerlambat();

        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);

        $this->travelTo('2026-10-20 10:00:00');
        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);

        $this->travelTo('2026-10-21 10:00:00');
        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);

        $this->assertSame(3, WaLog::where('id_peminjaman', $p->id_peminjaman)->count());
        foreach ([1, 2, 3] as $keKe) {
            $this->assertDatabaseHas('wa_logs', [
                'id_peminjaman' => $p->id_peminjaman,
                'ke_ke' => $keKe,
                'status' => 'terkirim',
            ]);
        }

        $this->travelTo('2026-10-22 10:00:00');
        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);

        $this->assertSame(3, WaLog::where('id_peminjaman', $p->id_peminjaman)->count());
        $this->assertDatabaseMissing('wa_logs', [
            'id_peminjaman' => $p->id_peminjaman,
            'ke_ke' => 4,
        ]);
        Http::assertSentCount(3);
    }

    public function test_tidak_mengirim_di_luar_jam_operasional(): void
    {
        $this->travelTo('2026-10-19 10:00:00');
        $p = $this->peminjamanTerlambat();

        $this->travelTo('2026-10-19 22:00:00');
        $this->artisan('wa:kirim-terlambat')
            ->expectsOutput('Diluar jam operasional (08:00-16:00). Berhenti.')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('wa_logs', ['id_peminjaman' => $p->id_peminjaman]);
        Http::assertNothingSent();
    }

    public function test_tidak_mengirim_untuk_peminjaman_berstatus_selesai(): void
    {
        $this->travelTo('2026-10-19 10:00:00');
        $p = $this->peminjamanTerlambat(['status' => 'selesai']);

        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);

        $this->assertDatabaseMissing('wa_logs', ['id_peminjaman' => $p->id_peminjaman]);
        Http::assertNothingSent();
    }

    public function test_tidak_mengirim_untuk_peminjaman_yang_sudah_dikembalikan(): void
    {
        $this->travelTo('2026-10-19 10:00:00');
        $p = $this->peminjamanTerlambat();
        $laboran = $this->laboran();
        $detail = $p->details()->firstOrFail();

        $pengembalian = Pengembalian::create([
            'id_peminjaman' => $p->id_peminjaman,
            'tanggal_pengembalian' => now()->subDay(),
            'email_penerima' => $laboran->email,
        ]);
        PengembalianDetail::create([
            'id_pengembalian' => $pengembalian->id_pengembalian,
            'id_detail_peminjaman' => $detail->id_detail,
            'jumlah_dikembalikan' => $detail->jumlah,
            'kondisi' => 'baik',
        ]);

        $this->assertSame('disetujui', $p->fresh()->status);

        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);

        $this->assertDatabaseMissing('wa_logs', ['id_peminjaman' => $p->id_peminjaman]);
        Http::assertNothingSent();
    }

    public function test_tidak_mengirim_untuk_peminjaman_yang_melibatkan_ruangan(): void
    {
        $this->travelTo('2026-10-19 10:00:00');
        $p = $this->peminjamanTerlambat();
        $ruangan = $this->ruangan();

        $p->ruangans()->create([
            'id_ruangan' => $ruangan->id_ruangan,
            'tanggal_mulai' => now()->subDays(10),
            'tanggal_selesai' => now()->subDay(),
        ]);

        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);

        $this->assertDatabaseMissing('wa_logs', ['id_peminjaman' => $p->id_peminjaman]);
        Http::assertNothingSent();
    }

    public function test_nomor_kosong_dicatat_dilewati(): void
    {
        $this->travelTo('2026-10-19 10:00:00');
        $p = $this->peminjamanTerlambat([], null);

        $this->artisan('wa:kirim-terlambat')->assertExitCode(0);

        $this->assertDatabaseHas('wa_logs', [
            'id_peminjaman' => $p->id_peminjaman,
            'ke_ke' => 1,
            'status' => 'dilewati',
        ]);
        Http::assertNothingSent();
    }

    public function test_dry_run_tidak_mengirim_dan_tidak_menulis_log(): void
    {
        $this->travelTo('2026-10-19 10:00:00');
        $p = $this->peminjamanTerlambat();

        $this->artisan('wa:kirim-terlambat', ['--dry-run' => true])
            ->expectsOutput('DRY RUN: 1 peminjaman akan diproses.')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('wa_logs', ['id_peminjaman' => $p->id_peminjaman]);
        Http::assertNothingSent();
    }

    private function peminjamanTerlambat(array $override = [], ?string $noWhatsapp = '08123456789'): Peminjaman
    {
        $mahasiswa = $this->mahasiswa(['no_whatsapp' => $noWhatsapp]);
        $katalog = $this->katalog('alat', 10);

        $peminjaman = Peminjaman::create($override + [
            'email_peminjam' => $mahasiswa->email,
            'jenis_peminjaman' => 'pribadi',
            'status' => 'disetujui',
            'tanggal_pengajuan' => now()->subDays(10),
            'tanggal_peminjaman' => now()->subDays(10),
            'tanggal_rencana_kembali' => now()->subDays(5),
        ]);

        $peminjaman->details()->create([
            'id_katalog' => $katalog->id_katalog,
            'jumlah' => 1,
        ]);

        return $peminjaman;
    }
}
