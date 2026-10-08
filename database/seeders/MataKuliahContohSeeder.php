<?php

namespace Database\Seeders;

use App\Models\MataKuliah;
use Illuminate\Database\Seeder;

/** Contoh isi Mata Kuliah (semester ganjil 2026/2027) untuk tampilan Data Akademik & Jadwal. */
class MataKuliahContohSeeder extends Seeder
{
    private const DATA = [
        ['kode_mk' => 'AII233307', 'nama_mk' => 'Elektronika Dasar dan Sensoring', 'sks' => 3],
        ['kode_mk' => 'AIK233204', 'nama_mk' => 'Integrasi Sistem', 'sks' => 3],
        ['kode_mk' => 'AIK233301', 'nama_mk' => 'Pemrograman Web Lanjut', 'sks' => 3],
        ['kode_mk' => 'AIK233302', 'nama_mk' => 'Sistem Operasi', 'sks' => 3],
        ['kode_mk' => 'AIK233303', 'nama_mk' => 'Teknik Pengambilan Keputusan', 'sks' => 2],
        ['kode_mk' => 'AIK233305', 'nama_mk' => 'IT Project 1', 'sks' => 3],
        ['kode_mk' => 'AIK233306', 'nama_mk' => 'Komunikasi Data dan Jaringan Komputer', 'sks' => 3],
    ];

    public function run(): void
    {
        foreach (self::DATA as $baris) {
            MataKuliah::firstOrCreate(['kode_mk' => $baris['kode_mk']], $baris + ['semester' => 'ganjil']);
        }
    }
}
