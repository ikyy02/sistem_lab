<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Pengaturan jam & hari operasional laboratorium (satu baris, id = 1). */
class PengaturanOperasional extends Model
{
    public const HARI = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

    protected $table = 'pengaturan_operasional';
    public $timestamps = false;
    protected $fillable = ['jam_buka', 'jam_tutup', 'hari_operasional'];

    public static function ambil(): self
    {
        return self::firstOrCreate(['id' => 1], ['jam_buka' => '08:00:00', 'jam_tutup' => '16:00:00', 'hari_operasional' => 'senin,selasa,rabu,kamis,jumat']);
    }

    public function hariAktif(): array
    {
        return array_values(array_filter(explode(',', (string) $this->hari_operasional)));
    }
}
