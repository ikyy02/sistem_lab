<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlatBahan extends Model
{
    /**
     * Nilai yang diterima kolom `jenis`.
     */
    public const JENIS = ['alat', 'bahan', 'ruangan'];

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
        'gambar',
    ];

    /**
     * Cast kolom stok ke integer.
     */
    protected $casts = [
        'stok' => 'integer',
    ];

    public const GAMBAR_DIR = 'uploads/inventaris';

    /** URL publik gambar (null jika belum ada). */
    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar ? asset(self::GAMBAR_DIR . '/' . $this->gambar) : null;
    }
}
