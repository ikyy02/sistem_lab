<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Lokasi foto profil pengguna (semua role).
 * Foto disimpan di storage/app/public/{dir} dan diakses lewat public/storage (php artisan storage:link).
 */
class GambarProfil
{
    public const DIR = 'profil';

    /** URL foto profil untuk ditampilkan di <img>. */
    public static function url(?string $nama): ?string
    {
        if (! $nama) {
            return null;
        }
        $nama = basename($nama);

        return is_file(public_path(self::DIR.'/'.$nama)) ? asset(self::DIR.'/'.$nama) : asset('storage/'.self::DIR.'/'.$nama);
    }

    /** Hapus file foto yang sudah tidak dipakai. */
    public static function hapus(?string $nama): void
    {
        if ($nama) {
            $nama = basename($nama);
            Storage::disk('public')->delete(self::DIR.'/'.$nama);
            @unlink(public_path(self::DIR.'/'.$nama));
        }
    }
}
