<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Master satuan barang (meter, buah, dst.) untuk dropdown katalog.
class Satuan extends Model
{
    protected $table = 'satuans';
    protected $primaryKey = 'id_satuan';
    public $timestamps = false;
    protected $fillable = ['nama_satuan'];
}
