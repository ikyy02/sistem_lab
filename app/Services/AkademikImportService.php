<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Support\ExcelFile;
use App\Support\Options;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

/**
 * Import & template Data Akademik: Mata Kuliah dan Kelas (Excel .xlsx/.xls).
 * Semua-atau-tidak-sama-sekali; data yang sudah ada ditolak, tidak ditimpa.
 * Aturan mengikuti DataAkademikController::divalidasi().
 */
class AkademikImportService
{
    public const MAX_ROWS = 2000;
    public const MAX_ERRORS = 100;

    /** Judul kolom template => kunci data. */
    // Mata Kuliah milik satu prodi: Staf Prodi otomatis prodinya sendiri (tanpa kolom Prodi); Laboran wajib mengisi kolom Prodi.
    public static function columns(string $tab, ?int $batasProdi = null): array
    {
        if ($tab === 'kelas') {
            return ['Nama Kelas' => 'nama_kelas', 'Prodi' => 'prodi'];
        }

        return ['Kode' => 'kode_mk', 'Mata Kuliah' => 'nama_mk', 'SKS' => 'sks', 'Semester' => 'semester']
            + ($batasProdi === null ? ['Prodi' => 'prodi'] : []);
    }

    public function template(string $tab, ?int $batasProdi = null): array
    {
        $headers = array_keys(self::columns($tab, $batasProdi));
        $guides = ['Petunjuk pengisian', 'Isi data pada sheet "Data" mulai baris ke-2. Jangan ubah judul kolom.'];
        if ($tab === 'kelas') {
            $guides[] = 'Nama Kelas maksimal 100 karakter; harus unik pada prodi yang sama.';
            $guides[] = 'Prodi harus salah satu dari: ' . (implode(', ', Options::prodi($batasProdi)) ?: '(belum ada prodi)') . '.';
        } else {
            $guides[] = 'Kode maksimal 15 karakter dan Mata Kuliah maksimal 100 karakter; keduanya harus unik pada prodi yang sama.';
            $guides[] = $batasProdi === null
                ? 'Prodi harus salah satu dari: ' . (implode(', ', Options::prodi()) ?: '(belum ada prodi)') . '.'
                : 'Mata kuliah otomatis masuk ke prodi Anda; tidak perlu kolom Prodi.';
            $guides[] = 'SKS: angka bulat 1 sampai 10.';
            $guides[] = 'Semester: ' . implode(' atau ', array_map('ucfirst', Jadwal::SEMESTER)) . '.';
        }

        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet()->setTitle('Data');
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:' . chr(64 + count($headers)) . '1')->getFont()->setBold(true);
        foreach ($headers as $i => $h) {
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth(max(16, min(40, mb_strlen($h) + 12)));
        }
        $guide = $book->createSheet()->setTitle('Petunjuk');
        $guide->fromArray(array_map(fn ($g) => [$g], $guides), null, 'A1');
        $guide->getStyle('A1')->getFont()->setBold(true);
        $guide->getColumnDimension('A')->setWidth(100);
        $book->setActiveSheetIndex(0);

        return [new Xlsx($book), 'template-' . $tab . '.xlsx'];
    }

