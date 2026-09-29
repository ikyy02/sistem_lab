<?php

namespace App\Services;

use App\Models\AlatBahan;
use App\Models\Satuan;
use App\Support\Options;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Import & template untuk Alat, Bahan, Ruangan (Excel .xlsx/.xls atau CSV).
 * Aturan: semua baris divalidasi dulu; bila ada satu saja bermasalah, tidak ada data yang disimpan.
 * Data lama tidak diubah: nama yang sudah ada pada kategori yang sama ditolak, bukan ditimpa.
 */
class InventarisImportService
{
    public const MAX_ROWS = 2000;
    public const MAX_ERRORS = 100;

    /** Judul kolom template => kolom database. */
    public static function columns(string $kategori): array
    {
        return $kategori === 'ruangan'
            ? ['Nama' => 'nama', 'Kapasitas' => 'stok', 'Kondisi' => 'kondisi', 'Keterangan' => 'keterangan']
            : ['Nama' => 'nama', 'Satuan' => 'satuan', 'Stok' => 'stok', 'Kondisi' => 'kondisi', 'Keterangan' => 'keterangan'];
    }

    public function template(string $kategori, string $format): array
    {
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet()->setTitle('Data');
        $sheet->fromArray([array_keys(self::columns($kategori))], null, 'A1');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        foreach (['A' => 30, 'B' => 16, 'C' => 12, 'D' => 16, 'E' => 50] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        if ($format === 'csv') {
            $writer = (new CsvWriter($book))->setUseBOM(true);
            return [$writer, 'template-' . $kategori . '.csv'];
        }

        $guide = $book->createSheet()->setTitle('Petunjuk');
        $rows = [['Petunjuk pengisian'], ['Isi data pada sheet "Data" mulai baris ke-2. Jangan ubah judul kolom.'],
            ['Kondisi harus salah satu dari: ' . implode(', ', Options::KONDISI[$kategori])]];
        if ($kategori === 'ruangan') {
            $rows[] = ['Kapasitas: angka bulat (jumlah orang).'];
        } else {
            $rows[] = ['Satuan harus salah satu satuan yang sudah ada: ' . (Satuan::orderBy('nama')->pluck('nama')->implode(', ') ?: '(belum ada satuan)')];
            $rows[] = ['Stok: angka bulat 0 atau lebih.'];
        }
        $rows[] = ['Keterangan: tuliskan juga lokasi penyimpanan. Contoh: Disimpan di Laboratorium Komputer 1, ruangan laboran.'];
        $guide->fromArray($rows, null, 'A1');
        $guide->getStyle('A1')->getFont()->setBold(true);
        $guide->getColumnDimension('A')->setWidth(110);
        $book->setActiveSheetIndex(0);

        return [new Xlsx($book), 'template-' . $kategori . '.xlsx'];
    }

    /** @return array{success: bool, total: int, inserted?: int, error_count?: int, errors?: array} */
    public function import(UploadedFile $file, string $kategori): array
    {
        $rows = $this->read($file);
        $map = self::columns($kategori);

        $header = array_shift($rows) ?? [];
        $index = [];
        foreach ($header as $i => $h) {
            $key = preg_replace('/[^a-z]/', '', mb_strtolower(trim((string) $h)));
            foreach ($map as $title => $col) {
                if ($key === mb_strtolower($title)) {
                    $index[$col] = $i;
                }
            }
        }
        $missing = array_diff(array_keys($map), array_map(fn ($c) => array_search($c, $map), array_keys($index)));
        if ($missing) {
            throw new \RuntimeException('Judul kolom tidak sesuai template. Kolom tidak ditemukan: ' . implode(', ', $missing) . '.');
        }

        $satuan = Satuan::pluck('nama')->mapWithKeys(fn ($n) => [mb_strtolower($n) => $n])->all();
        $kondisi = collect(Options::KONDISI[$kategori])->mapWithKeys(fn ($n) => [mb_strtolower($n) => $n])->all();
        $existing = AlatBahan::where('jenis', $kategori)->pluck('nama')->map(fn ($n) => mb_strtolower(trim($n)))->flip()->all();

        $valid = [];
        $errors = [];
        $total = 0;
        $seen = [];

        foreach ($rows as $n => $row) {
            $line = $n + 2;
            $get = fn ($col) => trim((string) ($row[$index[$col]] ?? ''));
            if (! array_filter($row, fn ($v) => trim((string) $v) !== '')) {
                continue;
            }
            if (++$total > self::MAX_ROWS) {
                throw new \RuntimeException('Jumlah baris melebihi batas ' . self::MAX_ROWS . '.');
            }

            $nama = preg_replace('/\s+/u', ' ', $get('nama'));
            $msg = [];
            if ($nama === '' || mb_strlen($nama) > 255) {
                $msg[] = 'Nama wajib diisi (maksimal 255 karakter).';
            } elseif (isset($existing[mb_strtolower($nama)])) {
                $msg[] = 'Nama sudah terdaftar.';
            } elseif (isset($seen[mb_strtolower($nama)])) {
                $msg[] = 'Nama duplikat di dalam file (baris ' . $seen[mb_strtolower($nama)] . ').';
            }

            $stok = $get('stok');
            if (! preg_match('/^\d{1,7}$/', $stok) || ($kategori === 'ruangan' && (int) $stok < 1)) {
                $msg[] = ($kategori === 'ruangan' ? 'Kapasitas' : 'Stok') . ' harus berupa angka bulat' . ($kategori === 'ruangan' ? ' minimal 1.' : ' 0 atau lebih.');
            }

            $k = $kondisi[mb_strtolower($get('kondisi'))] ?? null;
            if (! $k) {
                $msg[] = 'Kondisi harus salah satu dari: ' . implode(', ', Options::KONDISI[$kategori]) . '.';
            }

            $sat = '';
            if ($kategori !== 'ruangan') {
                $sat = $satuan[mb_strtolower($get('satuan'))] ?? null;
                if (! $sat) {
                    $msg[] = 'Satuan "' . $get('satuan') . '" belum ada. Tambahkan lewat menu Satuan atau gunakan satuan yang tersedia.';
                }
            }

            if ($msg) {
                $errors[] = ['row' => $line, 'nama' => $nama, 'messages' => $msg];
                continue;
            }

            $seen[mb_strtolower($nama)] = $line;
            $valid[] = [
                'nama' => $nama, 'jenis' => $kategori, 'satuan' => $sat, 'stok' => (int) $stok,
                'kondisi' => $k, 'keterangan' => $get('keterangan') ?: null,
                'created_at' => now(), 'updated_at' => now(),
            ];
        }

        if ($total === 0) {
            throw new \RuntimeException('File tidak berisi data. Isi data mulai baris ke-2.');
        }
        if ($errors) {
            return ['success' => false, 'total' => $total, 'error_count' => count($errors), 'errors' => array_slice($errors, 0, self::MAX_ERRORS)];
        }

        DB::transaction(function () use ($valid) {
            foreach (array_chunk($valid, 100) as $chunk) {
                AlatBahan::insert($chunk);
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
                $reader = IOFactory::createReaderForFile($file->getRealPath());
                $reader->setReadDataOnly(true);
            }
            $sheet = $reader->load($file->getRealPath())->getSheet(0);
            $data = $sheet->toArray(null, true, false, false);
        } catch (\Throwable $e) {
            throw new \RuntimeException('File tidak dapat dibaca. Pastikan file Excel/CSV valid.');
        }

        return array_slice($data, 0, self::MAX_ROWS + 50);
    }
}
