<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Services\AkunService;
use App\Services\AuthService;
use App\Support\Options;
use App\Support\Role;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Data Akademik (khusus Staf Prodi): Mata Kuliah, Kelas, Semester, dan Dosen.
 * Mata Kuliah, Kelas, dan Dosen dikelola penuh; Semester bernilai tetap sistem (ganjil/genap)
 * sehingga ditampilkan sebagai ringkasan, bukan data yang dapat diubah.
 * Dosen dibuat/diubah/dihapus bersama akun login-nya lewat AkunService (relasi 1:1 dengan akuns).
 * Master yang sudah dipakai jadwal tidak dapat dihapus (FK ON DELETE RESTRICT).
 */
class DataAkademikController extends Controller
{
    public const TABS = ['mata-kuliah' => 'Mata Kuliah', 'kelas' => 'Kelas', 'semester' => 'Semester', 'dosen' => 'Dosen'];
    public const PER_PAGE = [10, 25, 50, 100];

    public const STATUS_DOSEN = ['aktif', 'nonaktif'];

    public function index(Request $request)
    {
        $tab = array_key_exists((string) $request->query('tab'), self::TABS) ? $request->query('tab') : 'mata-kuliah';
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
            $query = Dosen::query()->select('dosens.*')
                ->leftJoin('prodis', 'prodis.id_prodi', '=', 'dosens.id_prodi')
                ->addSelect('prodis.nama_prodi');
            $this->cari($query, $search, ['dosens.nuptk_nidn', 'dosens.nama', 'dosens.email', 'dosens.no_whatsapp', 'prodis.nama_prodi']);
            $data = $query->orderBy('dosens.nama')->orderBy('dosens.nuptk_nidn')->paginate($perPage)->withQueryString();
        }

        if ($data && $data->currentPage() > $data->lastPage()) {
            return redirect()->route('data-akademik.index', array_merge($request->query(), ['page' => $data->lastPage()]));
        }

