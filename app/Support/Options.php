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
    public static function prodi(?int $idProdi = null): array
    {
        return Prodi::when($idProdi, fn ($q) => $q->where('id_prodi', $idProdi))->orderBy('nama_prodi')->pluck('nama_prodi', 'id_prodi')->all();
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
    // Format teks pilihan di dropdown dibuat di sini; ubah di sini bila label dropdown ingin berbeda.
    public static function kelas(?int $idProdi = null): array
    {
        return Kelas::with('prodi')->when($idProdi, fn ($q) => $q->where('id_prodi', $idProdi))->orderBy('nama_kelas')->orderBy('id_kelas')
            ->get()
            ->mapWithKeys(fn ($k) => [$k->id_kelas => $k->nama_kelas . ' — ' . ($k->prodi->nama_prodi ?? '—')])
            ->all();
    }

    public static function dosen(?int $idProdi = null): array
    {
        return Dosen::batasProdi($idProdi)->orderBy('nama')->get()
            ->mapWithKeys(fn ($d) => [$d->nuptk_nidn => $d->nama . ' (' . $d->nuptk_nidn . ')'])
            ->all();
    }

    public static function mataKuliah(?int $idProdi = null): array
    {
        return MataKuliah::batasProdi($idProdi)->orderBy('kode_mk')->get()
            ->mapWithKeys(fn ($m) => [$m->id_mata_kuliah => $m->label()])
            ->all();
    }
}
