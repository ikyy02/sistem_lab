<?php

namespace App\Http\Controllers;

use App\Exceptions\MahasiswaImportException;
use App\Services\MahasiswaImportService;
use App\Services\MahasiswaTemplateService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Import & template Excel mahasiswa. CRUD mahasiswa kini ada di UserManagementController (Kelola Data User).
 */
class MahasiswaController extends Controller
{
    /**
     * Unduh template Excel untuk import mahasiswa.
     */
    public function template(MahasiswaTemplateService $template)
    {
        if ($missing = $this->spreadsheetLibraryMissing()) {
            return $missing;
        }

        $spreadsheet = $template->build();

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'template_import_mahasiswa.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import data mahasiswa dari file Excel.
     * Semua-atau-tidak-sama-sekali: bila ada satu baris bermasalah, tidak ada yang disimpan.
     */
    public function import(Request $request, MahasiswaImportService $importer)
    {
        $request->validate([
            'file' => ['required', 'file', 'extensions:xlsx,xls', 'max:2048'],
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.uploaded' => 'File gagal diunggah. Pastikan ukuran file tidak lebih dari 2 MB.',
            'file.file' => 'File tidak valid.',
            'file.extensions' => 'File harus berformat .xlsx atau .xls.',
            'file.max' => 'Ukuran file maksimal 2 MB.',
        ]);

        if ($missing = $this->spreadsheetLibraryMissing()) {
            return $missing;
        }

        try {
            $result = $importer->import($request->file('file'));
        } catch (MahasiswaImportException $e) {
            return redirect()
                ->route('kelola-user.index', ['kategori' => 'mahasiswa'])
                ->with('error', $e->getMessage());
        }

        if (! $result['success']) {
            return redirect()
                ->route('kelola-user.index', ['kategori' => 'mahasiswa'])
                ->with('import_report', $result);
        }

        return redirect()
            ->route('kelola-user.index', ['kategori' => 'mahasiswa'])
            ->with('success', "Import berhasil: {$result['inserted']} data mahasiswa ditambahkan.");
    }

    /**
     * Pastikan library PhpSpreadsheet sudah dipasang (composer require phpoffice/phpspreadsheet).
     * Bila belum, tampilkan pesan yang jelas alih-alih error 500.
     */
    private function spreadsheetLibraryMissing()
    {
        if (class_exists(IOFactory::class)) {
            return null;
        }

        return redirect()
            ->route('kelola-user.index', ['kategori' => 'mahasiswa'])
            ->with('error', 'Library Excel belum terpasang. Jalankan perintah: composer require phpoffice/phpspreadsheet');
    }
}
