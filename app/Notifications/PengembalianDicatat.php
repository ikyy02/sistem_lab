<?php

namespace App\Notifications;

use App\Models\Dosen;
use App\Models\Laboran;
use App\Models\Peminjaman;
use App\Models\Pengembalian;

class PengembalianDicatat extends PeminjamanNotification
{
    /** @var array<int, array{nama:string, jumlah:int}> Sisa saat pengembalian dicatat (snapshot agar tetap benar saat antrean diproses). */
    private array $sisa = [];

    public function __construct(Peminjaman $peminjaman, public Pengembalian $pengembalian)
    {
        parent::__construct($peminjaman);
        $this->sisa = $this->hitungSisa();
    }

    protected function judul(): string
    {
        return 'Pengembalian Dicatat';
    }

    protected function pembuka(): string
    {
        return 'Pengembalian barang untuk peminjaman #'.$this->peminjaman->id_peminjaman.' telah dicatat.';
    }

    protected function subjek(): string
    {
        return sprintf('[SIARKA] Pengembalian peminjaman #%d dicatat', $this->peminjaman->id_peminjaman);
    }

    protected function dataEmail(): array
    {
        return array_merge(parent::dataEmail(), [
            'penerima' => $this->namaPenerima(),
            'waktu_pencatatan' => $this->waktu($this->pengembalian->tanggal_pengembalian),
            'dikembalikan' => $this->pengembalian->details
                ->map(fn ($detail) => [
                    'nama' => $detail->detailPeminjaman?->katalog?->nama ?? '-',
                    'jumlah' => (int) $detail->jumlah_dikembalikan,
                    'kondisi' => $this->labelKondisi($detail->kondisi),
                ])
                ->values()
                ->all(),
            'sisa' => $this->sisa,
            'rusak' => $this->daftarRusak(),
        ]);
    }

    public function toDatabase(object $notifiable): array
    {
        $baik = $this->pengembalian->details->where('kondisi', 'baik')->sum('jumlah_dikembalikan');
        $rusak = collect($this->daftarRusak())->sum('jumlah');
        $sisa = collect($this->sisa)->sum('jumlah');

        return [
            'judul' => $this->judul(),
            'pesan' => 'Pengembalian peminjaman #'.$this->peminjaman->id_peminjaman.' oleh '.$this->peminjaman->nama_peminjam
                .' dicatat oleh '.$this->namaPenerima().'. '.(int) $baik.' barang baik, '.$rusak.' rusak, '.$sisa.' belum kembali.',
        ];
    }

    private function namaPenerima(): string
    {
        $email = $this->pengembalian->email_penerima;

        return Laboran::where('email', $email)->value('nama')
            ?? Dosen::where('email', $email)->value('nama')
            ?? $email;
    }

    /** @return array<int, array{nama:string, jumlah:int}> */
    private function hitungSisa(): array
    {
        $p = $this->peminjaman;
        $p->load(['details.katalog', 'pengembalians.details']);

        $sudah = $p->pengembalians->flatMap->details
            ->groupBy('id_detail_peminjaman')
            ->map(fn ($g) => (int) $g->sum('jumlah_dikembalikan'));

        return $p->details
            ->map(fn ($d) => [
                'nama' => $d->katalog?->nama ?? '-',
                'jumlah' => (int) $d->jumlah - (int) ($sudah[$d->id_detail] ?? 0),
            ])
            ->filter(fn ($r) => $r['jumlah'] > 0)
            ->values()
            ->all();
    }

    /** @return array<int, array{nama:string, jumlah:int}> */
    private function daftarRusak(): array
    {
        return $this->pengembalian->details
            ->where('kondisi', 'rusak')
            ->map(fn ($d) => [
                'nama' => $d->detailPeminjaman?->katalog?->nama ?? '-',
                'jumlah' => (int) $d->jumlah_dikembalikan,
            ])
            ->values()
            ->all();
    }

    private function labelKondisi(?string $kondisi): string
    {
        return match ($kondisi) {
            'baik' => 'Baik',
            'rusak' => 'Rusak',
            default => '-',
        };
    }
}
