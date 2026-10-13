<?php

namespace App\Services;

use App\Models\AlatBahan;
use App\Models\Ruangan;
use App\Models\Satuan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use ZipArchive;

/**
 * Import & template untuk Alat, Bahan, Ruangan (.xlsx atau .csv).
 *
 * - Semua baris divalidasi dulu; bila ada satu saja bermasalah, tidak ada data yang disimpan.
 * - Data lama tidak diubah: nama yang sudah ada pada kategori yang sama ditolak, bukan ditimpa.
 * - Parser native: tanpa dependensi eksternal. .xlsx via OOXML, .csv via native parsing.
 */
class KatalogImportService
{
    public const MAX_ROWS = 2000;
    public const MAX_ERRORS = 100;
    public const MAX_CELL_LEN = 5000;

    /** Judul kolom template => kolom database. Gambar tidak dapat diimpor (diunggah lewat Edit). */
    public static function columns(string $kategori): array
    {
        return $kategori === 'ruangan'
            ? ['Nama' => 'nama_ruangan', 'Keterangan' => 'keterangan']
            : ['Nama' => 'nama', 'Satuan' => 'satuan', 'Stok' => 'stok', 'Harga' => 'harga', 'Ruangan' => 'ruangan', 'Keterangan' => 'keterangan'];
    }

    /**
     * Buat file template CSV atau XLSX untuk diunduh.
     * Karena kita tidak pakai PhpOffice, untuk XLSX kita buat CSV dengan content-type xlsx.
     * @return array{0: string, 1: string} [content, filename]
     */
    public function template(string $kategori, string $format): array
    {
        $headers = array_keys(self::columns($kategori));
        $guides = [];
        
        if ($kategori === 'ruangan') {
            $guides[] = 'Isi data mulai baris ke-2. Jangan ubah judul kolom.';
            $guides[] = 'Nama wajib diisi, maksimal 50 karakter. Nama harus unik.';
            $guides[] = 'Keterangan boleh dikosongkan, maksimal 2000 karakter.';
        } else {
            $guides[] = 'Isi data mulai baris ke-2. Jangan ubah judul kolom.';
            $listSatuan = Satuan::orderBy('nama_satuan')->pluck('nama_satuan')->implode(', ') ?: '(belum ada satuan)';
            $listRuangan = Ruangan::orderBy('nama_ruangan')->pluck('nama_ruangan')->implode(', ') ?: '(belum ada ruangan)';
            $guides[] = 'Satuan: ' . $listSatuan . '.';
            $guides[] = 'Ruangan: ' . $listRuangan . '.';
            $guides[] = 'Stok: angka bulat 0 atau lebih.';
            $guides[] = 'Harga: untuk 1 satuan, > 0 (maks. 2 desimal).';
            $guides[] = 'Gambar: unggah lewat Edit setelah import.';
        }

        $filename = 'template-' . $kategori . '.' . ($format === 'csv' ? 'csv' : 'xlsx');

        $csv = $this->arrayToCsv([$headers]);
        
        return [$csv, $filename];
    }

