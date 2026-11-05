<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaLog extends Model
{
    protected $table = 'wa_logs';

    protected $fillable = [
        'id_peminjaman', 'jenis', 'ke_ke', 'nomor', 'status', 'dikirim_at',
    ];

    protected $casts = [
        'dikirim_at' => 'datetime',
    ];

    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'id_peminjaman', 'id_peminjaman');
    }
}
