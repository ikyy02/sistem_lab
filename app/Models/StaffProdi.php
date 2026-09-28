<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffProdi extends Model
{
    protected $table = 'staff_prodis';

    protected $fillable = ['nama', 'program_studi', 'email', 'no_whatsapp', 'password'];
}
