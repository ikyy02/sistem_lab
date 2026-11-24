<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    protected $table = 'hari_liburs';
    protected $primaryKey = 'id_libur';
    protected $fillable = ['tanggal', 'keterangan', 'jenis'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /** Cek apakah suatu tanggal merupakan hari libur kalender (libur nasional, cuti bersama, libur khusus). */
    public static function isLibur(CarbonInterface|string $tanggal): bool
    {
        $tglStr = $tanggal instanceof CarbonInterface ? $tanggal->format('Y-m-d') : substr((string) $tanggal, 0, 10);

        return self::where('tanggal', $tglStr)->exists();
    }
}
