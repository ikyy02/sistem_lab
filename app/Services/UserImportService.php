<?php

namespace App\Services;

use App\Http\Controllers\UserManagementController;
use App\Models\Akun;
use App\Support\Options;
use App\Support\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Import & template untuk Dosen, Staff Prodi, Laboran/Admin (Excel .xlsx/.xls atau CSV).
 * (Mahasiswa memakai MahasiswaImportService sendiri karena aturan email institusi & kelas-nya lebih khusus.)
 * Kolom mengikuti field kategori pada UserManagementController::config() + kolom Password.
 * Semua-atau-tidak-sama-sekali: satu baris bermasalah -> tidak ada data yang disimpan.
 */
class UserImportService
{
    public const MAX_ROWS = 2000;
    public const MAX_ERRORS = 100;

    /** Judul kolom template => nama kolom database, untuk kategori selain mahasiswa. */
    public static function columns(string $kategori): array
    {
        $map = ['Password' => 'password'];
        foreach (UserManagementController::config($kategori)['fields'] as $f) {
            $map = [$f['label'] => $f['name']] + $map;
        }

        return $map;
    }

    public function template(string $kategori, string $format): array
    {
        $columns = self::columns($kategori);
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet()->setTitle('Data');
        $sheet->fromArray([array_keys($columns)], null, 'A1');
        $sheet->getStyle('A1:' . chr(64 + count($columns)) . '1')->getFont()->setBold(true);
        foreach (array_keys($columns) as $i => $label) {
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth(max(16, min(40, mb_strlen($label) + 12)));
        }

        if ($format === 'csv') {
            $writer = (new CsvWriter($book))->setUseBOM(true);

            return [$writer, 'template-' . $kategori . '.csv'];
        }

        $guide = $book->createSheet()->setTitle('Petunjuk');
        $rows = [['Petunjuk pengisian'], ['Isi data pada sheet "Data" mulai baris ke-2. Jangan ubah judul kolom.'], ['Password minimal 4 karakter.']];
        if ($kategori === Role::DOSEN || $kategori === Role::STAFF) {
            $rows[] = ['Prodi harus salah satu dari: ' . implode(', ', Options::prodi())];
        }
        $guide->fromArray($rows, null, 'A1');
        $guide->getStyle('A1')->getFont()->setBold(true);
        $guide->getColumnDimension('A')->setWidth(100);
        $book->setActiveSheetIndex(0);

        return [new Xlsx($book), 'template-' . $kategori . '.xlsx'];
    }

