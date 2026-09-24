<?php

namespace App\Services;

use App\Exceptions\MahasiswaImportException;
use App\Http\Requests\MahasiswaRequest;
use App\Models\Mahasiswa;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

/**
 * Import data mahasiswa dari file Excel (.xlsx / .xls).
 *
 * Prinsip: ALL-OR-NOTHING.
 *  1. Seluruh baris dibaca dan divalidasi terlebih dahulu (tanpa menyentuh tabel).
 *  2. Jika ada SATU saja baris bermasalah -> tidak ada data yang disimpan,
 *     daftar baris bermasalah + alasannya dikembalikan ke pengguna.
 *  3. Jika semua valid -> semua baris disimpan dalam SATU transaksi database.
 *
 * Tidak ada perubahan struktur database: data masuk ke tabel `mahasiswas` yang sudah ada.
 */
class MahasiswaImportService
{
    /** Nama sheet pada template. Jika tidak ada di file, sheet pertama yang dibaca. */
    public const SHEET_NAME = 'Data Mahasiswa';

    /** Batas jumlah baris data per file. */
    public const MAX_ROWS = 5000;

    /** Batas jumlah baris bermasalah yang ditampilkan ke pengguna. */
    public const MAX_DISPLAYED_ERRORS = 200;

    /** Judul kolom pada template => nama kolom database. Urutan = urutan kolom template. */
    public const COLUMNS = [
        'NIM' => 'nim',
        'Nama' => 'nama',
        'Program Studi' => 'program_studi',
        'No WhatsApp' => 'no_whatsapp',
        'Email' => 'email',
    ];

    /**
     * Variasi penulisan judul kolom yang diterima (setelah huruf kecil & tanda baca/spasi dibuang),
     * sehingga "No. WhatsApp", "no whatsapp", "Nomor WhatsApp" dianggap sama.
     */
    private const HEADER_ALIASES = [
        'nim' => 'nim',
        'nama' => 'nama',
        'namamahasiswa' => 'nama',
        'programstudi' => 'program_studi',
        'prodi' => 'program_studi',
        'nowhatsapp' => 'no_whatsapp',
        'nomorwhatsapp' => 'no_whatsapp',
        'nowa' => 'no_whatsapp',
        'whatsapp' => 'no_whatsapp',
        'email' => 'email',
    ];

    /** Batas sheet mentah (sebelum baris kosong dibuang) agar file aneh tidak menghabiskan memori. */
    private const MAX_SHEET_ROWS = 20000;

    /** Jumlah baris per INSERT (100 baris x 7 kolom = 700 parameter, aman untuk semua driver). */
    private const INSERT_CHUNK = 100;

    /**
     * @return array{success: bool, total: int, inserted?: int, error_count?: int, errors?: array<int, array{row: int, nim: string, nama: string, messages: array<int, string>}>}
     *
     * @throws MahasiswaImportException untuk masalah level-file
     */
    public function import(UploadedFile $file): array
    {
        $rows = $this->readRows($file);

        $errors = $this->validateRows($rows);

        if ($errors !== []) {
            return [
                'success' => false,
                'total' => count($rows),
                'error_count' => count($errors),
                'errors' => array_slice($errors, 0, self::MAX_DISPLAYED_ERRORS),
            ];
        }

        $this->insertRows($rows);

        return [
            'success' => true,
            'total' => count($rows),
            'inserted' => count($rows),
        ];
    }

