<?php

namespace App\Http\Controllers;

use App\Exceptions\MahasiswaImportException;
use App\Http\Requests\MahasiswaRequest;
use App\Models\Mahasiswa;
use App\Services\MahasiswaImportService;
use App\Services\MahasiswaTemplateService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MahasiswaController extends Controller
{
    /** Pilihan jumlah data per halaman. */
    public const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    /** Kolom yang boleh dipakai untuk sorting: kunci = nilai query string, isi = label di dropdown. */
    public const SORT_OPTIONS = [
        'nim' => 'NIM',
        'nama' => 'Nama Mahasiswa',
        'program_studi' => 'Program Studi',
    ];

    /** Parameter query string yang dipertahankan setelah hapus data. */
    private const LIST_PARAMS = ['search', 'sort', 'direction', 'per_page', 'page'];

    /**
     * Tampilkan daftar mahasiswa dengan pencarian, sorting, dan pagination.
     * Variabel $data diteruskan ke view mahasiswa.index.
     */
    public function index(Request $request)
    {
        $search = mb_substr(trim($this->queryString($request, 'search')), 0, 100);

        // Nilai dari query string selalu di-whitelist agar tidak bisa dipakai untuk mengurutkan kolom sembarang.
        $sort = $this->queryString($request, 'sort');
        $sort = isset(self::SORT_OPTIONS[$sort]) ? $sort : 'nim';

        $direction = strtolower($this->queryString($request, 'direction')) === 'desc' ? 'desc' : 'asc';

        $perPage = (int) $this->queryString($request, 'per_page');
        $perPage = in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : self::PER_PAGE_OPTIONS[0];

        $data = Mahasiswa::query()
            ->search($search)
            ->orderBy($sort, $direction)
            ->orderBy('id') // pembeda tetap: urutan konsisten antar halaman meski nilai kolom sort sama
            ->paginate($perPage)
            ->withQueryString();

        // Halaman yang diminta sudah tidak ada (mis. per_page diubah atau data terakhir dihapus).
        if ($data->currentPage() > $data->lastPage()) {
            return redirect()->route('mahasiswa.index', array_merge(
                $request->query(),
                ['page' => $data->lastPage()]
            ));
        }

        return view('mahasiswa.index', [
            'data' => $data,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
            'sortOptions' => self::SORT_OPTIONS,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'isFiltered' => $request->hasAny(['search', 'sort', 'direction', 'per_page']),
        ]);
    }

    /**
     * Tampilkan form tambah data.
     */
    public function create()
    {
        return view('mahasiswa.create');
    }

    /**
     * Simpan data baru ke tabel mahasiswas.
     * Validasi & normalisasi ada di MahasiswaRequest (dipakai juga oleh import Excel).
     */
    public function store(MahasiswaRequest $request)
    {
        Mahasiswa::create($request->validated());

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail data mahasiswa.
     * Variabel $mahasiswa diteruskan ke view mahasiswa.show.
     */
    public function show(Mahasiswa $mahasiswa)
    {
        return view('mahasiswa.show', compact('mahasiswa'));
    }

    /**
     * Tampilkan form edit data.
     * Variabel $mahasiswa diteruskan ke view mahasiswa.edit.
     */
    public function edit(Mahasiswa $mahasiswa)
    {
        return view('mahasiswa.edit', compact('mahasiswa'));
    }

    /**
     * Perbarui data di tabel mahasiswas.
     */
    public function update(MahasiswaRequest $request, Mahasiswa $mahasiswa)
    {
        $mahasiswa->update($request->validated());

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    /**
     * Hapus data dari tabel mahasiswas.
     * Pencarian/sorting/halaman yang sedang dibuka dipertahankan (dikirim lewat query string form).
     */
    public function destroy(Request $request, Mahasiswa $mahasiswa)
    {
        $mahasiswa->delete();

        return redirect()
            ->route('mahasiswa.index', $request->only(self::LIST_PARAMS))
            ->with('success', 'Data mahasiswa berhasil dihapus.');
    }

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
                ->route('mahasiswa.index')
                ->with('error', $e->getMessage());
        }

        if (! $result['success']) {
            return redirect()
                ->route('mahasiswa.index')
                ->with('import_report', $result);
        }

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', "Import berhasil: {$result['inserted']} data mahasiswa ditambahkan.");
    }

    /**
     * Ambil parameter query string sebagai string ('' bila tidak ada / bukan string,
     * mis. ?sort[]=x), supaya input aneh tidak menyebabkan error.
     */
    private function queryString(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : '';
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
            ->route('mahasiswa.index')
            ->with('error', 'Library Excel belum terpasang. Jalankan perintah: composer require phpoffice/phpspreadsheet');
    }
}
