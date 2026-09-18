<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilLaboran extends Model
{
    protected $table      = 'profil_laboran';
    protected $primaryKey = 'id_laboran';

    protected $fillable = [
        'user_id',
        'nama',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
