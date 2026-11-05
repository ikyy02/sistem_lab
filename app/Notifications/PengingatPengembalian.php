<?php

namespace App\Notifications;

class PengingatPengembalian extends PeminjamanNotification
{
    protected function judul(): string
    {
        return 'Pengingat Pengembalian';
    }

    protected function subjek(): string
    {
        return sprintf('[SIARKA] Pengingat pengembalian peminjaman #%d', $this->peminjaman->id_peminjaman);
    }

    protected function pembuka(): string
    {
        return 'Batas waktu pengembalian peminjaman ini adalah besok, '
            .$this->waktu($this->peminjaman->tanggal_rencana_kembali)
            .'. Mohon siapkan alat/bahan untuk dikembalikan ke laboratorium.';
    }
}
