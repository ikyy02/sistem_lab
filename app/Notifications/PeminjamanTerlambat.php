<?php

namespace App\Notifications;

class PeminjamanTerlambat extends PeminjamanNotification
{
    protected function judul(): string
    {
        return 'Peminjaman Terlambat';
    }

    protected function subjek(): string
    {
        return sprintf('[SIARKA] Peminjaman #%d terlambat dikembalikan', $this->peminjaman->id_peminjaman);
    }

    protected function pembuka(): string
    {
        return 'Peminjaman ini telah melewati tanggal rencana pengembalian ('
            .$this->waktu($this->peminjaman->tanggal_rencana_kembali)
            .'). Mohon segera kembalikan barang yang dipinjam.';
    }
}
