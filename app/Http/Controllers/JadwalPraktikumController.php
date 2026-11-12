<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\PeriodeJadwal;
use Illuminate\Http\Request;

/** Jadwal Praktikum (hanya-baca) untuk Mahasiswa & Dosen: daftar jadwal periode berjalan. */
class JadwalPraktikumController extends Controller
{
    private const PER_PAGE = [10, 25, 50];

    public function index(Request $request)
    {
        $hari = in_array($request->query('hari'), Jadwal::HARI, true) ? $request->query('hari') : '';
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];

        $daftarPeriode = PeriodeJadwal::orderByDesc('tanggal_mulai')->orderByDesc('id_periode')->get();
        $berjalan = $daftarPeriode->firstWhere('status', PeriodeJadwal::BERJALAN);
        $periodeReq = (string) $request->query('periode', '');
        $periode = ($periodeReq === 'semua' || $daftarPeriode->contains('id_periode', (int) $periodeReq))
            ? $periodeReq : (string) ($berjalan?->id_periode ?? 'semua');

        $query = Jadwal::query()
            ->with(['mataKuliah', 'dosen', 'ruangan', 'kelas.prodi'])
            ->where('status', 'aktif');

        if ($periode !== 'semua') {
            $query->where('id_periode', (int) $periode);
        }
        if ($hari !== '') {
            $query->where('hari', $hari);
        }

        $data = $query->orderByRaw(Jadwal::HARI_SQL)->orderBy('jam_mulai')->orderBy('id_jadwal')
            ->paginate($perPage)->withQueryString();

        if ($data->currentPage() > $data->lastPage()) {
            return redirect()->route('jadwal-praktikum.index', array_merge($request->query(), ['page' => $data->lastPage()]));
        }

        return view('jadwal-praktikum.index', [
            'data' => $data,
            'hari' => $hari,
            'periode' => $periode,
            'daftarPeriode' => $daftarPeriode,
            'berjalan' => $berjalan,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
        ]);
    }
}
