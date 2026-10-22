<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Services\AkademikImportService;
use App\Services\AuthService;
use App\Support\ExcelFile;
use App\Support\Options;
use App\Support\Role;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Data Akademik (Staf Prodi dan Laboran/Admin): Mata Kuliah, Kelas, Semester, dan Dosen.
 * Tab Dosen hanya-baca untuk Staf Prodi (daftar dan pencarian); tambah/ubah/hapus dosen ditolak 403 di backend.
 * Laboran/Admin mengelola dosen dan akunnya lewat Kelola Data User (lihat tabs()).
 * Mata Kuliah dan Kelas dikelola penuh; Semester bernilai tetap sistem (ganjil/genap)
 * sehingga ditampilkan sebagai ringkasan, bukan data yang dapat diubah.
 * Master yang sudah dipakai jadwal tidak dapat dihapus (FK ON DELETE RESTRICT).
 */
class DataAkademikController extends Controller
{
    public const TABS = ['mata-kuliah' => 'Mata Kuliah', 'kelas' => 'Kelas', 'semester' => 'Semester', 'dosen' => 'Dosen'];
    public const PER_PAGE = [10, 25, 50, 100];

    /** Tab yang boleh dipakai role saat ini. Satu sumber untuk tampilan dan akses langsung lewat URL. */
    private function tabs(): array
    {
        return (AuthService::user()['role'] ?? null) === Role::LABORAN ? array_diff_key(self::TABS, ['dosen' => 1]) : self::TABS;
    }

    public function index(Request $request)
    {
        $tabs = $this->tabs();
        $tab = array_key_exists((string) $request->query('tab'), $tabs) ? $request->query('tab') : 'mata-kuliah';
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];

        $data = null;
        if ($tab === 'mata-kuliah') {
            $query = MataKuliah::query();
            $this->cari($query, $search, ['mata_kuliahs.kode_mk', 'mata_kuliahs.nama_mk']);
            $data = $query->orderBy('kode_mk')->paginate($perPage)->withQueryString();
        } elseif ($tab === 'kelas') {
            $query = Kelas::query()->select('kelas.*')
                ->leftJoin('prodis', 'prodis.id_prodi', '=', 'kelas.id_prodi')
                ->addSelect('prodis.nama_prodi');
            $this->cari($query, $search, ['kelas.nama_kelas', 'prodis.nama_prodi']);
            $data = $query->orderBy('kelas.nama_kelas')->orderBy('kelas.id_kelas')->paginate($perPage)->withQueryString();
        } elseif ($tab === 'dosen') {
            // Email tidak diambil: tidak boleh terlihat oleh Staff Prodi.
            $query = Dosen::query()->select('dosens.nuptk_nidn', 'dosens.nama', 'dosens.id_prodi', 'dosens.no_whatsapp', 'dosens.status')
                ->leftJoin('prodis', 'prodis.id_prodi', '=', 'dosens.id_prodi')
                ->addSelect('prodis.nama_prodi');
            $this->cari($query, $search, ['dosens.nuptk_nidn', 'dosens.nama', 'dosens.no_whatsapp', 'prodis.nama_prodi']);
            $data = $query->orderBy('dosens.nama')->orderBy('dosens.nuptk_nidn')->paginate($perPage)->withQueryString();
        }

        if ($data && $data->currentPage() > $data->lastPage()) {
            return redirect()->route('data-akademik.index', array_merge($request->query(), ['page' => $data->lastPage()]));
        }

