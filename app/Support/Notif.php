<?php

namespace App\Support;

use App\Models\Akun;
use App\Models\Peminjaman;
use App\Notifications\PeminjamanSelesai;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Notif
{
    /**
     * @param  array<int, string|null>  $emails
     */
    public static function kirim(array $emails, Notification $notification): void
    {
        try {
            $emails = array_values(array_unique(array_filter($emails, fn ($email) => is_string($email) && $email !== '')));
            if ($emails === []) {
                return;
            }
            foreach (Akun::whereIn('email', $emails)->get() as $akun) {
                try {
                    $akun->notify($notification);
                } catch (\Throwable $e) {
                    Log::warning('Notif gagal dikirim ke '.$akun->email.': '.$e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Notif gagal dikirim: '.$e->getMessage());
        }
    }

    /**
     * Semua akun Laboran/Admin.
     *
     * @return array<int, string>
     */
    public static function laboran(): array
    {
        return Akun::where('role', Role::LABORAN)->pluck('email')->all();
    }

    /**
     * Email penyetuju pengajuan: dosen penanggung jawab bila jenis "atas_dosen", selain itu semua akun Laboran.
     *
     * @return array<int, string>
     */
    public static function penyetuju(Peminjaman $peminjaman): array
    {
        if ($peminjaman->jenis_peminjaman === 'atas_dosen' && $peminjaman->email_dosen) {
            return [$peminjaman->email_dosen];
        }

        return self::laboran();
    }

    /**
     * PeminjamanSelesai ke peminjam, hanya saat status benar-benar "selesai" dan maksimal sekali per transaksi
     * (penjaga cache menutup jalur kembalikan, selesaikan, dan sinkronSelesai yang dapat saling berlomba).
     */
    public static function peminjamanSelesai(Peminjaman $peminjaman): void
    {
        if ($peminjaman->status !== 'selesai') {
            return;
        }
        if (! Cache::add('notif:peminjaman-selesai:' . $peminjaman->id_peminjaman, 1, now()->addDays(30))) {
            return;
        }

        static::kirim([$peminjaman->email_peminjam], new PeminjamanSelesai($peminjaman));
    }
}
