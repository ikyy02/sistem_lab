<?php

namespace App\Notifications;

class PengajuanDiteruskanKeLaboran extends PeminjamanNotification
{
    protected function judul(): string
    {
        return 'Pengajuan Peminjaman Disetujui Dosen';
    }

    protected function subjek(): string
    {
        return sprintf('[SIARKA] Pengajuan peminjaman #%d disetujui Dosen (Menunggu Keputusan Akhir)', $this->peminjaman->id_peminjaman);
    }

    protected function pembuka(): string
    {
        return 'Pengajuan peminjaman telah disetujui/divalidasi oleh Dosen penanggung jawab dan kini menunggu keputusan akhir Anda sebagai Laboran.';
    }
}
