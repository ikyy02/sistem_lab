<?php

namespace App\Notifications;

class PengajuanDiterima extends PeminjamanNotification
{
    protected function judul(): string
    {
        return 'Pengajuan Peminjaman Diterima';
    }

    protected function subjek(): string
    {
        return sprintf('[SIARKA] Pengajuan peminjaman #%d telah diterima', $this->peminjaman->id_peminjaman);
    }

    protected function pembuka(): string
    {
        return 'Pengajuan peminjaman Anda telah kami terima dan sedang menunggu persetujuan.';
    }
}
