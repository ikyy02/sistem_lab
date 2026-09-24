<?php

namespace App\Services;

use App\Http\Requests\MahasiswaRequest;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Membuat template Excel untuk import mahasiswa.
 *
 * Judul kolom diambil dari MahasiswaImportService::COLUMNS, sehingga template
 * dan proses import selalu sinkron (satu sumber kebenaran).
 *
 * Isi file:
 *  - Sheet "Data Mahasiswa": hanya judul kolom. Sengaja TANPA baris contoh, agar contoh
 *    tidak ikut ter-import bila pengguna lupa menghapusnya.
 *  - Sheet "Petunjuk": aturan pengisian + contoh baris. Tidak dibaca oleh proses import.
 */
class MahasiswaTemplateService
{
    private const HEADER_COLOR = '16A34A'; // hijau utama SILAB

    public function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $this->buildDataSheet($spreadsheet);
        $this->buildGuideSheet($spreadsheet);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function buildDataSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(MahasiswaImportService::SHEET_NAME);

        $headers = array_keys(MahasiswaImportService::COLUMNS);
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers)); // E

        $sheet->fromArray([$headers], null, 'A1');

        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::HEADER_COLOR]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->freezePane('A2');

        $widths = ['A' => 18, 'B' => 32, 'C' => 30, 'D' => 20, 'E' => 38];
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // NIM (A) dan No WhatsApp (D) diformat sebagai TEKS supaya Excel tidak menghapus
        // angka 0 di depan (081234... -> 81234...) atau mengubahnya ke notasi ilmiah.
        $lastRow = MahasiswaImportService::MAX_ROWS + 1;
        foreach (['A', 'D'] as $column) {
            $sheet->getStyle("{$column}2:{$column}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_TEXT);
        }
    }

    private function buildGuideSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Petunjuk');

        $domain = MahasiswaRequest::EMAIL_DOMAIN;
        $maxRows = MahasiswaImportService::MAX_ROWS;

        $rows = [
            ['Petunjuk pengisian template import data mahasiswa'],
            [],
            ['Kolom', 'Wajib', 'Aturan', 'Contoh'],
            ['NIM', 'Ya', 'Huruf/angka tanpa spasi, maksimal 20 karakter. Tidak boleh sama dengan NIM lain.', '2210101001'],
            ['Nama', 'Ya', 'Nama lengkap mahasiswa, maksimal 255 karakter.', 'Budi Santoso'],
            ['Program Studi', 'Ya', 'Nama program studi, maksimal 255 karakter.', 'Teknik Informatika'],
            ['No WhatsApp', 'Ya', 'Diawali 08, 62, atau +62 (10-14 digit). Contoh: 081234567890.', '081234567890'],
            ['Email', 'Ya', "Email institusi berformat nama@{$domain}. Tidak boleh sama dengan email lain.", "budi.santoso@{$domain}"],
            [],
            ['Catatan'],
            ['1. Isi data pada sheet "' . MahasiswaImportService::SHEET_NAME . '" mulai dari baris ke-2. Jangan mengubah atau menghapus judul kolom di baris pertama.'],
            ['2. Baris contoh di sheet ini tidak ikut diimpor.'],
            ["3. Maksimal {$maxRows} baris data per file."],
            ['4. Jika ada satu saja baris yang tidak valid, seluruh data tidak disimpan. Sistem akan menampilkan baris yang bermasalah beserta alasannya; perbaiki lalu unggah ulang.'],
            ['5. Kolom NIM dan No WhatsApp sudah berformat teks agar angka 0 di depan tidak hilang.'],
        ];

        // Ditulis sebagai teks eksplisit: nilai seperti "2210101001" tidak boleh berubah menjadi angka.
        foreach ($rows as $rowIndex => $cells) {
            foreach ($cells as $columnIndex => $value) {
                $coordinate = Coordinate::stringFromColumnIndex($columnIndex + 1) . ($rowIndex + 1);
                $sheet->setCellValueExplicit($coordinate, (string) $value, DataType::TYPE_STRING);
            }
        }

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A10')->getFont()->setBold(true);
        $sheet->getStyle('A3:D3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::HEADER_COLOR]],
        ]);

        foreach (['A' => 18, 'B' => 8, 'C' => 70, 'D' => 36] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        $sheet->getStyle('A3:D8')->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
    }
}
