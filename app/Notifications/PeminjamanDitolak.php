<?php

namespace App\Notifications;

class PeminjamanDitolak extends PeminjamanNotification
{
    protected function judul(): string
    {
        return 'Peminjaman Ditolak';
    }

    protected function subjek(): string
    {
        return sprintf('[SIARKA] Peminjaman #%d ditolak', $this->peminjaman->id_peminjaman);
    }

    protected function pembuka(): string
    {
        $alasan = $this->peminjaman->alasan_ditolak;

        return 'Pengajuan peminjaman Anda ditolak.'
            .($alasan !== null && $alasan !== '' ? ' Alasan: '.$alasan : '');
    }
}
