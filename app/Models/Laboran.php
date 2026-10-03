<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboran extends Model
{
    protected $table = 'laborans';
    protected $primaryKey = 'id_pegawai';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['id_pegawai', 'nama', 'email', 'no_whatsapp'];

    public function akun()
    {
        return $this->belongsTo(Akun::class, 'email', 'email');
    }
}
