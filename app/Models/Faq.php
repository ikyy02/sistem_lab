<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Pertanyaan yang sering diajukan; dipakai halaman Bantuan (/bantuan). */
class Faq extends Model
{
    protected $table = 'faqs';

    protected $fillable = ['kategori', 'pertanyaan', 'jawaban', 'urutan', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean', 'urutan' => 'integer'];
    }
}
