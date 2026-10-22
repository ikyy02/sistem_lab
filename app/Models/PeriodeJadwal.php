<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Periode berlaku jadwal perkuliahan. Hanya satu periode "berjalan"; periode sebelumnya menjadi "arsip" (hanya-baca). */
class PeriodeJadwal extends Model
{
    public const BERJALAN = 'berjalan';
    public const ARSIP = 'arsip';

    protected $table = 'periode_jadwals';
    protected $primaryKey = 'id_periode';
    public $timestamps = false;
    protected $fillable = ['nama', 'tanggal_mulai', 'tanggal_berakhir', 'status'];
    protected $casts = ['tanggal_mulai' => 'date:Y-m-d', 'tanggal_berakhir' => 'date:Y-m-d'];

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class, 'id_periode', 'id_periode');
    }

    // Periode yang sedang berjalan (paling akhir berakhirnya bila ada lebih dari satu).
    public static function berjalan(): ?self
    {
        return self::where('status', self::BERJALAN)->orderByDesc('tanggal_berakhir')->first();
    }

    public function terarsip(): bool
    {
        return $this->status === self::ARSIP;
    }
}
