<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Kegagalan level-file pada import mahasiswa (file rusak, kolom tidak sesuai,
 * terlalu banyak baris, gagal menyimpan). Pesannya aman ditampilkan ke pengguna.
 *
 * Kesalahan per-baris (data tidak valid / duplikat) BUKAN exception;
 * itu dikembalikan sebagai daftar error oleh MahasiswaImportService.
 */
class MahasiswaImportException extends RuntimeException
{
}
