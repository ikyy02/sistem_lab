<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengembalianDetail extends Model
{
    public const KONDISI = ['baik', 'rusak'];

    protected $table = 'pengembalian_details';
    protected $primaryKey = 'id_detail_pengembalian';
    public $timestamps = false;
    protected $fillable = ['id_pengembalian', 'id_detail_peminjaman', 'jumlah_dikembalikan', 'kondisi', 'keterangan'];

    public function detailPeminjaman()
    {
        return $this->belongsTo(PeminjamanDetail::class, 'id_detail_peminjaman', 'id_detail');
    }
}
