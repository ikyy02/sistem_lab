<?php

namespace App\Services;

use App\Models\AlatBahan;
use App\Models\Peminjaman;
use App\Models\PengaturanOperasional;

/**
 * Data Dashboard Mahasiswa. Semua query diskop ke akun yang login (email & NIM dari AuthService::user());
 * tidak ada id dari request. "Pembaruan terbaru" diturunkan dari peminjamans.tanggal_persetujuan (tanpa tabel
 * baru), dan status selesai/terlambat memakai logika yang sudah ada (scheduler sinkronSelesai + accessor
 * Peminjaman::terlambat) sehingga tidak diduplikasi.
 */
class DashboardMahasiswaService
{
    /** Nama hari (indeks date('w')) untuk status operasional laboratorium hari ini. */
    private const HARI = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

    /**
     * @param  array{role:string,key:string,nama:string,email:string}  $me
     * @return array<string,mixed>
     */
    public function untuk(array $me): array
    {
        $email = $me['email'];

        // a) ringkasan: satu query groupBy
        $hitung = Peminjaman::query()->selectRaw('status, COUNT(*) as jml')
            ->where('email_peminjam', $email)->groupBy('status')->pluck('jml', 'status');

        // b) peminjaman yang harus dikembalikan (sumber terlambat, terdekat, dan daftar wajib kembali)
        $aktif = Peminjaman::query()->with(Peminjaman::withProfil())
            ->where('email_peminjam', $email)->where('status', 'disetujui')
            ->orderBy('tanggal_rencana_kembali')->get();
        $terlambat = $aktif->filter(fn ($p) => $p->terlambat)->count();
        $dekat = $aktif->filter(fn ($p) => ! $p->terlambat
            && $p->tanggal_rencana_kembali !== null
            && $p->tanggal_rencana_kembali->lte(now()->addDay()))->count();
        $terdekat = $aktif->filter(fn ($p) => $p->tanggal_peminjaman !== null && $p->tanggal_peminjaman->gte(now()))
            ->sortBy('tanggal_peminjaman')->first();

        // c) pembaruan terbaru: pengajuan yang diproses 7 hari terakhir (turunan, tanpa tabel notifikasi)
        $pembaruan = Peminjaman::query()->with(Peminjaman::withProfil())
            ->where('email_peminjam', $email)->whereNotNull('tanggal_persetujuan')
            ->where('tanggal_persetujuan', '>=', now()->subDays(7))
            ->orderByDesc('tanggal_persetujuan')->limit(5)->get();

        // d) pengajuan terbaru
        $terbaru = Peminjaman::query()->with(Peminjaman::withProfil())
            ->where('email_peminjam', $email)
            ->orderByDesc('tanggal_pengajuan')->limit(5)->get();

        $hariIni = self::HARI[(int) now()->format('w')];

        // f) status laboratorium hari ini
        $setting = PengaturanOperasional::ambil();

        // g) katalog tersedia untuk pintasan
        $katalogTersedia = AlatBahan::query()->where('stok', '>', 0)->count();

        return [
            'ringkasan' => [
                'menunggu' => (int) ($hitung['menunggu'] ?? 0),
                'disetujui' => (int) ($hitung['disetujui'] ?? 0),
                'ditolak' => (int) ($hitung['ditolak'] ?? 0),
                'selesai' => (int) ($hitung['selesai'] ?? 0),
                'total' => (int) $hitung->sum(),
                'terlambat' => $terlambat,
                'dekat' => $dekat,
            ],
            'harusDikembalikan' => $aktif,
            'terdekat' => $terdekat,
            'pembaruan' => $pembaruan,
            'terbaru' => $terbaru,
            'hariIni' => $hariIni,
            'lab' => $this->statusLaboratorium($setting),
            'katalogTersedia' => $katalogTersedia,
        ];
    }

    /** @return array{status:string,label:string,keterangan:string,jam:string} */
    private function statusLaboratorium(PengaturanOperasional $setting): array
    {
        $jam = now()->format('H:i:s');
        $rentang = substr($setting->jam_buka, 0, 5).'–'.substr($setting->jam_tutup, 0, 5);
        $hariIni = self::HARI[(int) now()->format('w')];
        if (! in_array($hariIni, $setting->hariAktif(), true)) {
            return ['status' => 'tutup', 'label' => 'Tutup', 'keterangan' => 'Laboratorium tidak beroperasi pada hari ini.', 'jam' => $rentang];
        }
        if ($jam < $setting->jam_buka || $jam >= $setting->jam_tutup) {
            return ['status' => 'tutup', 'label' => 'Tutup', 'keterangan' => 'Di luar jam operasional laboratorium.', 'jam' => $rentang];
        }

        return ['status' => 'buka', 'label' => 'Buka', 'keterangan' => 'Laboratorium sedang beroperasi.', 'jam' => $rentang];
    }
}