        return view('data-akademik.index', [
            'tab' => $tab,
            'tabs' => self::TABS,
            'data' => $data,
            'search' => $search,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
            'prodiOptions' => in_array($tab, ['kelas', 'dosen'], true) ? Options::prodi() : [],
            'statusDosen' => self::STATUS_DOSEN,
            'semesterInfo' => $tab === 'semester' ? $this->semesterInfo() : null,
            'openModal' => session('open_modal'),
            'me' => AuthService::user(),
        ]);
    }

    public function store(Request $request, string $tab)
    {
        if ($tab === 'dosen') {
            return $this->storeDosen($request);
        }

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
        if ($tab === 'dosen') {
            return $this->updateDosen($request, $id);
        }

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
        if ($tab === 'dosen') {
            return $this->destroyDosen($request, $id);
        }

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

    /** Simpan dosen + akun login secara atomik (email wajib ada di tabel akuns / FK). */
    private function storeDosen(Request $request)
    {
        $data = $this->divalidasi($request, 'dosen', null);
        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }
        $password = (string) ($data['password'] ?? '');
        $profil = array_diff_key($data, array_flip(['email', 'password']));

        try {
            app(AkunService::class)->buat(Role::DOSEN, $data['email'], $password, $profil);
        } catch (\Throwable $e) {
            return $this->kembali('dosen', 'Gagal menyimpan data dosen. Silakan coba lagi.', 'error', $request);
        }

        return $this->kembali('dosen', self::TABS['dosen'] . ' berhasil ditambahkan.');
    }

    /** Perbarui dosen beserta akunnya; password kosong berarti tidak diganti. */
    private function updateDosen(Request $request, string $id)
    {
        $dosen = Dosen::findOrFail($id);
        $data = $this->divalidasi($request, 'dosen', $dosen);
        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }
        $password = ($data['password'] ?? null) ?: null;
        $profil = array_diff_key($data, array_flip(['password']));

        try {
            app(AkunService::class)->ubah($dosen, Role::DOSEN, $profil, $password);
        } catch (\Throwable $e) {
            return $this->kembali('dosen', 'Gagal memperbarui data dosen. Silakan coba lagi.', 'error', $request);
        }

        return $this->kembali('dosen', self::TABS['dosen'] . ' berhasil diperbarui.', 'success', $request);
    }

    private function destroyDosen(Request $request, string $id)
    {
        $dosen = Dosen::findOrFail($id);
        if (Jadwal::where('nuptk_nidn', $dosen->getKey())->exists()) {
            return $this->kembali('dosen', 'Dosen "' . $dosen->nama . '" masih dipakai jadwal dan tidak dapat dihapus.', 'error', $request);
        }

        try {
            app(AkunService::class)->hapus($dosen);
        } catch (\Throwable $e) {
            return $this->kembali('dosen', 'Dosen "' . $dosen->nama . '" masih dipakai data lain dan tidak dapat dihapus.', 'error', $request);
        }

        return $this->kembali('dosen', self::TABS['dosen'] . ' berhasil dihapus.', 'success', $request);
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
        if ($tab === 'dosen') {
            return $this->divalidasiDosen($request, $record);
        }

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

    /** @return array<string,mixed>|\Illuminate\Http\RedirectResponse */
    private function divalidasiDosen(Request $request, $record)
    {
        $emailLama = $record?->email;

        $rules = [
            'nuptk_nidn' => ['bail', 'required', 'string', 'max:30', 'regex:/^[A-Za-z0-9]+$/', Rule::unique('dosens', 'nuptk_nidn')->ignore($record?->getKey(), 'nuptk_nidn')],
            'nama' => ['bail', 'required', 'string', 'min:2', 'max:100'],
            'id_prodi' => ['bail', 'required', 'integer', Rule::exists('prodis', 'id_prodi')],
            'email' => ['bail', 'required', 'email', 'max:50', Rule::unique('akuns', 'email')->ignore($emailLama, 'email')],
            'no_whatsapp' => ['bail', 'nullable', 'string', 'max:20', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{7,11}$/'],
            'status' => ['bail', 'required', Rule::in(self::STATUS_DOSEN)],
            'password' => [$record ? 'nullable' : 'required', 'string', 'min:4', 'max:100'],
        ];

        $pesan = [
            'nuptk_nidn.required' => 'NUPTK/NIDN wajib diisi.',
            'nuptk_nidn.unique' => 'NUPTK/NIDN sudah terdaftar.',
            'nuptk_nidn.regex' => 'NUPTK/NIDN hanya boleh berisi huruf dan angka.',
            'nama.required' => 'Nama dosen wajib diisi.',
            'nama.min' => 'Nama dosen minimal :min karakter.',
            'id_prodi.required' => 'Prodi wajib dipilih.',
            'id_prodi.integer' => 'Pilih Prodi dari daftar.',
            'id_prodi.exists' => 'Pilih Prodi dari daftar.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar di sistem.',
            'no_whatsapp.regex' => 'Nomor WhatsApp tidak valid. Gunakan format 08xxxxxxxxxx (atau diawali +62 / 62).',
            'status.in' => 'Status hanya Aktif atau Nonaktif.',
            'password.required' => 'Password akun login wajib diisi.',
            'password.min' => 'Password minimal :min karakter.',
        ];

        $v = Validator::make($request->all(), $rules, $pesan, ['nuptk_nidn' => 'NUPTK/NIDN', 'nama' => 'Nama dosen', 'id_prodi' => 'Prodi', 'email' => 'Email', 'no_whatsapp' => 'Nomor WhatsApp', 'status' => 'Status', 'password' => 'Password']);
        if ($v->fails()) {
            return redirect()->route('data-akademik.index', $request->only(['tab', 'search', 'per_page', 'page']))
                ->withErrors($v)->withInput()
                ->with('open_modal', ['mode' => $record ? 'edit' : 'create', 'tab' => 'dosen', 'id' => $record?->getKey()]);
        }

        $data = $v->validated();
        $wa = $data['no_whatsapp'] ?? null;
        $out = [
            'nuptk_nidn' => trim((string) $data['nuptk_nidn']),
            'nama' => trim(preg_replace('/\s+/u', ' ', (string) $data['nama'])),
            'id_prodi' => (int) $data['id_prodi'],
            'email' => mb_strtolower(trim((string) $data['email'])),
            'no_whatsapp' => $wa !== null && $wa !== '' ? preg_replace('/[\s\-\.\(\)]/', '', $wa) : null,
            'status' => $data['status'],
        ];
        if (! empty($data['password'])) {
            $out['password'] = $data['password'];
        }

        return $out;
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