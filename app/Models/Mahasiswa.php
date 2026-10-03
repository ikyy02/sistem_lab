<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswas';
    protected $primaryKey = 'nim';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['nim', 'nama', 'id_prodi', 'email', 'no_whatsapp'];

    public function akun()
    {
        return $this->belongsTo(Akun::class, 'email', 'email');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }
}
