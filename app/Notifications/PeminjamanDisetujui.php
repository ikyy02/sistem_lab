<?php

namespace App\Notifications;

class PeminjamanDisetujui extends PeminjamanNotification
{
    protected function judul(): string
    {
        return 'Peminjaman Disetujui';
    }

    protected function subjek(): string
    {
        return sprintf('[SIARKA] Peminjaman #%d disetujui', $this->peminjaman->id_peminjaman);
    }

    protected function pembuka(): string
    {
        return 'Pengajuan peminjaman Anda telah disetujui.';
    }
}