    /**
     * Validasi + impor. Lempar RuntimeException untuk error file;
     * kembalikan array report untuk error per-baris.
     *
     * @return array{success: bool, total: int, inserted?: int, error_count?: int, errors?: array}
     */
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
            throw new RuntimeException('Judul kolom tidak sesuai template. Kolom tidak ditemukan: ' . implode(', ', array_map(fn ($c) => $labels[$c], $missing)) . '.');
        }
        
        $room = $kategori === 'ruangan';
        $satuan = Satuan::pluck('id_satuan', 'nama_satuan')->mapWithKeys(fn ($id, $n) => [mb_strtolower($n) => $id])->all();
        $ruangan = Ruangan::pluck('id_ruangan', 'nama_ruangan')->mapWithKeys(fn ($id, $n) => [mb_strtolower($n) => $id])->all();
        $existing = array_flip(array_map(fn ($n) => mb_strtolower(trim($n)), $room ? Ruangan::pluck('nama_ruangan')->all() : AlatBahan::pluck('nama')->all()));
        
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
            
            $nama = preg_replace('/\s+/u', ' ', $get($room ? 'nama_ruangan' : 'nama')) ?? '';
            $msg = [];
            
            if ($nama === '' || mb_strlen($nama) > 50) {
                $msg[] = 'Nama wajib diisi (maksimal 50 karakter).';
            } elseif (isset($existing[mb_strtolower($nama)])) {
                $msg[] = 'Nama sudah terdaftar.';
            } elseif (isset($seen[mb_strtolower($nama)])) {
                $msg[] = 'Nama duplikat di file (baris ' . $seen[mb_strtolower($nama)] . ').';
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
                    $msg[] = 'Satuan "' . $get('satuan') . '" tidak ada.';
                }
                
                $idRuangan = $ruangan[mb_strtolower($get('ruangan'))] ?? null;
                if (! $idRuangan) {
                    $msg[] = 'Ruangan "' . $get('ruangan') . '" tidak ada.';
                }
                
                $record += ['id_satuan' => $idSatuan, 'stok' => (int) $stok, 'harga' => $harga, 'id_ruangan' => $idRuangan, 'gambar' => ''];
            }
            
            if ($msg) {
                $errors[] = ['row' => $line, 'nama' => $nama, 'messages' => $msg];
                continue;
            }
            
            $seen[mb_strtolower($nama)] = $line;
            $keterangan = $get('keterangan');
            $record['keterangan'] = $keterangan !== '' ? $keterangan : null;
            $valid[] = $record;
        }
        
        if ($total === 0) {
            throw new RuntimeException('File tidak berisi data. Isi data mulai baris ke-2.');
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

    /**
     * Baca file .xlsx / .csv menjadi array 2D.
     * @return array<int, array<int, string>>
     */
    private function read(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['xlsx', 'xls', 'csv', 'txt'], true)) {
            throw new RuntimeException('Format file harus .xlsx, .xls, atau .csv.');
        }
        
        $path = $file->getRealPath();
        if ($path === false || ! is_readable($path)) {
            throw new RuntimeException('File tidak dapat dibaca.');
        }

        if ($ext === 'xls') {
            throw new RuntimeException('Format .xls tidak didukung. Simpan ulang sebagai .xlsx atau .csv.');
        }

        try {
            if ($ext === 'csv' || $ext === 'txt') {
                return $this->readCsv($path);
            }
            return $this->readXlsx($path);
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new RuntimeException('File tidak dapat dibaca. Pastikan file Excel/CSV valid.');
        }
    }

    /**
     * Parser CSV: deteksi delimiter, handle quoted fields.
     * @return array<int, array<int, string>>
     */
    private function readCsv(string $path): array
    {
        $raw = (string) file_get_contents($path);
        if (substr($raw, 0, 3) === "\xEF\xBB\xBF") {
            $raw = substr($raw, 3);
        }
        
        $lines = array_filter(explode("\n", $raw), fn ($l) => trim($l) !== '');
        
        if (empty($lines)) {
            return [];
        }
        
        $firstLine = reset($lines);
        $delimiter = substr_count($firstLine ?? '', ';') > substr_count($firstLine ?? '', ',') ? ';' : ',';
        
        $data = [];
        foreach ($lines as $line) {
            $line = rtrim($line, "\r");
            $row = str_getcsv($line, $delimiter, '"');
            if (is_array($row)) {
                $data[] = array_map(fn ($v) => trim((string) $v), $row);
            }
        }

        return array_slice($data, 0, self::MAX_ROWS + 50);
    }

    /**
     * Parser OOXML minimal: baca sharedStrings + sheet1 dari .xlsx.
     * @return array<int, array<int, string>>
     */
    private function readXlsx(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi ZIP belum aktif.');
        }
        
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('File bukan .xlsx yang valid.');
        }

        $shared = [];
        if (($idx = $zip->locateName('xl/sharedStrings.xml')) !== false) {
            $xml = (string) $zip->getFromIndex($idx);
            $shared = $this->parseSharedStrings($xml);
        }

        $sheetXml = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_starts_with($name, 'xl/worksheets/sheet') && str_ends_with($name, '.xml')) {
                $sheetXml = (string) $zip->getFromIndex($i);
                break;
            }
        }
        $zip->close();
        
        if ($sheetXml === null) {
            throw new RuntimeException('Sheet tidak ditemukan.');
        }

        return $this->parseSheet($sheetXml, $shared);
    }

    /**
     * Parse sharedStrings.xml.
     * @return array<int, string>
     */
    private function parseSharedStrings(string $xml): array
    {
        $items = [];
        $prev = libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml);
        libxml_use_internal_errors($prev);
        
        if ($doc === false) {
            return $items;
        }
        
        foreach ($doc->si as $si) {
            $text = '';
            if (isset($si->t)) {
                $text = (string) $si->t;
            } elseif (isset($si->r)) {
                foreach ($si->r as $r) {
                    $text .= (string) ($r->t ?? '');
                }
            }
            $items[] = $text;
        }

        return $items;
    }

    /**
     * Parse sheet1.xml.
     * @param array<int, string> $shared
     * @return array<int, array<int, string>>
     */
    private function parseSheet(string $xml, array $shared): array
    {
        $prev = libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml);
        libxml_use_internal_errors($prev);
        
        if ($doc === false) {
            throw new RuntimeException('Sheet XML tidak valid.');
        }
        
        $rows = [];
        foreach ($doc->sheetData->row as $row) {
            $rowData = [];
            $maxCol = 0;
            
            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                $colIdx = $this->columnIndex($ref);
                $type = (string) $c['t'];
                $value = '';
                
                if (isset($c->v)) {
                    $raw = (string) $c->v;
                    if ($type === 's') {
                        $value = $shared[(int) $raw] ?? '';
                    } elseif ($type === 'b') {
                        $value = $raw === '1' ? 'TRUE' : 'FALSE';
                    } else {
                        $value = $raw;
                    }
                } elseif (isset($c->is->t)) {
                    $value = (string) $c->is->t;
                }
                
                if (mb_strlen($value) > self::MAX_CELL_LEN) {
                    $value = mb_substr($value, 0, self::MAX_CELL_LEN);
                }
                
                $rowData[$colIdx] = $value;
                if ($colIdx > $maxCol) {
                    $maxCol = $colIdx;
                }
            }
            
            $line = [];
            for ($i = 0; $i <= $maxCol; $i++) {
                $line[] = $rowData[$i] ?? '';
            }
            $rows[] = $line;
        }

        return $rows;
    }

    /**
     * Konversi referensi kolom Excel (A1, B2, Z99) ke index 0-based.
     */
    private function columnIndex(string $cellRef): int
    {
        $letters = '';
        for ($i = 0, $n = strlen($cellRef); $i < $n; $i++) {
            $ch = $cellRef[$i];
            if (($ch >= 'A' && $ch <= 'Z') || ($ch >= 'a' && $ch <= 'z')) {
                $letters .= strtoupper($ch);
            } else {
                break;
            }
        }
        
        $idx = 0;
        for ($i = 0, $n = strlen($letters); $i < $n; $i++) {
            $idx = $idx * 26 + (ord($letters[$i]) - 64);
        }

        return $idx - 1;
    }

    /**
     * Helper: array ke CSV string.
     */
    private function arrayToCsv(array $rows): string
    {
        $output = '';
        foreach ($rows as $row) {
            $output .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\n";
        }
        return $output;
    }
}
