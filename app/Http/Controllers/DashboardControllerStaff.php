<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Services\AuthService;

/**
 * Dashboard Staf Prodi: ringkasan data akademik (dosen, mata kuliah, jadwal)
 * dihitung langsung dari database, plus jadwal perkuliahan terdekat dan data terbaru.
 */
class DashboardControllerStaff extends Controller
{
    public function index()
    {
        // Nama hari Indonesia huruf kecil, dipakai untuk mengurutkan jadwal hari ini paling atas.
        $hariIni = strtolower(now()->locale('id')->dayName);
        $prodi = AuthService::batasProdi();
        // Staf Prodi: semua angka dan daftar dibatasi ke prodinya (jadwal terhubung ke prodi lewat kelas); null = tanpa batas.
        $jadwal = fn () => Jadwal::berlaku()->when($prodi, fn ($q) => $q->whereIn('jadwals.id_kelas', Kelas::where('id_prodi', $prodi)->select('id_kelas')));

        return view('dashboard.staff_prodi', [
            'me' => AuthService::user(),
            'totalDosen' => Dosen::batasProdi($prodi)->count(),
            'totalMataKuliah' => MataKuliah::batasProdi($prodi)->count(),
            'totalJadwal' => $jadwal()->count(),
            'hariIni' => $hariIni,
            // 5 jadwal terdekat: hari ini dulu, lalu urut hari dan jam mulai. berlaku() = jadwal pada periode aktif.
            'jadwal' => $jadwal()->with(['mataKuliah', 'dosen', 'kelas', 'ruangan'])
                ->orderByRaw('CASE WHEN hari = ? THEN 0 ELSE 1 END', [$hariIni])
                ->orderByRaw(Jadwal::HARI_SQL)
                ->orderBy('jam_mulai')
                ->limit(5)
                ->get(),
            'mataKuliah' => MataKuliah::batasProdi($prodi)->orderBy('kode_mk')->limit(5)->get(),
            'dosenPerProdi' => $this->perProdi(Dosen::batasProdi($prodi), 'dosens'),
        ]);
    }

    /** Jumlah data per prodi (nama_prodi => jumlah). */
    private function perProdi($query, string $tabel)
    {
        return $query
            ->join('prodis', 'prodis.id_prodi', '=', $tabel . '.id_prodi')
            ->selectRaw('prodis.nama_prodi, COUNT(*) as jumlah')
            ->groupBy('prodis.nama_prodi')
            ->orderByDesc('jumlah')
            ->orderBy('prodis.nama_prodi')
            ->pluck('jumlah', 'nama_prodi');
    }
}