    /**
     * Baca file Excel menjadi daftar baris ter-normalisasi.
     *
     * @return array<int, array<string, string>> [nomor baris Excel => [kolom db => nilai]]
     */
    private function readRows(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $readerType = strtolower($file->getClientOriginalExtension()) === 'xls' ? 'Xls' : 'Xlsx';

        try {
            $reader = IOFactory::createReader($readerType);

            // Isi file harus benar-benar sesuai ekstensinya (bukan sekadar file yang di-rename).
            if (! $reader->canRead($path)) {
                throw new MahasiswaImportException(
                    'Isi file bukan format Excel yang valid. Gunakan file .xlsx atau .xls, atau unduh template yang disediakan.'
                );
            }

            $reader->setReadDataOnly(true);   // abaikan style/gambar -> lebih hemat memori
            $reader->setReadEmptyCells(false); // sel kosong (mis. hanya berformat) tidak dibaca

            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getSheetByName(self::SHEET_NAME) ?? $spreadsheet->getSheet(0);

            if ($sheet->getHighestRow() > self::MAX_SHEET_ROWS) {
                throw new MahasiswaImportException(
                    'File terlalu besar. Maksimal ' . self::MAX_ROWS . ' baris data per file; pecah menjadi beberapa file.'
                );
            }

            // Nilai mentah (tanpa format tampilan). Indeks 0 = baris 1 Excel.
            $matrix = $sheet->toArray(null, true, false, false);

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        } catch (MahasiswaImportException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::warning('Import mahasiswa: file Excel tidak dapat dibaca.', ['exception' => $e->getMessage()]);

            throw new MahasiswaImportException(
                'File Excel tidak dapat dibaca. Pastikan file tidak rusak dan tidak diproteksi password.',
                0,
                $e
            );
        }

        $columnMap = $this->mapHeaders(array_shift($matrix) ?? []);

        $rows = [];
        foreach ($matrix as $offset => $cells) {
            $excelRow = $offset + 2; // baris 1 = judul kolom

            $record = [];
            $isBlank = true;
            foreach ($columnMap as $columnIndex => $field) {
                $value = $this->cellToString($cells[$columnIndex] ?? null);
                $isBlank = $isBlank && $value === '';
                $record[$field] = $value;
            }

            if ($isBlank) {
                continue; // lewati baris kosong
            }

            $rows[$excelRow] = MahasiswaRequest::normalize($record);
        }

        if ($rows === []) {
            throw new MahasiswaImportException(
                'File tidak berisi data mahasiswa. Isi data mulai dari baris ke-2, di bawah judul kolom.'
            );
        }

        if (count($rows) > self::MAX_ROWS) {
            throw new MahasiswaImportException(
                'Jumlah data (' . count($rows) . ' baris) melebihi batas ' . self::MAX_ROWS . ' baris per file. Pecah menjadi beberapa file.'
            );
        }

        return $rows;
    }

    /**
     * Petakan judul kolom pada baris pertama ke nama kolom database.
     * Urutan kolom di file bebas; yang penting kelima judul wajib ada.
     *
     * @return array<int, string> [indeks kolom => nama kolom db]
     */
    private function mapHeaders(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $index => $cell) {
            $key = preg_replace('/[^a-z0-9]/', '', mb_strtolower($this->cellToString($cell))) ?? '';
            $field = self::HEADER_ALIASES[$key] ?? null;

            if ($field !== null && ! in_array($field, $map, true)) {
                $map[$index] = $field;
            }
        }

        $missing = array_diff(array_values(self::COLUMNS), array_values($map));

        if ($missing !== []) {
            $labels = array_flip(self::COLUMNS);

            throw new MahasiswaImportException(
                'Kolom berikut tidak ditemukan pada baris pertama: '
                . implode(', ', array_map(fn ($field) => $labels[$field], $missing))
                . '. Gunakan template Excel yang disediakan (tombol Download Template Excel).'
            );
        }

