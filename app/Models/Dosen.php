<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    /**
     * Tabel yang digunakan — sesuai migrasi create_dosens_table.
     */
    protected $table = 'dosens';

    /**
     * Kolom yang boleh diisi secara mass-assignment.
     * Sesuai struktur tabel: nidn, nip, nama, program_studi, email, no_whatsapp.
     */
    protected $fillable = [
        'nidn',
        'nip',
        'nama',
        'program_studi',
        'email',
        'no_whatsapp',
    ];
}