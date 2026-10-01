<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffProdi extends Model
{
    protected $table = 'staff_prodis';

    protected $primaryKey = 'id_pegawai';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id_pegawai', 'nama', 'program_studi', 'email', 'no_whatsapp', 'password'];
}
