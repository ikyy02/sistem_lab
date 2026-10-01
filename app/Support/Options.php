<?php

namespace App\Support;

use App\Models\Kelas;
use App\Models\MasterOption;
use App\Models\Prodi;
use App\Models\Satuan;

/**
 * Sumber pilihan dropdown. Semua berasal dari Kelola Data Master (database),
 * sehingga perubahan master langsung mengikuti seluruh form terkait.
 */
class Options
{
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

    public static function kategori(): array
    {
        return MasterOption::values('kategori');
    }

    /** Kondisi Alat/Bahan, atau Status Ruangan, sesuai jenis. */
    public static function kondisi(string $jenis): array
    {
        return $jenis === 'ruangan' ? MasterOption::values('status', 'ruangan') : MasterOption::values('kondisi', $jenis);
    }
}
