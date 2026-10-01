<?php

namespace App\Support;

use App\Models\Kelas;
use App\Models\Prodi;
use App\Models\Satuan;

/**
 * Sumber pilihan dropdown. Semua berasal dari Kelola Data Master (database),
 * sehingga perubahan master langsung mengikuti seluruh form terkait.
 */
class Options
{
    /** Pilihan tetap (statis) Kondisi Alat/Bahan dan Status Ruangan. */
    public const KONDISI = [
        'alat' => ['Baik', 'Rusak', 'Hilang'],
        'bahan' => ['Baik', 'Rusak', 'Kadaluarsa'],
        'ruangan' => ['Tersedia', 'Tidak Tersedia'],
    ];

    public static function prodi(): array
    {
        return Prodi::orderBy('nama')->pluck('nama')->all();
    }

    public static function kelas(): array
    {
        return Kelas::orderBy('nama')->pluck('nama')->all();
    }

    public static function satuan(): array
    {
        return Satuan::orderBy('nama')->pluck('nama')->all();
    }

    public static function kondisi(string $jenis): array
    {
        return self::KONDISI[$jenis] ?? [];
    }
}
