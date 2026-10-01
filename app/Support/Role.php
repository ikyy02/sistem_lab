<?php

namespace App\Support;

use App\Models\Dosen;
use App\Models\Laboran;
use App\Models\Mahasiswa;
use App\Models\StaffProdi;

/**
 * Definisi 4 role/kategori pengguna. Satu tempat untuk model, label, dan halaman awal tiap role.
 */
class Role
{
    public const MAHASISWA = 'mahasiswa';
    public const DOSEN = 'dosen';
    public const STAFF = 'staff';
    public const LABORAN = 'laboran';

    public const ALL = [self::MAHASISWA, self::DOSEN, self::STAFF, self::LABORAN];

    public const MODELS = [
        self::MAHASISWA => Mahasiswa::class,
        self::DOSEN => Dosen::class,
        self::STAFF => StaffProdi::class,
        self::LABORAN => Laboran::class,
    ];

    public const LABELS = [
        self::MAHASISWA => 'Mahasiswa',
        self::DOSEN => 'Dosen',
        self::STAFF => 'Staff Prodi',
        self::LABORAN => 'Laboran/Admin',
    ];

    /** Halaman tujuan setelah login. */
    public static function home(string $role): string
    {
        return $role === self::LABORAN ? route('inventaris.index', ['kategori' => 'alat']) : route('katalog');
    }
}
