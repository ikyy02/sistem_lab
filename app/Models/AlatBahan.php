<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlatBahan extends Model
{
    /**
     * Tabel yang digunakan — sesuai tabel yang sudah ada di database sistem_lab.
     */
    protected $table = 'alat_bahans';

    /**
     * Kolom yang boleh diisi secara mass-assignment.
     * Sesuai struktur tabel: nama, jenis, satuan, stok, kondisi, keterangan.
     */
    protected $fillable = [
        'nama',
        'jenis',
        'satuan',
        'stok',
        'kondisi',
        'keterangan',
    ];

    /**
     * Cast kolom stok ke integer.
     */
    protected $casts = [
        'stok' => 'integer',
    ];
}
