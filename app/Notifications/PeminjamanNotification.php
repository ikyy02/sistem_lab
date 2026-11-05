<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use App\Notifications\Concerns\MengirimEmail;
use App\Support\PesanPengingatWa;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

abstract class PeminjamanNotification extends Notification implements ShouldQueue
{
    use MengirimEmail, Queueable;

    public function __construct(public Peminjaman $peminjaman)
    {
        $this->afterCommit = true;
    }

    public function via(object $notifiable): array
    {
        return config('notifikasi.kirim_email_peminjaman', true)
            ? ['mail', 'database']
            : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $nama = 'emails.'.Str::kebab(class_basename(static::class));

        return $this->siapkanEmail(
            $this->subjek(),
            [$nama, $nama.'-text'],
            $this->dataEmail(),
        );
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'judul' => $this->judul(),
            'pesan' => $this->pembuka().' Peminjaman #'.$this->peminjaman->id_peminjaman.' oleh '.$this->peminjaman->nama_peminjam.'.',
        ];
    }

    abstract protected function judul(): string;

    abstract protected function pembuka(): string;

    protected function subjek(): string
    {
        return '[SIARKA] '.$this->judul().' #'.$this->peminjaman->id_peminjaman;
    }

    /**
     * @return array<string, mixed>
     */
    protected function dataEmail(): array
    {
        $p = $this->peminjaman;

        return [
            'judul' => $this->judul(),
            'pembuka' => $this->pembuka(),
            'nama' => $p->nama_peminjam,
            'id' => $p->id_peminjaman,
            'jenis' => $p->label_jenis,
            'items' => $p->details
                ->map(fn ($detail) => [
                    'nama' => $detail->katalog?->nama ?? '-',
                    'jumlah' => (int) $detail->jumlah,
                    'jenis' => $detail->katalog?->jenis === 'bahan' ? 'Bahan' : 'Alat',
                ])
                ->values()
                ->all(),
            'ruangan' => $p->ruangans
                ->map(fn ($ruangan) => [
                    'nama' => $ruangan->ruangan?->nama_ruangan ?? '-',
                    'mulai' => $this->waktu($ruangan->tanggal_mulai),
                    'selesai' => $this->waktu($ruangan->tanggal_selesai),
                ])
                ->values()
                ->all(),
            'batas_kembali' => $this->waktu($p->tanggal_rencana_kembali),
            'status' => $p->label_status,
            'tautan' => $this->tautan(),
        ];
    }

    protected function tautan(): string
    {
        return rtrim((string) config('app.url'), '/')
            .route('peminjaman.show', $this->peminjaman->id_peminjaman, false);
    }

    protected function waktu(mixed $nilai): string
    {
        if (! $nilai instanceof DateTimeInterface) {
            return '-';
        }

        $zona = (string) config('app.timezone');
        $waktu = CarbonImmutable::instance($nilai)->setTimezone($zona)->locale('id');
        $label = PesanPengingatWa::labelZona($zona, $waktu);
        $teks = $waktu->translatedFormat('l, j F Y').' pukul '.$waktu->format('H.i');

        return $label === '' ? $teks : $teks.' '.$label;
    }
}
