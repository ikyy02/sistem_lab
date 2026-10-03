<?php

namespace App\Services;

use App\Models\AlatBahan;
use App\Models\Ruangan;
use App\Models\Satuan;
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
class KatalogImportService
{
    public const MAX_ROWS = 2000;
    public const MAX_ERRORS = 100;

    /** Judul kolom template => kolom database. Gambar tidak dapat diimpor (diunggah lewat Edit). */
    public static function columns(string $kategori): array
    {
        return $kategori === 'ruangan'
            ? ['Nama' => 'nama_ruangan', 'Keterangan' => 'keterangan']
            : ['Nama' => 'nama', 'Satuan' => 'satuan', 'Stok' => 'stok', 'Harga' => 'harga', 'Ruangan' => 'ruangan', 'Keterangan' => 'keterangan'];
    }

    public function template(string $kategori, string $format): array
    {
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet()->setTitle('Data');
        $sheet->fromArray([array_keys(self::columns($kategori))], null, 'A1');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        foreach (['A' => 30, 'B' => 16, 'C' => 12, 'D' => 16, 'E' => 24, 'F' => 50] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }
        if ($format === 'csv') {
            $writer = (new CsvWriter($book))->setUseBOM(true);

            return [$writer, 'template-' . $kategori . '.csv'];
        }
        $guide = $book->createSheet()->setTitle('Petunjuk');
        $rows = [['Petunjuk pengisian'], ['Isi data pada sheet "Data" mulai baris ke-2. Jangan ubah judul kolom.']];
        if ($kategori !== 'ruangan') {
            $rows[] = ['Satuan harus salah satu satuan yang sudah ada: ' . (Satuan::orderBy('nama_satuan')->pluck('nama_satuan')->implode(', ') ?: '(belum ada satuan)')];
            $rows[] = ['Ruangan harus salah satu ruangan yang sudah ada: ' . (Ruangan::orderBy('nama_ruangan')->pluck('nama_ruangan')->implode(', ') ?: '(belum ada ruangan)')];
            $rows[] = ['Stok: angka bulat 0 atau lebih. Harga: harga untuk 1 satuan, lebih dari 0 (maksimal 2 angka di belakang koma).'];
            $rows[] = ['Gambar wajib: unggah gambar tiap data lewat tombol Edit setelah import.'];
        }
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
        $missing = array_diff(array_values($map), array_keys($index));
        if ($missing) {
            $labels = array_flip($map);
            throw new \RuntimeException('Judul kolom tidak sesuai template. Kolom tidak ditemukan: ' . implode(', ', array_map(fn ($c) => $labels[$c], $missing)) . '.');
        }
        $room = $kategori === 'ruangan';
        $satuan = Satuan::pluck('id_satuan', 'nama_satuan')->mapWithKeys(fn ($id, $n) => [mb_strtolower($n) => $id])->all();
        $ruangan = Ruangan::pluck('id_ruangan', 'nama_ruangan')->mapWithKeys(fn ($id, $n) => [mb_strtolower($n) => $id])->all();
        // Nama katalog unik global; nama ruangan unik pada tabel ruangans.
        $existing = array_flip(array_map(fn ($n) => mb_strtolower(trim($n)), $room ? Ruangan::pluck('nama_ruangan')->all() : AlatBahan::pluck('nama')->all()));
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
            $nama = preg_replace('/\s+/u', ' ', $get($room ? 'nama_ruangan' : 'nama'));
            $msg = [];
            if ($nama === '' || mb_strlen($nama) > 50) {
                $msg[] = 'Nama wajib diisi (maksimal 50 karakter).';
            } elseif (isset($existing[mb_strtolower($nama)])) {
                $msg[] = 'Nama sudah terdaftar.';
            } elseif (isset($seen[mb_strtolower($nama)])) {
                $msg[] = 'Nama duplikat di dalam file (baris ' . $seen[mb_strtolower($nama)] . ').';
            }
            $record = $room ? ['nama_ruangan' => $nama] : ['nama' => $nama, 'jenis' => $kategori];
            if (! $room) {
                $stok = $get('stok');
                if (! preg_match('/^\d{1,9}$/', $stok)) {
                    $msg[] = 'Stok harus berupa angka bulat 0 atau lebih.';
                }
                $harga = str_replace(',', '.', $get('harga'));
                if (! preg_match('/^\d{1,13}(\.\d{1,2})?$/', $harga) || (float) $harga <= 0) {
                    $msg[] = 'Harga harus lebih dari 0 (maksimal 2 angka di belakang koma).';
                }
                $idSatuan = $satuan[mb_strtolower($get('satuan'))] ?? null;
                if (! $idSatuan) {
                    $msg[] = 'Satuan "' . $get('satuan') . '" belum ada. Tambahkan lewat menu Satuan atau gunakan satuan yang tersedia.';
                }
                $idRuangan = $ruangan[mb_strtolower($get('ruangan'))] ?? null;
                if (! $idRuangan) {
                    $msg[] = 'Ruangan "' . $get('ruangan') . '" belum ada. Tambahkan lebih dulu di tab Ruangan.';
                }
                $record += ['id_satuan' => $idSatuan, 'stok' => (int) $stok, 'harga' => $harga, 'id_ruangan' => $idRuangan, 'gambar' => ''];
            }
            if ($msg) {
                $errors[] = ['row' => $line, 'nama' => $nama, 'messages' => $msg];
                continue;
            }
            $seen[mb_strtolower($nama)] = $line;
            $record['keterangan'] = $get('keterangan') ?: null;
            $valid[] = $record;
        }
        if ($total === 0) {
            throw new \RuntimeException('File tidak berisi data. Isi data mulai baris ke-2.');
        }
        if ($errors) {
            return ['success' => false, 'total' => $total, 'error_count' => count($errors), 'errors' => array_slice($errors, 0, self::MAX_ERRORS)];
        }
        DB::transaction(function () use ($valid, $room) {
            foreach (array_chunk($valid, 100) as $chunk) {
                $room ? Ruangan::insert($chunk) : AlatBahan::insert($chunk);
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
