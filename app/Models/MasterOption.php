<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Pilihan tetap yang dikelola di Kelola Data Master: tipe kategori | status | kondisi (grup = alat/bahan/ruangan). */
class MasterOption extends Model
{
    protected $fillable = ['tipe', 'grup', 'nama'];

    public static function values(string $tipe, string $grup = ''): array
    {
        return static::where('tipe', $tipe)->where('grup', $grup)->orderBy('nama')->pluck('nama')->all();
    }
}
