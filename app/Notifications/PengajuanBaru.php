<?php

namespace App\Notifications;

class PengajuanBaru extends PeminjamanNotification
{
    protected function judul(): string
    {
        return 'Pengajuan Peminjaman Baru';
    }

    protected function subjek(): string
    {
        return sprintf('[SIARKA] Pengajuan peminjaman baru #%d', $this->peminjaman->id_peminjaman);
    }

    protected function pembuka(): string
    {
        return 'Ada pengajuan peminjaman baru yang menunggu persetujuan Anda.';
    }
}
