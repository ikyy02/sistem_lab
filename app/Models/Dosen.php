<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    protected $table = 'dosens';
    protected $primaryKey = 'nuptk_nidn';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['nuptk_nidn', 'nama', 'id_prodi', 'email', 'no_whatsapp', 'status'];

    public function akun()
    {
        return $this->belongsTo(Akun::class, 'email', 'email');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }
}
