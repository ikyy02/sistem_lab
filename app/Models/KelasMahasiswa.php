<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Keanggotaan kelas mahasiswa per periode akademik (tahun_akademik + semester). */
class KelasMahasiswa extends Model
{
    protected $table = 'kelas_mahasiswas';

    protected $primaryKey = 'id_kelas_mahasiswa';

    public $timestamps = false;

    protected $fillable = ['nim', 'id_kelas', 'tahun_akademik', 'semester'];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }
}
