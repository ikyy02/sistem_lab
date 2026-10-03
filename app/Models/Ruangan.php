<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    protected $table = 'ruangans';
    protected $primaryKey = 'id_ruangan';
    public $timestamps = false;
    protected $fillable = ['nama_ruangan', 'keterangan'];
}
