<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    protected $table = 'hari_libur';
    protected $primaryKey = 'id_libur';
    public $timestamps = false;
    protected $fillable = ['tanggal', 'keterangan'];
    protected $casts = ['tanggal' => 'date:Y-m-d'];
}