    /** @return array{success: bool, total: int, inserted?: int, error_count?: int, errors?: array} */
    public function import(UploadedFile $file, string $tab, ?int $batasProdi = null): array
    {
        $kelas = $tab === 'kelas';
        $map = self::columns($tab, $batasProdi);
        $rows = ExcelFile::rows($file, self::MAX_ROWS + 50);
        $header = array_shift($rows) ?? [];

        $index = ExcelFile::matchHeaders($header, $map);

        $prodi = array_map('mb_strtolower', Options::prodi($batasProdi)); // Staf Prodi: hanya prodinya, prodi lain dilaporkan "tidak ada"
        $existing = [];
        if ($kelas) {
            foreach (Kelas::get(['nama_kelas', 'id_prodi']) as $k) {
                $existing[$k->id_prodi . '|' . mb_strtolower($k->nama_kelas)] = true;
            }
        } else {
            // Kode dan nama unik per prodi.
            foreach (MataKuliah::get(['kode_mk', 'nama_mk', 'id_prodi']) as $m) {
                $existing['k|' . $m->id_prodi . '|' . mb_strtolower($m->kode_mk)] = true;
                $existing['n|' . $m->id_prodi . '|' . mb_strtolower($m->nama_mk)] = true;
            }
        }

        $valid = [];
        $errors = [];
        $total = 0;
        $seen = [];

        foreach ($rows as $n => $row) {
            $line = $n + 2;
            $get = fn (string $col): string => trim((string) ($row[$index[$col]] ?? ''));
            if (! array_filter($row, fn ($v) => trim((string) $v) !== '')) {
                continue;
            }
            if (++$total > self::MAX_ROWS) {
                throw new RuntimeException('Jumlah baris melebihi batas ' . self::MAX_ROWS . '.');
            }

            $msg = [];
            if ($kelas) {
                $nama = preg_replace('/\s+/u', ' ', $get('nama_kelas')) ?? '';
                $idProdi = array_search(mb_strtolower($get('prodi')), $prodi, true);
                if ($nama === '' || mb_strlen($nama) > 100) {
                    $msg[] = 'Nama Kelas wajib diisi (maksimal 100 karakter).';
                }
                if ($idProdi === false) {
                    $msg[] = 'Prodi "' . $get('prodi') . '" tidak ada.';
                }
                $keys = [$idProdi . '|' . mb_strtolower($nama)];
                $record = ['nama_kelas' => $nama, 'id_prodi' => $idProdi];
            } else {
                $kode = strtoupper($get('kode_mk'));
                $nama = preg_replace('/\s+/u', ' ', $get('nama_mk')) ?? '';
                $sks = $get('sks');
                $sem = mb_strtolower($get('semester'));
                if ($kode === '' || mb_strlen($kode) > 15) {
                    $msg[] = 'Kode wajib diisi (maksimal 15 karakter).';
                }
                if ($nama === '' || mb_strlen($nama) > 100) {
                    $msg[] = 'Mata Kuliah wajib diisi (maksimal 100 karakter).';
                }
                if (! preg_match('/^\d{1,2}$/', $sks) || (int) $sks < 1 || (int) $sks > 10) {
                    $msg[] = 'SKS harus angka bulat 1 sampai 10.';
                }
                if (! in_array($sem, Jadwal::SEMESTER, true)) {
                    $msg[] = 'Semester hanya Ganjil atau Genap.';
                }
                // Staf Prodi: prodinya sendiri; Laboran: dari kolom Prodi (prodi tak dikenal dilaporkan).
                $idProdi = $batasProdi ?? array_search(mb_strtolower($get('prodi')), $prodi, true);
                if ($idProdi === false) {
                    $msg[] = 'Prodi "' . $get('prodi') . '" tidak ada.';
                }
                $keys = ['k|' . $idProdi . '|' . mb_strtolower($kode), 'n|' . $idProdi . '|' . mb_strtolower($nama)];
                $record = ['kode_mk' => $kode, 'nama_mk' => $nama, 'sks' => (int) $sks, 'semester' => $sem, 'id_prodi' => $idProdi];
            }

            if (! $msg) {
                foreach ($keys as $k) {
                    if (isset($existing[$k])) {
                        $msg[] = 'Data sudah terdaftar.';
                        break;
                    }
                    if (isset($seen[$k])) {
                        $msg[] = 'Duplikat di file (baris ' . $seen[$k] . ').';
                        break;
                    }
                }
            }

            if ($msg) {
                $errors[] = ['row' => $line, 'nama' => $nama, 'messages' => $msg];
                continue;
            }
            foreach ($keys as $k) {
                $seen[$k] = $line;
            }
            $valid[] = $record;
        }

        if ($total === 0) {
            throw new RuntimeException('File tidak berisi data. Isi data mulai baris ke-2.');
        }
        if ($errors) {
            return ['success' => false, 'total' => $total, 'error_count' => count($errors), 'errors' => array_slice($errors, 0, self::MAX_ERRORS)];
        }

        DB::transaction(function () use ($valid, $kelas) {
            foreach (array_chunk($valid, 100) as $chunk) {
                $kelas ? Kelas::insert($chunk) : MataKuliah::insert($chunk);
            }
        });

        return ['success' => true, 'total' => $total, 'inserted' => count($valid)];
    }
}
