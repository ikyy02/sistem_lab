<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilStaf extends Model
{
    protected $table      = 'profil_staf';
    protected $primaryKey = 'id_staf';

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
