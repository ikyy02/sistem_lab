<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengembalian extends Model
{
    protected $table = 'pengembalians';
    protected $primaryKey = 'id_pengembalian';
    public $timestamps = false;
    protected $fillable = ['id_peminjaman', 'tanggal_pengembalian', 'email_penerima', 'keterangan'];
    protected $casts = ['tanggal_pengembalian' => 'datetime'];

    public function details()
    {
        return $this->hasMany(PengembalianDetail::class, 'id_pengembalian', 'id_pengembalian');
    }
}
