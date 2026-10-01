<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboran extends Model
{
    protected $table = 'laborans';

    protected $primaryKey = 'id_pegawai';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id_pegawai', 'nama', 'email', 'no_whatsapp', 'password'];
}
