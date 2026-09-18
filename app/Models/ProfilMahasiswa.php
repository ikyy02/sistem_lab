<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilMahasiswa extends Model
{
    protected $table      = 'profil_mahasiswa';
    protected $primaryKey = 'id_mahasiswa';

    protected $fillable = [
        'user_id',
        'nim',
        'nama',
        'program_studi',
        'no_whatsapp',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