        return $map;
    }

    /**
     * Validasi semua baris. Mengembalikan daftar baris bermasalah (kosong = semua valid).
     *
     * @param  array<int, array<string, string>>  $rows
     * @return array<int, array{row: int, nim: string, nama: string, messages: array<int, string>}>
     */
    private function validateRows(array $rows): array
    {
        $rules = MahasiswaRequest::baseRules();
        $messages = MahasiswaRequest::errorMessages();
        $attributes = MahasiswaRequest::attributeNames();

        // Cek duplikat ke database sekali jalan (bukan satu query per baris).
        $existingNim = $this->existingValues('nim', array_column($rows, 'nim'));
        $existingEmail = $this->existingValues('email', array_column($rows, 'email'));

        $seenNim = [];
        $seenEmail = [];
        $errors = [];

        foreach ($rows as $rowNumber => $data) {
            $problems = [];

            // 1. Data wajib, format NIM, format email, nomor WhatsApp, panjang karakter.
            $validator = Validator::make($data, $rules, $messages, $attributes);
            if ($validator->fails()) {
                $problems = $validator->errors()->all();
            }

            // 2. NIM duplikat (di dalam file & di database).
            if ($data['nim'] !== '') {
                $key = mb_strtolower($data['nim']);

                if (isset($seenNim[$key])) {
                    $problems[] = "NIM duplikat: sama dengan NIM pada baris {$seenNim[$key]} di file.";
                } else {
                    $seenNim[$key] = $rowNumber;
                }

                if (isset($existingNim[$key])) {
                    $problems[] = 'NIM sudah terdaftar di database.';
                }
            }

            // 3. Email duplikat (kolom email juga unik di database).
            if ($data['email'] !== '') {
                $key = mb_strtolower($data['email']);

                if (isset($seenEmail[$key])) {
                    $problems[] = "Email duplikat: sama dengan email pada baris {$seenEmail[$key]} di file.";
                } else {
                    $seenEmail[$key] = $rowNumber;
                }

                if (isset($existingEmail[$key])) {
                    $problems[] = 'Email sudah terdaftar di database.';
                }
            }

            if ($problems !== []) {
                $errors[] = [
                    'row' => $rowNumber,
                    'nim' => (string) $data['nim'],
                    'nama' => (string) $data['nama'],
                    'messages' => $problems,
                ];
            }
        }

        return $errors;
    }

    /**
     * Ambil nilai kolom (nim / email) dari daftar yang SUDAH ada di database.
     *
     * @return array<string, true> kunci = nilai huruf kecil
     */
    private function existingValues(string $column, array $values): array
    {
        $values = array_values(array_unique(array_filter(
            $values,
            fn ($value) => is_string($value) && $value !== ''
        )));

        $found = [];
        foreach (array_chunk($values, 500) as $chunk) {
            foreach (Mahasiswa::whereIn($column, $chunk)->pluck($column) as $value) {
                $found[mb_strtolower((string) $value)] = true;
            }
        }

        return $found;
    }

    /**
     * Simpan seluruh baris dalam satu transaksi: semua masuk, atau tidak ada sama sekali.
     *
     * @param  array<int, array<string, string>>  $rows
     */
    private function insertRows(array $rows): void
    {
        $now = now();

        $payload = array_map(
            fn (array $data) => $data + ['created_at' => $now, 'updated_at' => $now],
            array_values($rows)
        );

        try {
            DB::transaction(function () use ($payload) {
                foreach (array_chunk($payload, self::INSERT_CHUNK) as $chunk) {
                    Mahasiswa::insert($chunk);
                }
            });
        } catch (QueryException $e) {
            // Transaksi otomatis di-rollback. Kasus tipikal: ada pengguna lain yang menambahkan
            // NIM/email yang sama tepat di antara proses validasi dan penyimpanan.
            Log::error('Import mahasiswa gagal disimpan.', ['exception' => $e->getMessage()]);

            throw new MahasiswaImportException(
                'Data gagal disimpan (kemungkinan ada NIM/email yang baru saja ditambahkan pengguna lain). '
                . 'Tidak ada data yang tersimpan. Silakan coba import ulang.',
                0,
                $e
            );
        }
    }

    /**
     * Ubah nilai sel Excel menjadi string bersih.
     * Angka utuh (mis. NIM yang tersimpan sebagai angka) tidak boleh menjadi "2.2101E+9".
     */
    private function cellToString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            return floor($value) === $value && abs($value) < 1e15
                ? sprintf('%.0f', $value)
                : (string) $value;
        }

        // string, atau objek RichText (punya __toString)
        return trim((string) $value);
    }
}
