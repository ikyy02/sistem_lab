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
    public const STAFF = 'staff_prodi';
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

    /**
     * Halaman tujuan setelah login.
     * Laboran/Admin -> Kelola Katalog; Staf Prodi -> Dashboard Staf Prodi; lainnya -> Katalog.
     */
    public static function home(string $role): string
    {
        if ($role === self::LABORAN) {
            return route('kelola-katalog.index', ['kategori' => 'alat']);
        }

        return $role === self::STAFF ? route('dashboard') : route('katalog');
    }
}
