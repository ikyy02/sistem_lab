<?php

namespace App\Http\Controllers;

use App\Support\Cari;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\PeriodeJadwal;
use App\Services\AuthService;
use App\Services\JadwalService;
use App\Support\Options;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

        // Alur filter: periode, hari, status, lalu pencarian kata kunci.
        // Periode: default periode berjalan; periode arsip dapat dipilih (hanya-baca); 'semua' menampilkan seluruhnya.
        $daftarPeriode = PeriodeJadwal::orderByDesc('tanggal_mulai')->orderByDesc('id_periode')->get();
        $berjalan = $daftarPeriode->firstWhere('status', PeriodeJadwal::BERJALAN);
        $periodeReq = (string) $request->query('periode', '');
        $periode = ($periodeReq === 'semua' || $daftarPeriode->contains('id_periode', (int) $periodeReq))
            ? $periodeReq : (string) ($berjalan?->id_periode ?? 'semua');

        // Join ke tabel terkait supaya pencarian bisa memakai nama mata kuliah, dosen, ruangan, dan kelas.
        $query = Jadwal::query()
            ->select('jadwals.*')
            ->leftJoin('mata_kuliahs', 'mata_kuliahs.id_mata_kuliah', '=', 'jadwals.id_mata_kuliah')
            ->leftJoin('dosens', 'dosens.nuptk_nidn', '=', 'jadwals.nuptk_nidn')
            ->leftJoin('ruangans', 'ruangans.id_ruangan', '=', 'jadwals.id_ruangan')
            ->leftJoin('kelas', 'kelas.id_kelas', '=', 'jadwals.id_kelas');

        // Staf Prodi hanya melihat jadwal kelas prodinya (jadwal terhubung ke prodi lewat kelas).
        $query->when(AuthService::batasProdi(), fn ($q, $prodi) => $q->where('kelas.id_prodi', $prodi));
        if ($periode !== 'semua') {
            $query->where('jadwals.id_periode', (int) $periode);
        }
        if ($hari !== '') {
            $query->where('jadwals.hari', $hari);
        }
        if ($status !== '') {
            $query->where('jadwals.status', $status);
        }
        // Pencarian multi-kata (maks 5), tiap kata harus cocok di salah satu kolom.
        Cari::kolom($query, $search, ['mata_kuliahs.nama_mk', 'mata_kuliahs.kode_mk', 'dosens.nama', 'dosens.nuptk_nidn',
            'ruangans.nama_ruangan', 'kelas.nama_kelas', 'jadwals.tahun_akademik']);

        // Eager load periode agar tidak query berulang per baris.
        $query->with('periode');
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
            'periode' => $periode,
            'daftarPeriode' => $daftarPeriode,
            'berjalan' => $berjalan,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
        ]);
    }

    public function create()
    {
        return view('jadwal.create', $this->form());
    }

    // Simpan lewat JadwalService (di dalamnya ada cek bentrok jadwal).
    public function store(Request $request, JadwalService $service)
    {
        $service->simpan($this->payload($request));

        return redirect()->route('jadwal.index')->with('success', 'Jadwal perkuliahan berhasil ditambahkan.');
    }

    public function show(Jadwal $jadwal)
    {
        $this->pastikanMilikProdi($jadwal);
        $jadwal->load(['mataKuliah', 'kelas.prodi', 'dosen.prodi', 'ruangan']);

        return view('jadwal.show', ['jadwal' => $jadwal] + $this->form());
    }

    public function edit(Jadwal $jadwal)
    {
        $this->pastikanMilikProdi($jadwal);
        $this->pastikanBisaDiubah($jadwal);

        return view('jadwal.edit', ['jadwal' => $jadwal] + $this->form());
    }

    public function update(Request $request, Jadwal $jadwal, JadwalService $service)
    {
        $this->pastikanMilikProdi($jadwal);
        $this->pastikanBisaDiubah($jadwal);
        $service->simpan($this->payload($request), $jadwal);

        return redirect()->route('jadwal.index')->with('success', 'Jadwal perkuliahan berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal)
    {
        $this->pastikanMilikProdi($jadwal);
        $this->pastikanBisaDiubah($jadwal);
        $jadwal->delete();

        return redirect()->route('jadwal.index')->with('success', 'Jadwal perkuliahan berhasil dihapus.');
    }

    /** Staf Prodi hanya boleh membuka jadwal kelas prodinya; jadwal prodi lain 404 (Laboran tanpa batas). */
    private function pastikanMilikProdi(Jadwal $jadwal): void
    {
        $prodi = AuthService::batasProdi();
        abort_if($prodi !== null && (int) $jadwal->kelas?->id_prodi !== $prodi, 404);
    }

    /** Jadwal periode arsip hanya dapat dilihat. */
    private function pastikanBisaDiubah(Jadwal $jadwal): void
    {
        if ($jadwal->terarsip()) {
            abort(403, 'Jadwal arsip hanya dapat dilihat dan tidak dapat diubah.');
        }
    }

    /** @return array<string,mixed> */
    // Data dropdown untuk form tambah/ubah/lihat.
    private function form(): array
    {
        $prodi = AuthService::batasProdi();

        return [
            'mataKuliahOptions' => Options::mataKuliah($prodi),
            'kelasOptions' => Options::kelas($prodi),
            'dosenOptions' => Options::dosen($prodi),
            'ruanganOptions' => Options::ruangan(),
        ];
    }

    /** @return array<string,mixed> */
    private function payload(Request $request): array
    {
        $data = $request->only([
            'id_mata_kuliah', 'id_kelas', 'nuptk_nidn', 'id_ruangan', 'hari',
            'jam_mulai', 'jam_selesai', 'status', 'tahun_akademik', 'semester',
        ]);
        // Staf Prodi tidak boleh memakai kelas, mata kuliah, atau dosen prodi lain (dropdown hanya pilihan; ini penegakan di backend).
        $prodi = AuthService::batasProdi();
        if ($prodi !== null) {
            $salah = [];
            if (filled($data['id_kelas'] ?? null) && ! Kelas::whereKey($data['id_kelas'])->where('id_prodi', $prodi)->exists()) {
                $salah['id_kelas'] = 'Pilih kelas dari daftar.';
            }
            if (filled($data['id_mata_kuliah'] ?? null) && ! MataKuliah::whereKey($data['id_mata_kuliah'])->batasProdi($prodi)->exists()) {
                $salah['id_mata_kuliah'] = 'Pilih mata kuliah dari daftar.';
            }
            if (filled($data['nuptk_nidn'] ?? null) && ! Dosen::whereKey($data['nuptk_nidn'])->batasProdi($prodi)->exists()) {
                $salah['nuptk_nidn'] = 'Pilih dosen dari daftar.';
            }
            if ($salah) {
                throw ValidationException::withMessages($salah);
            }
        }

        return $data;
    }
}
