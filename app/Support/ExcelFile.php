<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

/**
 * Satu-satunya pintu baca file impor SIARKA: hanya Excel (.xlsx / .xls).
 * Dipakai semua modul impor, jadi aturan format tidak bisa berbeda antarmodul.
 */
class ExcelFile
{
    public const EXTENSIONS = ['xlsx', 'xls'];

    public const NOT_EXCEL = 'File bukan Excel yang valid. Gunakan file .xlsx atau .xls, atau unduh template yang disediakan.';

    /** Aturan validasi request (backend): ekstensi Excel + ukuran. */
    public static function rules(int $maxKb): array
    {
        return ['required', 'file', 'extensions:' . implode(',', self::EXTENSIONS), 'max:' . $maxKb];
    }

    /** Pesan validasi untuk rules() agar seragam di semua modul. */
    public static function messages(int $maxKb): array
    {
        return [
            'file.required' => 'Pilih file Excel (.xlsx atau .xls) terlebih dahulu.',
            'file.uploaded' => 'File gagal diunggah. Pastikan ukuran file tidak lebih dari ' . $maxKb / 1024 . ' MB.',
            'file.file' => 'File tidak valid.',
            'file.extensions' => 'File harus berformat Excel (.xlsx atau .xls).',
            'file.max' => 'Ukuran file maksimal ' . $maxKb / 1024 . ' MB.',
        ];
    }

    /**
     * Baca sheet pertama sebagai array 2D (nilai mentah). Isi file harus cocok dengan ekstensinya,
     * jadi CSV atau file lain yang sekadar di-rename ditolak.
     *
     * @return array<int, array<int, mixed>>
     */
    public static function rows(UploadedFile $file, int $limit): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();
        if (! in_array($ext, self::EXTENSIONS, true) || $path === false || ! is_readable($path)) {
            throw new RuntimeException(self::NOT_EXCEL);
        }

        try {
            $reader = IOFactory::createReader(ucfirst($ext));
            if (! $reader->canRead($path)) {
                throw new RuntimeException(self::NOT_EXCEL);
            }
            // Cek ukuran sebelum memuat: file kecil bisa berisi jutaan baris dan menghabiskan memori.
            if (($reader->listWorksheetInfo($path)[0]['totalRows'] ?? 0) > $limit) {
                throw new RuntimeException("File terlalu besar: maksimal {$limit} baris. Hapus baris kosong di bawah data atau pecah menjadi beberapa file.");
            }
            $reader->setReadDataOnly(true);
            $data = $reader->load($path)->getSheet(0)->toArray(null, true, false, false);
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable) {
            throw new RuntimeException('File Excel tidak dapat dibaca atau rusak. Unduh template dan isi ulang.');
        }

        return array_slice($data, 0, $limit);
    }
}
