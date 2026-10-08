<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Jadwal;
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
        $hariIni = strtolower(date('l'));

        return view('dashboard.staff_prodi', [
            'me' => AuthService::user(),
            'totalDosen' => Dosen::count(),
            'totalMataKuliah' => MataKuliah::count(),
            'totalJadwal' => Jadwal::count(),
            'hariIni' => $hariIni,
            'jadwal' => Jadwal::with(['mataKuliah', 'dosen', 'kelas', 'ruangan'])
                ->orderByRaw('CASE WHEN hari = ? THEN 0 ELSE 1 END', [$hariIni])
                ->orderByRaw(Jadwal::HARI_SQL)
                ->orderBy('jam_mulai')
                ->limit(5)
                ->get(),
            'mataKuliah' => MataKuliah::orderBy('kode_mk')->limit(5)->get(),
            'dosenPerProdi' => $this->perProdi(Dosen::class, 'dosens'),
        ]);
    }

    /** Jumlah data per prodi (nama_prodi => jumlah). */
    private function perProdi(string $model, string $tabel)
    {
        return $model::query()
            ->join('prodis', 'prodis.id_prodi', '=', $tabel . '.id_prodi')
            ->selectRaw('prodis.nama_prodi, COUNT(*) as jumlah')
            ->groupBy('prodis.nama_prodi')
            ->orderByDesc('jumlah')
            ->orderBy('prodis.nama_prodi')
            ->pluck('jumlah', 'nama_prodi');
    }
}
