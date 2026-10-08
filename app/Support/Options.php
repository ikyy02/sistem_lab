<?php

namespace App\Support;

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\MataKuliah;
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

    /** Kelas (rombel) ditampilkan lengkap dengan prodi karena namanya unik per prodi. */
    public static function kelas(): array
    {
        return Kelas::with('prodi')->orderBy('nama_kelas')->orderBy('id_kelas')
            ->get()
            ->mapWithKeys(fn ($k) => [$k->id_kelas => $k->nama_kelas . ' — ' . ($k->prodi->nama_prodi ?? '—')])
            ->all();
    }

    public static function dosen(): array
    {
        return Dosen::orderBy('nama')->get()
            ->mapWithKeys(fn ($d) => [$d->nuptk_nidn => $d->nama . ' (' . $d->nuptk_nidn . ')'])
            ->all();
    }

    public static function mataKuliah(): array
    {
        return MataKuliah::orderBy('kode_mk')->get()
            ->mapWithKeys(fn ($m) => [$m->id_mata_kuliah => $m->label()])
            ->all();
    }
}
