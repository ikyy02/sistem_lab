<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffProdi extends Model
{
    protected $table = 'staff_prodis';
    protected $primaryKey = 'id_pegawai';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['id_pegawai', 'nama', 'id_prodi', 'email', 'no_whatsapp'];

    public function akun()
    {
        return $this->belongsTo(Akun::class, 'email', 'email');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }
}
