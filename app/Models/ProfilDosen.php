<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilDosen extends Model
{
    protected $table      = 'profil_dosen';
    protected $primaryKey = 'id_dosen';

    protected $fillable = [
        'user_id',
        'nip_nidn',
        'nama',
        'program_studi',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
