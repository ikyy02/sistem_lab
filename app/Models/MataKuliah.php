<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Master mata kuliah. Dipakai oleh Jadwal Perkuliahan dan dashboard Staf Prodi. */
class MataKuliah extends Model
{
    protected $table = 'mata_kuliahs';
    protected $primaryKey = 'id_mata_kuliah';
    public $timestamps = false;
    protected $fillable = ['kode_mk', 'nama_mk', 'sks', 'semester'];

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class, 'id_mata_kuliah', 'id_mata_kuliah');
    }

    /** Label singkat untuk dropdown: "IF-101 — Basis Data". */
    public function label(): string
    {
        return $this->kode_mk . ' — ' . $this->nama_mk;
    }
}
