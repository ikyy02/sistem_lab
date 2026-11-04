<?php

namespace App\Support;

use Illuminate\Contracts\Database\Query\Builder;

/** Pencarian LIKE yang aman: kata dipecah (maks 5), karakter ! % _ di-escape, dipasang dengan ESCAPE '!'. */
class Cari
{
    public const MAKS_KATA = 5;

    /** @return list<string> */
    public static function kata(string $teks): array
    {
        return array_slice(preg_split('/\s+/u', $teks, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, self::MAKS_KATA);
    }

    /** Pola `%kata%` untuk `LIKE ? ESCAPE '!'`. */
    public static function like(string $kata): string
    {
        return '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $kata).'%';
    }

    /** Tiap kata harus cocok di salah satu kolom. */
    public static function kolom(Builder $query, string $teks, array $kolom): void
    {
        foreach (self::kata($teks) as $kata) {
            $like = self::like($kata);
            $query->where(function ($g) use ($kolom, $like) {
                foreach ($kolom as $col) {
                    $g->orWhereRaw("{$col} LIKE ? ESCAPE '!'", [$like]);
                }
            });
        }
    }
}
