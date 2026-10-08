<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Kelas dipakai oleh Jadwal dan oleh relasi KelasMahasiswa (keanggotaan per semester).
 * Mahasiswa tidak menyimpan kelas sebagai kolom; hubungannya lewat kelas_mahasiswas.
 */
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

    /** @return Collection<int,KelasMahasiswa> */
    public function mahasiswas()
    {
        return $this->hasMany(KelasMahasiswa::class, 'id_kelas', 'id_kelas');
    }
}
