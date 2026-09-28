<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboran extends Model
{
    protected $table = 'laborans';

    protected $fillable = ['nama', 'email', 'no_whatsapp', 'password'];
}
