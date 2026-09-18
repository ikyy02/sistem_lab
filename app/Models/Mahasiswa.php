<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    /**
     * Tabel yang digunakan.
     */
    protected $table = 'mahasiswas';

    /**
     * Kolom yang boleh diisi secara mass-assignment.
     * Sesuai struktur tabel: nim, nama, program_studi, email, no_whatsapp.
     */
    protected $fillable = [
        'nim',
        'nama',
        'program_studi',
        'email',
        'no_whatsapp',
    ];
}
