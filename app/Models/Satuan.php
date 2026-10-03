<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    protected $table = 'satuans';
    protected $primaryKey = 'id_satuan';
    public $timestamps = false;
    protected $fillable = ['nama_satuan'];
}
