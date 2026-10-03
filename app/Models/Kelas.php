<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Kelas hanya dipakai oleh Jadwal (bukan FK di Mahasiswa). */
class Kelas extends Model
{
    protected $table = 'kelas';
    protected $primaryKey = 'id_kelas';
    public $timestamps = false;
    protected $fillable = ['nama_kelas', 'id_prodi'];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }
}