    /** @return array{success: bool, total: int, inserted?: int, error_count?: int, errors?: array} */
    public function import(UploadedFile $file, string $kategori): array
    {
        $model = Role::MODELS[$kategori];
        $columns = self::columns($kategori);
        $rows = $this->read($file);

        $header = array_shift($rows) ?? [];
        $index = [];
        foreach ($header as $i => $h) {
            $key = preg_replace('/[^a-z]/', '', mb_strtolower(trim((string) $h)));
            foreach ($columns as $label => $col) {
                if ($key === preg_replace('/[^a-z]/', '', mb_strtolower($label))) {
                    $index[$col] = $i;
                }
            }
        }
        $missing = array_diff(array_values($columns), array_keys($index));
        if ($missing) {
            $labels = array_flip($columns);
            throw new \RuntimeException('Judul kolom tidak sesuai template. Kolom tidak ditemukan: ' . implode(', ', array_map(fn ($c) => $labels[$c], $missing)) . '.');
        }

        $existingEmail = [];
        foreach (Akun::pluck('email') as $e) {
            $existingEmail[mb_strtolower($e)] = true;
        }
        $existingUnique = [];
        $uniqueCol = (new $model)->getKeyName(); // nuptk_nidn / id_pegawai
        $keyLabel = array_column(UserManagementController::config($kategori)['fields'], 'label', 'name')[$uniqueCol];
        {
            foreach ($model::pluck($uniqueCol) as $v) {
                $existingUnique[mb_strtolower((string) $v)] = true;
            }
        }

        $valid = [];
        $errors = [];
        $total = 0;
        $seenEmail = [];
        $seenUnique = [];

        foreach ($rows as $n => $row) {
            $line = $n + 2;
            $get = fn ($col) => trim((string) ($row[$index[$col]] ?? ''));
            if (! array_filter($row, fn ($v) => trim((string) $v) !== '')) {
                continue;
            }
            if (++$total > self::MAX_ROWS) {
                throw new \RuntimeException('Jumlah baris melebihi batas ' . self::MAX_ROWS . '.');
            }

            $msg = [];
            $record = [];

            foreach (UserManagementController::config($kategori)['fields'] as $f) {
                $val = preg_replace('/\s+/u', ' ', $get($f['name']));
                if ($f['required'] && $val === '') {
                    $msg[] = $f['label'] . ' wajib diisi.';
                }
                if (($f['type'] ?? '') === 'select' && $val !== '') {
                    $id = array_search(mb_strtolower($val), array_map('mb_strtolower', $f['options']), true);
                    if ($id === false) {
                        $msg[] = $f['label'] . ' harus salah satu dari: ' . implode(', ', $f['options']) . '.';
                        $val = '';
                    } else {
                        $val = (string) $id;
                    }
                }
                if ($f['name'] === 'email' && $val !== '' && (! filter_var($val, FILTER_VALIDATE_EMAIL) || mb_strlen($val) > 50)) {
                    $msg[] = 'Format Email tidak valid (maksimal 50 karakter).';
                }
                if ($f['name'] === 'no_whatsapp' && $val !== '' && ! preg_match(UserManagementController::WA_REGEX, $val)) {
                    $msg[] = 'WhatsApp tidak valid. Gunakan format 08xxxxxxxxxx.';
                }
                $record[$f['name']] = $val !== '' ? $val : null;
            }

            $password = $get('password');
            if (mb_strlen($password) < 4) {
                $msg[] = 'Password minimal 4 karakter.';
            }

            $email = mb_strtolower((string) $record['email']);
            if ($email !== '') {
                if (isset($seenEmail[$email])) {
                    $msg[] = 'Email duplikat dengan baris ' . $seenEmail[$email] . ' di file.';
                } elseif (isset($existingEmail[$email])) {
                    $msg[] = 'Email sudah terdaftar di sistem.';
                } else {
                    $seenEmail[$email] = $line;
                }
            }

            if (! empty($record[$uniqueCol])) {
                $u = mb_strtolower((string) $record[$uniqueCol]);
                if (! preg_match($uniqueCol === 'nuptk_nidn' ? '/^[A-Za-z0-9]{1,30}$/' : '/^[A-Za-z0-9._\-]{1,30}$/', (string) $record[$uniqueCol])) {
                    $msg[] = $keyLabel . ' tidak valid (maksimal 30 karakter, tanpa spasi/simbol).';
                } elseif (isset($seenUnique[$u])) {
                    $msg[] = $keyLabel . ' duplikat dengan baris ' . $seenUnique[$u] . ' di file.';
                } elseif (isset($existingUnique[$u])) {
                    $msg[] = $keyLabel . ' sudah terdaftar.';
                } else {
                    $seenUnique[$u] = $line;
                }
            }

            if ($msg) {
                $errors[] = ['row' => $line, 'nama' => $record['nama'] ?? '', 'messages' => $msg];
                continue;
            }

            $valid[] = $record + ['password' => $password];
        }

        if ($total === 0) {
            throw new \RuntimeException('File tidak berisi data. Isi data mulai baris ke-2.');
        }
        if ($errors) {
            return ['success' => false, 'total' => $total, 'error_count' => count($errors), 'errors' => array_slice($errors, 0, self::MAX_ERRORS)];
        }

        $akun = app(AkunService::class);
        DB::transaction(function () use ($akun, $kategori, $valid) {
            foreach ($valid as $row) {
                $password = $row['password'];
                $email = mb_strtolower($row['email']);
                unset($row['password'], $row['email']);
                $akun->buat($kategori, $email, $password, $row);
            }
        });
        return ['success' => true, 'total' => $total, 'inserted' => count($valid)];
    }

    private function read(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['xlsx', 'xls', 'csv', 'txt'], true)) {
            throw new \RuntimeException('Format file harus .xlsx, .xls, atau .csv.');
        }

        try {
            if ($ext === 'csv' || $ext === 'txt') {
                $first = (string) strtok((string) file_get_contents($file->getRealPath(), false, null, 0, 4096), "\n");
                $delimiter = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';
                $reader = new Csv();
                $reader->setDelimiter($delimiter)->setInputEncoding('UTF-8');
            } else {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getRealPath());
                $reader->setReadDataOnly(true);
            }
            $data = $reader->load($file->getRealPath())->getSheet(0)->toArray(null, true, false, false);
        } catch (\Throwable $e) {
            throw new \RuntimeException('File tidak dapat dibaca. Pastikan file Excel/CSV valid.');
        }

        return array_slice($data, 0, self::MAX_ROWS + 50);
    }
}
