<?php

namespace App\Notifications;

class PeminjamanSelesai extends PeminjamanNotification
{
    protected function judul(): string
    {
        return 'Peminjaman Selesai';
    }

    protected function subjek(): string
    {
        return sprintf('[SIARKA] Peminjaman #%d selesai', $this->peminjaman->id_peminjaman);
    }

    protected function pembuka(): string
    {
        return 'Peminjaman berikut telah selesai dan seluruh prosesnya ditutup.';
    }
}
