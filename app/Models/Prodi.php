<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Master program studi.
class Prodi extends Model
{
    protected $table = 'prodis';
    protected $primaryKey = 'id_prodi';
    public $timestamps = false;
    protected $fillable = ['nama_prodi'];
}
