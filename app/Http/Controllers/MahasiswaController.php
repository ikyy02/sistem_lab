<?php

namespace App\Http\Controllers;

use App\Exceptions\MahasiswaImportException;
use App\Services\MahasiswaImportService;
use App\Support\ExcelFile;
use App\Services\MahasiswaTemplateService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Import & template Excel mahasiswa. CRUD mahasiswa kini ada di UserManagementController (Kelola Data User).
 */
class MahasiswaController extends Controller
{
    /**
     * Unduh template Excel untuk import mahasiswa.
     */
    public function template(Request $request, MahasiswaTemplateService $template)
    {
        $spreadsheet = $template->build();

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'template-mahasiswa.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import data mahasiswa dari file Excel.
     * Semua-atau-tidak-sama-sekali: bila ada satu baris bermasalah, tidak ada yang disimpan.
     */
    // Impor semua-atau-tidak-sama-sekali: satu baris salah, tidak ada yang tersimpan.
    public function import(Request $request, MahasiswaImportService $importer)
    {
        $request->validate(['file' => ExcelFile::rules(2048)], ExcelFile::messages(2048));

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
}
