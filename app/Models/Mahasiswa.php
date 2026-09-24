<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    /**
     * Tabel yang digunakan.
     */
    protected $table = 'mahasiswas';

    /**
     * Kolom yang boleh diisi secara mass-assignment.
     * Sesuai struktur tabel: nim, nama, program_studi, email, no_whatsapp.
     */
    protected $fillable = [
        'nim',
        'nama',
        'program_studi',
        'email',
        'no_whatsapp',
    ];

    /**
     * Kolom yang dicari oleh fitur pencarian pada halaman Kelola Mahasiswa.
     */
    public const SEARCHABLE = ['nim', 'nama', 'program_studi', 'email'];

    /**
     * Pencarian: kata kunci dipecah per spasi. Setiap kata harus cocok dengan
     * salah satu kolom (NIM / Nama / Program Studi / Email).
     *
     * Contoh: "budi informatika" -> cocok dengan mahasiswa bernama Budi
     * yang program studinya mengandung "informatika".
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $words = preg_split('/\s+/u', trim((string) $term), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        // Batasi jumlah kata agar query tetap ringan.
        foreach (array_slice($words, 0, 5) as $word) {
            // "!" dipakai sebagai karakter escape (ESCAPE '!') agar % dan _ yang diketik
            // pengguna dianggap teks biasa, bukan wildcard. Cara ini portabel di MySQL & SQLite.
            $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word) . '%';

            $query->where(function (Builder $group) use ($like) {
                foreach (self::SEARCHABLE as $column) {
                    $group->orWhereRaw("{$column} LIKE ? ESCAPE '!'", [$like]);
                }
            });
        }

        return $query;
    }
}
