<?php

namespace App\Models;

use App\Support\GambarProfil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/** Akun login. PK = email. Role: mahasiswa | dosen | staff_prodi | laboran (tanpa tabel role). */
class Akun extends Model
{
    use Notifiable;

    public const ROLES = ['mahasiswa', 'dosen', 'staff_prodi', 'laboran'];

    protected $table = 'akuns';
    // Email dipakai sebagai primary key; tabel lain menunjuk ke email ini (foreign key).
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['email', 'password', 'role', 'wajib_ganti_password', 'foto'];
    protected $casts = ['wajib_ganti_password' => 'boolean', 'password' => 'hashed'];
    protected $hidden = ['password'];

    // URL foto profil untuk ditampilkan di <img>; null bila belum mengunggah foto.
    public function getFotoUrlAttribute(): ?string
    {
        return GambarProfil::url($this->foto);
    }
}
