<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Master SOP & Panduan laboratorium; isi dipakai halaman /sop (kartu + modal + cetak). */
class Sop extends Model
{
    protected $table = 'sops';

    protected $fillable = ['kode', 'judul', 'deskripsi', 'tujuan', 'poin', 'urutan'];

    protected function casts(): array
    {
        return ['poin' => 'array'];
    }
}
