<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    public const GAMBAR_DIR = 'uploads/ruangan';

    protected $table = 'ruangans';
    protected $primaryKey = 'id_ruangan';
    public $timestamps = false;
    protected $fillable = ['nama_ruangan', 'keterangan', 'gambar'];

    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar ? asset(self::GAMBAR_DIR . '/' . $this->gambar) : null;
    }
}
