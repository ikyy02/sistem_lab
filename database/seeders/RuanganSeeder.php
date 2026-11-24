<?php

namespace Database\Seeders;

use App\Models\Ruangan;
use Illuminate\Database\Seeder;

class RuanganSeeder extends Seeder
{
    private const DATA = [
        [
            'nama_ruangan' => 'Lab Linus Torvalds',
            'keterangan' => 'Laboratorium Komputer & Jaringan (Lantai 2)',
            'gambar' => 'lt.jpg',
        ],
        [
            'nama_ruangan' => 'Lab Linus Torvalds 2',
            'keterangan' => 'Laboratorium Praktikum Sistem Operasi & Jaringan (Lantai 2)',
            'gambar' => 'lt2.jpg',
        ],
        [
            'nama_ruangan' => 'Lab Steve Jobs',
            'keterangan' => 'Laboratorium Desain, UI/UX & Pemrograman Visual (Lantai 2)',
            'gambar' => 'sj.jpg',
        ],
        [
            'nama_ruangan' => 'Lab Steve Jobs 2',
            'keterangan' => 'Laboratorium Multimedia & Pengembangan Mobile (Lantai 2)',
            'gambar' => 'sj2.jpg',
        ],
        [
            'nama_ruangan' => 'Lab Bill Gates',
            'keterangan' => 'Laboratorium Aplikasi Bisnis & Komputer Perkantoran (Lantai 1)',
            'gambar' => 'bg.jpg',
        ],
        [
            'nama_ruangan' => 'Lab Bill Gates 2',
            'keterangan' => 'Laboratorium Sistem Informasi & Basis Data (Lantai 1)',
            'gambar' => 'bg2.jpg',
        ],
        [
            'nama_ruangan' => 'Lab Kenneth Thompson',
            'keterangan' => 'Laboratorium Pemrograman Dasar & Algoritma (Lantai 3)',
            'gambar' => 'kt.jpg',
        ],
        [
            'nama_ruangan' => 'Lab Kenneth Thompson 2',
            'keterangan' => 'Laboratorium Rekayasa Perangkat Lunak Lanjut (Lantai 3)',
            'gambar' => 'kt2.jpg',
        ],
    ];

    public function run(): void
    {
        foreach (self::DATA as $item) {
            Ruangan::firstOrCreate(
                ['nama_ruangan' => $item['nama_ruangan']],
                [
                    'keterangan' => $item['keterangan'],
                    'gambar' => $item['gambar'],
                ]
            );
        }
    }
}
