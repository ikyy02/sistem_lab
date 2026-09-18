<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Role constants
     */
    const ROLE_MAHASISWA  = 'mahasiswa';
    const ROLE_DOSEN      = 'dosen';
    const ROLE_STAF_PRODI = 'staf_prodi';
    const ROLE_LABORAN    = 'laboran';

    /**
     * Status constants
     */
    const STATUS_AKTIF    = 'aktif';
    const STATUS_NONAKTIF = 'nonaktif';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'nim',
        'nidn',
        'nip',
        'no_hp',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // -----------------------------------------------------------------------
    // Role helpers
    // -----------------------------------------------------------------------

    public function isLaboran(): bool
    {
        return $this->role === self::ROLE_LABORAN;
    }

    public function isStafProdi(): bool
    {
        return $this->role === self::ROLE_STAF_PRODI;
    }

    public function isDosen(): bool
    {
        return $this->role === self::ROLE_DOSEN;
    }

    public function isMahasiswa(): bool
    {
        return $this->role === self::ROLE_MAHASISWA;
    }

    public function isAktif(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }

    /**
     * Kembalikan label role yang ramah tampilan.
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_MAHASISWA  => 'Mahasiswa',
            self::ROLE_DOSEN      => 'Dosen',
            self::ROLE_STAF_PRODI => 'Staf Prodi',
            self::ROLE_LABORAN    => 'Laboran',
            default               => ucfirst($this->role),
        };
    }

    /**
     * Kembalikan nomor identitas sesuai role (NIM / NIDN / NIP).
     */
    public function getIdentitasAttribute(): ?string
    {
        return match ($this->role) {
            self::ROLE_MAHASISWA  => $this->nim,
            self::ROLE_DOSEN      => $this->nidn,
            self::ROLE_STAF_PRODI,
            self::ROLE_LABORAN    => $this->nip,
            default               => null,
        };
    }

    /**
     * Label jenis identitas sesuai role.
     */
    public function getLabelIdentitasAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_MAHASISWA  => 'NIM',
            self::ROLE_DOSEN      => 'NIDN',
            self::ROLE_STAF_PRODI,
            self::ROLE_LABORAN    => 'NIP',
            default               => '-',
        };
    }

    // -----------------------------------------------------------------------
    // Relasi ke tabel profil
    // -----------------------------------------------------------------------

    /**
     * Relasi ke profil mahasiswa (one-to-one).
     */
    public function profilMahasiswa(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProfilMahasiswa::class, 'user_id');
    }

    /**
     * Relasi ke profil dosen (one-to-one).
     */
    public function profilDosen(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProfilDosen::class, 'user_id');
    }

    /**
     * Relasi ke profil laboran (one-to-one).
     */
    public function profilLaboran(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProfilLaboran::class, 'user_id');
    }

    /**
     * Relasi ke profil staf (one-to-one).
     */
    public function profilStaf(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProfilStaf::class, 'user_id');
    }

    /**
     * Helper: Ambil profil sesuai role user.
     */
    public function profil()
    {
        return match ($this->role) {
            self::ROLE_MAHASISWA  => $this->profilMahasiswa,
            self::ROLE_DOSEN      => $this->profilDosen,
            self::ROLE_LABORAN    => $this->profilLaboran,
            self::ROLE_STAF_PRODI => $this->profilStaf,
            default               => null,
        };
    }

    // -----------------------------------------------------------------------
    // Relasi (siap digunakan saat tabel peminjaman sudah tersedia)
    // -----------------------------------------------------------------------

    /**
     * Semua pengajuan peminjaman milik user ini.
     * Aktifkan setelah tabel peminjaman dibuat.
     */
    // public function peminjaman(): \Illuminate\Database\Eloquent\Relations\HasMany
    // {
    //     return $this->hasMany(\App\Models\Peminjaman::class);
    // }
}
