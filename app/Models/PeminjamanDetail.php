<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeminjamanDetail extends Model
{
    protected $table = 'peminjaman_details';
    protected $primaryKey = 'id_detail';
    public $timestamps = false;
    protected $fillable = ['id_peminjaman', 'id_katalog', 'jumlah'];

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman', 'id_peminjaman');
    }

    public function katalog()
    {
        return $this->belongsTo(AlatBahan::class, 'id_katalog', 'id_katalog');
    }
}
