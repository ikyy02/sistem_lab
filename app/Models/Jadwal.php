<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    public const HARI = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
    public const STATUS = ['aktif', 'dibatalkan'];
    public const SEMESTER = ['ganjil', 'genap'];

    /** Urutan hari untuk pengurutan (MySQL maupun SQLite). */
    public const HARI_SQL = "CASE hari WHEN 'senin' THEN 1 WHEN 'selasa' THEN 2 WHEN 'rabu' THEN 3 WHEN 'kamis' THEN 4 WHEN 'jumat' THEN 5 WHEN 'sabtu' THEN 6 ELSE 7 END";

    protected $table = 'jadwals';

    protected $primaryKey = 'id_jadwal';

    public $timestamps = false;

    protected $fillable = [
        'id_kelas', 'id_mata_kuliah', 'id_periode', 'nuptk_nidn', 'id_ruangan', 'hari', 'jam_mulai', 'jam_selesai', 'status', 'tahun_akademik', 'semester',
    ];

    public function periode()
    {
        return $this->belongsTo(PeriodeJadwal::class, 'id_periode', 'id_periode');
    }

    /** Jadwal milik periode arsip: hanya dapat dilihat, tidak dapat diubah atau dihapus. */
    public function terarsip(): bool
    {
        return $this->periode?->terarsip() ?? false;
    }

    /** Jadwal yang sedang berlaku: periode berjalan (atau belum terhubung ke periode mana pun). */
    public function scopeBerlaku($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('jadwals.id_periode')
                ->orWhereIn('jadwals.id_periode', PeriodeJadwal::query()->where('status', PeriodeJadwal::BERJALAN)->select('id_periode'));
        });
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'id_mata_kuliah', 'id_mata_kuliah');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'nuptk_nidn', 'nuptk_nidn');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'id_ruangan', 'id_ruangan');
    }
}
