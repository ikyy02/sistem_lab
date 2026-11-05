<?php

namespace App\Notifications;

class PeminjamanDibatalkan extends PeminjamanNotification
{
    protected function judul(): string
    {
        return 'Peminjaman Dibatalkan';
    }

    protected function subjek(): string
    {
        return sprintf('[SIARKA] Peminjaman #%d dibatalkan', $this->peminjaman->id_peminjaman);
    }

    protected function pembuka(): string
    {
        return 'Peminjaman berikut telah dibatalkan oleh peminjam.';
    }
}
