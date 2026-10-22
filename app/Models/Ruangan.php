<?php

namespace App\Models;

use App\Support\GambarKatalog;
use Illuminate\Database\Eloquent\Model;

// Master ruangan laboratorium; gambar disimpan di storage (lihat GambarKatalog).
class Ruangan extends Model
{
    public const GAMBAR_DIR = 'uploads/ruangan';

    protected $table = 'ruangans';
    protected $primaryKey = 'id_ruangan';
    public $timestamps = false;
    protected $fillable = ['nama_ruangan', 'keterangan', 'gambar'];

    // URL gambar via GambarKatalog (mendukung lokasi lama dan storage baru).
    public function getGambarUrlAttribute(): ?string
    {
        return GambarKatalog::url($this->gambar, self::GAMBAR_DIR);
    }
}
