<?php

namespace App\Services;

use App\Models\Peminjaman;
use App\Support\Notif;
use Carbon\CarbonInterface;

/**
 * Jalur sinkronSelesai (penjadwal): evaluasi status selesai, lalu kirim PeminjamanSelesai
 * sekali untuk tiap transaksi yang berubah dari disetujui menjadi selesai.
 */
class SinkronSelesaiService
{
    public function __construct(private PeminjamanService $peminjaman)
    {
    }

    public function jalankan(?CarbonInterface $sekarang = null): int
    {
        $kandidat = Peminjaman::where('status', 'disetujui')->pluck('id_peminjaman');
        $jumlah = $this->peminjaman->sinkronSelesai($sekarang);

        if ($jumlah > 0 && $kandidat->isNotEmpty()) {
            Peminjaman::with(Peminjaman::withProfil())
                ->whereIn('id_peminjaman', $kandidat)
                ->where('status', 'selesai')
                ->orderBy('id_peminjaman')
                ->get()
                ->each(fn ($p) => Notif::peminjamanSelesai($p));
        }

        return $jumlah;
    }
}
