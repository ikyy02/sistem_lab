<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Akun login. PK = email. Role: mahasiswa | dosen | staff_prodi | laboran (tanpa tabel role). */
class Akun extends Model
{
    public const ROLES = ['mahasiswa', 'dosen', 'staff_prodi', 'laboran'];

    protected $table = 'akuns';
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['email', 'password', 'role'];
    protected $hidden = ['password'];
}
