<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Master mata kuliah milik satu prodi (id_prodi NULL = data lama yang belum dapat dipetakan). Dipakai Jadwal Perkuliahan dan dashboard Staf Prodi. */
class MataKuliah extends Model
{
    protected $table = 'mata_kuliahs';
    protected $primaryKey = 'id_mata_kuliah';
    public $timestamps = false;
    protected $fillable = ['kode_mk', 'nama_mk', 'sks', 'semester', 'id_prodi'];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'id_prodi', 'id_prodi');
    }

    /** Batas prodi Staf Prodi (AuthService::batasProdi()); null (Laboran) = semua prodi. */
    public function scopeBatasProdi($query, ?int $idProdi)
    {
        return $query->when($idProdi, fn ($q) => $q->where('mata_kuliahs.id_prodi', $idProdi));
    }

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
