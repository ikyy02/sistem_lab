<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    public const STATUS = ['menunggu', 'disetujui', 'ditolak', 'selesai'];
    public const JENIS = ['pribadi', 'atas_dosen'];

    protected $table = 'peminjamans';
    protected $primaryKey = 'id_peminjaman';
    public $timestamps = false;
    protected $fillable = [
        'email_peminjam', 'jenis_peminjaman', 'email_dosen', 'tanggal_pengajuan', 'tanggal_peminjaman',
        'tanggal_rencana_kembali', 'status', 'keterangan', 'email_pemroses',
    ];
    protected $casts = [
        'tanggal_pengajuan' => 'datetime', 'tanggal_peminjaman' => 'datetime', 'tanggal_rencana_kembali' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(PeminjamanDetail::class, 'id_peminjaman', 'id_peminjaman');
    }

    public function ruangans()
    {
        return $this->hasMany(PeminjamanRuangan::class, 'id_peminjaman', 'id_peminjaman');
    }

    public function pengembalians()
    {
        return $this->hasMany(Pengembalian::class, 'id_peminjaman', 'id_peminjaman');
    }
}
