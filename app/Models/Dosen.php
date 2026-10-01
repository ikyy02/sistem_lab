<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    /**
     * Tabel yang digunakan — sesuai migrasi create_dosens_table.
     */
    protected $table = 'dosens';

    protected $primaryKey = 'nuptk_nidn';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Kolom yang boleh diisi secara mass-assignment.
     * Primary key: nuptk_nidn (identitas NUPTK/NIDN).
     */
    protected $fillable = [
        'nuptk_nidn',
        'nama',
        'program_studi',
        'email',
        'no_whatsapp',
        'password',
    ];
}