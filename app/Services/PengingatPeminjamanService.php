<?php

namespace App\Services;

use App\Models\Peminjaman;
use App\Notifications\PeminjamanTerlambat;
use App\Notifications\PengingatPengembalian;
use App\Support\Notif;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

/**
 * Pengingat harian peminjam:
 * - PengingatPengembalian untuk peminjaman disetujui yang alatnya belum kembali dan jatuh tempo besok;
 * - PeminjamanTerlambat untuk peminjaman disetujui yang sudah melewati tanggal rencana kembali.
 * Kirim ulang dicegah lewat cache key per (id_peminjaman, jenis, tanggal rencana kembali).
 */
class PengingatPeminjamanService
{
    public function __construct(private PeminjamanService $peminjaman)
    {
    }

    public function kirim(?CarbonInterface $sekarang = null): int
    {
        $sekarang = $sekarang ? Carbon::instance($sekarang) : now();
        $terkirim = 0;

        $besok = Peminjaman::with(Peminjaman::withProfil())
            ->where('status', 'disetujui')
            ->whereDate('tanggal_rencana_kembali', $sekarang->copy()->addDay()->toDateString())
            ->orderBy('id_peminjaman')
            ->get();
        foreach ($besok as $p) {
            if ($this->peminjaman->alatBelumKembali($p) && $this->belumPernahKirim('pengingat', $p)) {
                Notif::kirim([$p->email_peminjam], new PengingatPengembalian($p));
                $terkirim++;
            }
        }

        $lewat = Peminjaman::with(Peminjaman::withProfil())
            ->where('status', 'disetujui')
            ->where('tanggal_rencana_kembali', '<', $sekarang)
            ->orderBy('id_peminjaman')
            ->get();
        foreach ($lewat as $p) {
            if ($this->peminjaman->alatBelumKembali($p) && $this->belumPernahKirim('terlambat', $p)) {
                Notif::kirim([$p->email_peminjam], new PeminjamanTerlambat($p));
                $terkirim++;
            }
        }

        return $terkirim;
    }

    private function belumPernahKirim(string $jenis, Peminjaman $p): bool
    {
        $tanggal = $p->tanggal_rencana_kembali?->format('Y-m-d') ?? '-';

        return Cache::add('notif:pengingat:' . $jenis . ':' . $p->id_peminjaman . ':' . $tanggal, 1, now()->addDays(30));
    }
}
