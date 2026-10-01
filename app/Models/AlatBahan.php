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
        'per_unit',
        'harga_total',
        'unit_dasar_harga',
    ];

    /**
     * Cast kolom stok ke integer.
     */
    protected $casts = [
        'stok' => 'integer',
        'per_unit' => 'decimal:2',
        'harga_total' => 'decimal:2',
        'unit_dasar_harga' => 'decimal:2',
    ];

    public const GAMBAR_DIR = 'uploads/inventaris';

    /** URL publik gambar (null jika belum ada). */
    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar ? asset(self::GAMBAR_DIR . '/' . $this->gambar) : null;
    }

    /**
     * Jumlah Unit = Jumlah Satuan Asli ÷ Jumlah Satuan Asli per Unit (khusus TPK/SPK).
     * Null jika pembagi belum diisi/tidak valid -> konversi dianggap belum diatur.
     */
    public function getJumlahUnitAttribute(): ?float
    {
        if ($this->per_unit === null || (float) $this->per_unit <= 0) {
            return null;
        }

        return round(((float) $this->stok) / (float) $this->per_unit, 2);
    }

    /** Harga per Unit = Harga Total ÷ Jumlah Unit Dasar Harga (khusus TPK/SPK). */
    public function getHargaPerUnitAttribute(): ?float
    {
        if ($this->harga_total === null || $this->unit_dasar_harga === null || (float) $this->unit_dasar_harga <= 0) {
            return null;
        }

        return round((float) $this->harga_total / (float) $this->unit_dasar_harga, 2);
    }
}