        return view('data-akademik.index', [
            'tab' => $tab,
            'tabs' => $tabs,
            'data' => $data,
            'search' => $search,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
            'prodiOptions' => $tab === 'kelas' ? Options::prodi() : [],
            'semesterInfo' => $tab === 'semester' ? $this->semesterInfo() : null,
            'openModal' => session('open_modal'),
            'me' => AuthService::user(),
        ]);
    }

    public function store(Request $request, string $tab)
    {
        abort_unless(isset($this->tabs()[$tab]) && $tab !== 'dosen', 403); // dosen hanya-baca

        $data = $this->divalidasi($request, $tab, null);
        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }
        $model = $tab === 'kelas' ? Kelas::class : MataKuliah::class;
        $model::create($data);

        return $this->kembali($tab, self::TABS[$tab] . ' berhasil ditambahkan.');
    }

    public function update(Request $request, string $tab, string $id)
    {
        abort_unless(isset($this->tabs()[$tab]) && $tab !== 'dosen', 403); // dosen hanya-baca

        $model = $tab === 'kelas' ? Kelas::class : MataKuliah::class;
        $record = $model::findOrFail($id);
        $data = $this->divalidasi($request, $tab, $record);
        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }
        $record->update($data);

        return $this->kembali($tab, self::TABS[$tab] . ' berhasil diperbarui.', 'success', $request);
    }

    public function destroy(Request $request, string $tab, string $id)
    {
        abort_unless(isset($this->tabs()[$tab]) && $tab !== 'dosen', 403); // dosen hanya-baca

        $model = $tab === 'kelas' ? Kelas::class : MataKuliah::class;
        $record = $model::findOrFail($id);
        $nama = $tab === 'kelas' ? $record->nama_kelas : $record->nama_mk;
        try {
            $record->delete(); // ON DELETE RESTRICT bila sudah dipakai jadwal
        } catch (QueryException) {
            return $this->kembali($tab, self::TABS[$tab] . ' "' . $nama . '" masih dipakai jadwal dan tidak dapat dihapus.', 'error', $request);
        }

        return $this->kembali($tab, self::TABS[$tab] . ' berhasil dihapus.', 'success', $request);
    }

    public function template(string $tab, AkademikImportService $svc)
    {
        abort_if($tab === 'dosen', 404);
        [$writer, $name] = $svc->template($tab);

        return response()->streamDownload(fn () => $writer->save('php://output'), $name);
    }

    // Import Excel Mata Kuliah/Kelas; bila ada baris bermasalah, laporan per baris ditampilkan dan tidak ada data tersimpan.
    public function import(Request $request, string $tab, AkademikImportService $svc)
    {
        abort_if($tab === 'dosen', 404);
        $request->validate(['file' => ExcelFile::rules(2048)], ExcelFile::messages(2048));
        $back = redirect()->route('data-akademik.index', ['tab' => $tab]);

        try {
            $result = $svc->import($request->file('file'), $tab);
        } catch (\RuntimeException $e) {
            return $back->with('error', $e->getMessage());
        }

        return $result['success']
            ? $back->with('success', $result['inserted'] . ' data ' . self::TABS[$tab] . ' berhasil diimpor.')
            : $back->with('import_report', $result);
    }

    private function cari($query, string $search, array $kolom): void
    {
        $words = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach (array_slice($words, 0, 5) as $word) {
            $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word) . '%';
            $query->where(function ($group) use ($kolom, $like) {
                foreach ($kolom as $col) {
                    $group->orWhereRaw("{$col} LIKE ? ESCAPE '!'", [$like]);
                }
            });
        }
    }

    /** @return array<string,mixed>|\Illuminate\Http\RedirectResponse */
    private function divalidasi(Request $request, string $tab, $record)
    {
        if ($tab === 'kelas') {
            $rules = [
                'nama_kelas' => ['required', 'string', 'max:100', Rule::unique('kelas', 'nama_kelas')
                    ->ignore($record?->getKey(), 'id_kelas')->where('id_prodi', (int) $request->input('id_prodi'))],
                'id_prodi' => ['required', Rule::exists('prodis', 'id_prodi')],
            ];
            $pesan = [
                'nama_kelas.required' => 'Nama kelas wajib diisi.',
                'nama_kelas.unique' => 'Kelas dengan nama tersebut sudah ada pada prodi ini.',
                'id_prodi.required' => 'Prodi wajib dipilih.',
                'id_prodi.exists' => 'Pilih Prodi dari daftar.',
            ];
        } else {
            $rules = [
                'kode_mk' => ['required', 'string', 'max:15', Rule::unique('mata_kuliahs', 'kode_mk')->ignore($record?->getKey(), 'id_mata_kuliah')],
                'nama_mk' => ['required', 'string', 'max:100', Rule::unique('mata_kuliahs', 'nama_mk')->ignore($record?->getKey(), 'id_mata_kuliah')],
                'sks' => ['required', 'integer', 'min:1', 'max:10'],
                'semester' => ['required', Rule::in(Jadwal::SEMESTER)],
            ];
            $pesan = [
                'kode_mk.required' => 'Kode mata kuliah wajib diisi.',
                'kode_mk.unique' => 'Kode mata kuliah sudah dipakai.',
                'nama_mk.required' => 'Nama mata kuliah wajib diisi.',
                'nama_mk.unique' => 'Nama mata kuliah sudah dipakai.',
                'sks.min' => 'SKS minimal 1.',
                'sks.max' => 'SKS maksimal 10.',
                'semester.in' => 'Semester hanya Ganjil atau Genap.',
            ];
        }

        $v = Validator::make($request->all(), $rules, $pesan);
        if ($v->fails()) {
            return redirect()->route('data-akademik.index', $request->only(['tab', 'search', 'per_page', 'page']))
                ->withErrors($v)->withInput()
                ->with('open_modal', ['mode' => $record ? 'edit' : 'create', 'tab' => $tab, 'id' => $record?->getKey()]);
        }

        if ($tab === 'kelas') {
            return ['nama_kelas' => trim(preg_replace('/\s+/u', ' ', (string) $request->input('nama_kelas'))),
                'id_prodi' => (int) $request->input('id_prodi')];
        }

        return [
            'kode_mk' => strtoupper(trim((string) $request->input('kode_mk'))),
            'nama_mk' => trim(preg_replace('/\s+/u', ' ', (string) $request->input('nama_mk'))),
            'sks' => (int) $request->input('sks'),
            'semester' => $request->input('semester'),
        ];
    }

    private function kembali(string $tab, string $pesan, string $tipe = 'success', ?Request $request = null)
    {
        $query = $request ? $request->only(['tab', 'search', 'per_page', 'page']) : ['tab' => $tab];

        return redirect()->route('data-akademik.index', $query + ['tab' => $tab])->with($tipe, $pesan);
    }

    /** Ringkasan semester: nilai tetap sistem + jumlah jadwal/mata kuliah pada tiap nilai. */
    private function semesterInfo(): array
    {
        return collect(Jadwal::SEMESTER)->map(fn ($s) => [
            'nilai' => $s,
            'label' => ucfirst($s),
            'jadwal' => Jadwal::where('semester', $s)->count(),
            'mata_kuliah' => MataKuliah::where('semester', $s)->count(),
        ])->all();
    }
}