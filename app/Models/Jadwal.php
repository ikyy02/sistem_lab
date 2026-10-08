<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    public const HARI = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

    protected $table = 'jadwals';

    protected $primaryKey = 'id_jadwal';

    public $timestamps = false;

    protected $fillable = [
        'id_kelas', 'nuptk_nidn', 'id_ruangan', 'hari', 'jam_mulai', 'jam_selesai', 'status', 'tahun_akademik', 'semester',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'id_ruangan', 'id_ruangan');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'nuptk_nidn', 'nuptk_nidn');
    }
}
