<?php

namespace App\Support;

/**
 * Daftar pilihan tetap untuk dropdown. Ubah di sini bila daftar berubah.
 * (Satuan dikelola lewat modal pada halaman Alat/Bahan, disimpan di tabel `satuans`.)
 */
class Options
{
    public const PRODI = [
        'D3 Teknologi Informasi',
        'D4 Teknologi Rekayasa Komputer Jaringan',
        'D3 Akuntansi',
        'D4 Bisnis Digital',
    ];

    public static function kelas(): array
    {
        $out = [];
        foreach ([1, 2, 3, 4] as $tingkat) {
            foreach (['A', 'B', 'C', 'D', 'E'] as $rombel) {
                $out[] = $tingkat . $rombel;
            }
        }

        return $out;
    }

    public const KONDISI = [
        'alat' => ['Baik', 'Rusak', 'Hilang'],
        'bahan' => ['Baik', 'Rusak', 'Kadaluarsa'],
        'ruangan' => ['Tersedia', 'Tidak Tersedia'],
    ];
}
