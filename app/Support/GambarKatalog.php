<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Lokasi gambar katalog (alat/bahan/ruangan).
 * Gambar baru disimpan di storage/app/public/{dir} dan diakses lewat public/storage (php artisan storage:link).
 * Gambar lama masih ada di public/{dir}; tetap dipakai selama filenya ada, jadi tidak ada yang hilang.
 */
class GambarKatalog
{
    /** URL gambar: pakai file lama di public/ bila ada, selain itu lokasi baru di storage. */
    public static function url(?string $nama, string $dir): ?string
    {
        if (! $nama) {
            return null;
        }
        $nama = basename($nama);

        return is_file(public_path("$dir/$nama")) ? asset("$dir/$nama") : asset("storage/$dir/$nama");
    }

    /** Hapus file gambar yang sudah tidak dipakai, di lokasi baru maupun lama. */
    public static function hapus(?string $nama, string $dir): void
    {
        if ($nama) {
            $nama = basename($nama);
            Storage::disk('public')->delete("$dir/$nama");
            @unlink(public_path("$dir/$nama"));
        }
    }
}
