<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Ruangan yang dipinjam beserta rentang waktunya (satu peminjaman bisa lebih dari satu ruangan).
class PeminjamanRuangan extends Model
{
    protected $table = 'peminjaman_ruangans';
    protected $primaryKey = 'id_peminjaman_ruangan';
    public $timestamps = false;
    protected $fillable = ['id_peminjaman', 'id_ruangan', 'tanggal_mulai', 'tanggal_selesai', 'keterangan'];
    protected $casts = ['tanggal_mulai' => 'datetime', 'tanggal_selesai' => 'datetime'];

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman', 'id_peminjaman');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'id_ruangan', 'id_ruangan');
    }
}
