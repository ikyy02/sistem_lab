<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Notifications\AkunTerverifikasiNotification;
use App\Notifications\PeminjamanDibatalkan;
use App\Notifications\PeminjamanDisetujui;
use App\Notifications\PeminjamanDitolak;
use App\Notifications\PeminjamanSelesai;
use App\Notifications\PeminjamanTerlambat;
use App\Notifications\PengajuanBaru;
use App\Notifications\PengajuanDiterima;
use App\Notifications\PengembalianDicatat;
use App\Notifications\PengingatPengembalian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class TemplateEmailNotifikasiTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    public function test_setiap_notifikasi_memakai_subjek_dan_body_baru(): void
    {
        $mahasiswa = $this->mahasiswa();
        $laboran = $this->laboran();
        $akun = Akun::findOrFail($mahasiswa->email);
        $katalog = $this->katalog('alat', 10);
        $ruangan = $this->ruangan();

        $p = Peminjaman::create([
            'email_peminjam' => $mahasiswa->email,
            'no_whatsapp' => '08123456789',
            'jenis_peminjaman' => 'pribadi',
            'status' => 'disetujui',
            'tanggal_pengajuan' => now()->subDays(3),
            'tanggal_peminjaman' => now()->subDays(3),
            'tanggal_rencana_kembali' => now()->addDay(),
            'alasan_ditolak' => 'Jadwal laboratorium bentrok',
        ]);
        $detail = $p->details()->create(['id_katalog' => $katalog->id_katalog, 'jumlah' => 2]);
        $p->ruangans()->create([
            'id_ruangan' => $ruangan->id_ruangan,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addHour(),
        ]);
        $p->load(Peminjaman::withProfil());

        $pengembalian = Pengembalian::create([
            'id_peminjaman' => $p->id_peminjaman,
            'tanggal_pengembalian' => now(),
            'email_penerima' => $laboran->email,
        ]);
        $pengembalian->details()->create([
            'id_detail_peminjaman' => $detail->id_detail,
            'jumlah_dikembalikan' => 1,
            'kondisi' => 'baik',
        ]);
        $pengembalian->load('details.detailPeminjaman.katalog');

        $daftar = [
            'akun terverifikasi' => new AkunTerverifikasiNotification($mahasiswa->nama, 'mahasiswa', $mahasiswa->email),
            'pengajuan diterima' => new PengajuanDiterima($p),
            'pengajuan baru' => new PengajuanBaru($p),
            'peminjaman disetujui' => new PeminjamanDisetujui($p),
            'peminjaman ditolak' => new PeminjamanDitolak($p),
            'peminjaman dibatalkan' => new PeminjamanDibatalkan($p),
            'pengembalian dicatat' => new PengembalianDicatat($p, $pengembalian),
            'peminjaman selesai' => new PeminjamanSelesai($p),
            'pengingat pengembalian' => new PengingatPengembalian($p),
            'peminjaman terlambat' => new PeminjamanTerlambat($p),
        ];

        foreach ($daftar as $label => $notifikasi) {
            $this->periksa($label, $notifikasi, $akun);
        }
    }

    private function periksa(string $label, Notification $notifikasi, Akun $akun): void
    {
        $mail = $notifikasi->toMail($akun);

        $this->assertStringStartsWith('[SIARKA]', $mail->subject, $label);
        $this->assertStringNotContainsString('!', $mail->subject, $label);

        $views = array_values((array) $mail->view);
        $this->assertCount(2, $views, $label);
        $this->assertStringEndsWith('-text', $views[1], $label);

        $html = view($views[0], $mail->viewData)->render();
        $teks = view($views[1], $mail->viewData)->render();

        $this->assertNotSame('', trim(strip_tags($html)), $label);
        $this->assertNotSame('', trim(preg_replace('/\s+/', ' ', $teks)), $label);
        $this->assertStringContainsString(
            'Pesan otomatis dari SIARKA, Laboratorium Jurusan Komputer dan Bisnis.',
            $teks,
            $label,
        );
        $this->assertStringContainsString(
            'Pesan otomatis dari SIARKA, Laboratorium Jurusan Komputer dan Bisnis.',
            $html,
            $label,
        );
    }
}
