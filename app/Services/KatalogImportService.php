<?php

namespace App\Services;

use App\Models\AlatBahan;
use App\Models\Ruangan;
use App\Models\Satuan;
use App\Support\ExcelFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

/**
 * Import & template untuk Alat, Bahan, Ruangan (Excel .xlsx / .xls).
 *
 * - Semua baris divalidasi dulu; bila ada satu saja bermasalah, tidak ada data yang disimpan.
 * - Data lama tidak diubah: nama yang sudah ada pada kategori yang sama ditolak, bukan ditimpa.
 * - Pembacaan file lewat ExcelFile (hanya Excel).
 */
class KatalogImportService
{
    public const MAX_ROWS = 2000;
    public const MAX_ERRORS = 100;

    /** Judul kolom template => kolom database. Gambar tidak dapat diimpor (diunggah lewat Edit). */
    // Kolom template: Ruangan hanya Nama+Keterangan; Alat/Bahan juga Satuan, Stok, Harga, Ruangan.
    public static function columns(string $kategori): array
    {
        return $kategori === 'ruangan'
            ? ['Nama' => 'nama_ruangan', 'Keterangan' => 'keterangan']
            : ['Nama' => 'nama', 'Satuan' => 'satuan', 'Stok' => 'stok', 'Harga' => 'harga', 'Ruangan' => 'ruangan', 'Keterangan' => 'keterangan'];
    }

    /** Template Excel (.xlsx): sheet "Data" berisi judul kolom, sheet "Petunjuk" berisi aturan pengisian. */
    public function template(string $kategori): array
    {
        $headers = array_keys(self::columns($kategori));
        $guides = ['Petunjuk pengisian', 'Isi data pada sheet "Data" mulai baris ke-2. Jangan ubah judul kolom.'];

        if ($kategori === 'ruangan') {
            $guides[] = 'Nama wajib diisi, maksimal 50 karakter. Nama harus unik.';
            $guides[] = 'Keterangan boleh dikosongkan, maksimal 2000 karakter.';
        } else {
            $guides[] = 'Satuan: ' . (Satuan::orderBy('nama_satuan')->pluck('nama_satuan')->implode(', ') ?: '(belum ada satuan)') . '.';
            $guides[] = 'Ruangan: ' . (Ruangan::orderBy('nama_ruangan')->pluck('nama_ruangan')->implode(', ') ?: '(belum ada ruangan)') . '.';
            $guides[] = 'Stok: angka bulat 0 atau lebih.';
            $guides[] = 'Harga: untuk 1 satuan, > 0 (maks. 2 desimal).';
            $guides[] = 'Gambar: unggah lewat Edit setelah import.';
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

        return [new Xlsx($book), 'template-' . $kategori . '.xlsx'];
    }

    /**
     * Validasi + impor. Lempar RuntimeException untuk error file;
     * kembalikan array report untuk error per-baris.
     *
     * @return array{success: bool, total: int, inserted?: int, error_count?: int, errors?: array}
     */
    // Alur impor: validasi semua baris dulu, baru simpan dalam satu transaksi.
    public function import(UploadedFile $file, string $kategori): array
    {
        $rows = ExcelFile::rows($file, self::MAX_ROWS + 50);
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
        // Peta nama (huruf kecil) => id untuk mengubah teks Excel menjadi id_satuan/id_ruangan.
        $satuan = Satuan::pluck('id_satuan', 'nama_satuan')->mapWithKeys(fn ($id, $n) => [mb_strtolower($n) => $id])->all();
        $ruangan = Ruangan::pluck('id_ruangan', 'nama_ruangan')->mapWithKeys(fn ($id, $n) => [mb_strtolower($n) => $id])->all();
        // Nama yang sudah ada ditolak (data lama tidak ditimpa).
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
                
                // Gambar dikosongkan; diunggah lewat tombol Edit setelah impor.
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
            // Insert per 100 baris agar query tidak terlalu besar.
            foreach (array_chunk($valid, 100) as $chunk) {
                $room ? Ruangan::insert($chunk) : AlatBahan::insert($chunk);
            }
        });

        return ['success' => true, 'total' => $total, 'inserted' => count($valid)];
    }
}
