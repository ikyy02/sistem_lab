<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Services\JadwalService;
use App\Support\Options;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Kelola Jadwal Perkuliahan (khusus Staf Prodi): daftar, tambah, lihat, ubah, hapus.
 * Penyimpanan lewat JadwalService sehingga bentrok ruangan/hari/jam ikut diperiksa.
 */
class JadwalController extends Controller
{
    public const PER_PAGE = [10, 25, 50, 100];

    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $hari = in_array($request->query('hari'), Jadwal::HARI, true) ? $request->query('hari') : '';
        $status = in_array($request->query('status'), Jadwal::STATUS, true) ? $request->query('status') : '';
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];

        $query = Jadwal::query()
            ->select('jadwals.*')
            ->leftJoin('mata_kuliahs', 'mata_kuliahs.id_mata_kuliah', '=', 'jadwals.id_mata_kuliah')
            ->leftJoin('dosens', 'dosens.nuptk_nidn', '=', 'jadwals.nuptk_nidn')
            ->leftJoin('ruangans', 'ruangans.id_ruangan', '=', 'jadwals.id_ruangan')
            ->leftJoin('kelas', 'kelas.id_kelas', '=', 'jadwals.id_kelas');

        if ($hari !== '') {
            $query->where('jadwals.hari', $hari);
        }
        if ($status !== '') {
            $query->where('jadwals.status', $status);
        }
        $words = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach (array_slice($words, 0, 5) as $word) {
            $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word) . '%';
            $query->where(function ($group) use ($like) {
                foreach (['mata_kuliahs.nama_mk', 'mata_kuliahs.kode_mk', 'dosens.nama', 'dosens.nuptk_nidn',
                    'ruangans.nama_ruangan', 'kelas.nama_kelas', 'jadwals.tahun_akademik'] as $col) {
                    $group->orWhereRaw("{$col} LIKE ? ESCAPE '!'", [$like]);
                }
            });
        }

        $data = $query->orderByRaw(Jadwal::HARI_SQL)->orderBy('jam_mulai')->orderBy('id_jadwal')
            ->paginate($perPage)->withQueryString();
        if ($data->currentPage() > $data->lastPage()) {
            return redirect()->route('jadwal.index', array_merge($request->query(), ['page' => $data->lastPage()]));
        }

        return view('jadwal.index', [
            'data' => $data,
            'search' => $search,
            'hari' => $hari,
            'status' => $status,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
        ]);
    }

    public function create()
    {
        return view('jadwal.create', $this->form());
    }

    public function store(Request $request, JadwalService $service)
    {
        $this->validateJadwal($request);
        $service->simpan($this->payload($request));

        return redirect()->route('jadwal.index')->with('success', 'Jadwal perkuliahan berhasil ditambahkan.');
    }

    public function show(Jadwal $jadwal)
    {
        $jadwal->load(['mataKuliah', 'kelas.prodi', 'dosen.prodi', 'ruangan']);

        return view('jadwal.show', ['jadwal' => $jadwal] + $this->form());
    }

    public function edit(Jadwal $jadwal)
    {
        return view('jadwal.edit', ['jadwal' => $jadwal] + $this->form());
    }

    public function update(Request $request, Jadwal $jadwal, JadwalService $service)
    {
        $this->validateJadwal($request);
        $service->simpan($this->payload($request), $jadwal);

        return redirect()->route('jadwal.index')->with('success', 'Jadwal perkuliahan berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal)
    {
        $jadwal->delete();

        return redirect()->route('jadwal.index')->with('success', 'Jadwal perkuliahan berhasil dihapus.');
    }

    /** @return array<string,mixed> */
    private function form(): array
    {
        return [
            'mataKuliahOptions' => Options::mataKuliah(),
            'kelasOptions' => Options::kelas(),
            'dosenOptions' => Options::dosen(),
            'ruanganOptions' => Options::ruangan(),
        ];
    }

    private function validateJadwal(Request $request): void
    {
        $request->validate([
            'id_mata_kuliah' => ['required', Rule::exists('mata_kuliahs', 'id_mata_kuliah')],
            'id_kelas' => ['required', Rule::exists('kelas', 'id_kelas')],
            'nuptk_nidn' => ['required', Rule::exists('dosens', 'nuptk_nidn')],
            'id_ruangan' => ['required', Rule::exists('ruangans', 'id_ruangan')],
            'hari' => ['required', Rule::in(Jadwal::HARI)],
            'jam_mulai' => ['required', 'date_format:H:i:s,H:i'],
            'jam_selesai' => ['required', 'date_format:H:i:s,H:i'],
            'status' => ['required', Rule::in(Jadwal::STATUS)],
            'tahun_akademik' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'semester' => ['required', Rule::in(Jadwal::SEMESTER)],
        ], [
            'tahun_akademik.regex' => 'Tahun akademik berformat 2025/2026.',
            'id_mata_kuliah.exists' => 'Pilih mata kuliah dari daftar.',
        ]);
    }

    /** @return array<string,mixed> */
    private function payload(Request $request): array
    {
        return $request->only([
            'id_mata_kuliah', 'id_kelas', 'nuptk_nidn', 'id_ruangan', 'hari',
            'jam_mulai', 'jam_selesai', 'status', 'tahun_akademik', 'semester',
        ]);
    }
}
