<?php

namespace App\Support;

use App\Models\Prodi;
use App\Models\Ruangan;
use App\Models\Satuan;

/** Sumber pilihan dropdown (id => nama), semuanya dari database. */
class Options
{
    public static function prodi(): array
    {
        return Prodi::orderBy('nama_prodi')->pluck('nama_prodi', 'id_prodi')->all();
    }

    public static function satuan(): array
    {
        return Satuan::orderBy('nama_satuan')->pluck('nama_satuan', 'id_satuan')->all();
    }

    public static function ruangan(): array
    {
        return Ruangan::orderBy('nama_ruangan')->pluck('nama_ruangan', 'id_ruangan')->all();
    }
}
