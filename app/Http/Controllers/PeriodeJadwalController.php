<?php

namespace App\Http\Controllers;

use App\Models\PeriodeJadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Periode berlaku Jadwal Perkuliahan (Staf Prodi). Memulai periode baru mengarsipkan periode berjalan
 * (jadwalnya tetap tersimpan, hanya-baca); hanya periode berjalan yang dapat diubah tanggalnya.
 */
class PeriodeJadwalController extends Controller
{
    private const PESAN = [
        'required' => ':attribute wajib diisi.',
        'date_format' => ':attribute tidak valid.',
        'max' => ':attribute maksimal :max karakter.',
        'after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
    ];
    private const ATRIBUT = ['nama' => 'Nama periode', 'tanggal_mulai' => 'Tanggal mulai', 'tanggal_berakhir' => 'Tanggal berakhir'];

    public function index()
    {
        $periode = PeriodeJadwal::withCount('jadwals')->orderByDesc('tanggal_mulai')->orderByDesc('id_periode')->get();

        return view('jadwal.periode', ['periode' => $periode, 'berjalan' => $periode->firstWhere('status', PeriodeJadwal::BERJALAN)]);
    }

    /** Mulai periode baru: periode berjalan diarsipkan, jadwalnya tidak ditimpa. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_berakhir' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
        ], self::PESAN, self::ATRIBUT);

        DB::transaction(function () use ($data) {
            $terakhir = PeriodeJadwal::lockForUpdate()->max('tanggal_berakhir');
            if ($terakhir && $data['tanggal_mulai'] <= substr((string) $terakhir, 0, 10)) {
                throw ValidationException::withMessages(['tanggal_mulai' => 'Tanggal mulai harus setelah tanggal berakhir periode sebelumnya (' . substr((string) $terakhir, 0, 10) . ').']);
            }
            PeriodeJadwal::where('status', PeriodeJadwal::BERJALAN)->update(['status' => PeriodeJadwal::ARSIP]);
            PeriodeJadwal::create($data + ['status' => PeriodeJadwal::BERJALAN]);
        });

        return redirect()->route('jadwal.periode.index')->with('success', 'Periode baru dimulai. Periode sebelumnya disimpan sebagai arsip.');
    }

    /** Ubah nama/tanggal periode berjalan. Periode arsip tidak dapat diubah. */
    public function update(Request $request, int $id)
    {
        $periode = PeriodeJadwal::findOrFail($id);
        if ($periode->terarsip()) {
            throw ValidationException::withMessages(['nama' => 'Periode arsip hanya dapat dilihat dan tidak dapat diubah.']);
        }
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_berakhir' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
        ], self::PESAN, self::ATRIBUT);

        $akhirArsip = PeriodeJadwal::where('id_periode', '!=', $periode->id_periode)->max('tanggal_berakhir');
        if ($akhirArsip && $data['tanggal_mulai'] <= substr((string) $akhirArsip, 0, 10)) {
            throw ValidationException::withMessages(['tanggal_mulai' => 'Tanggal mulai harus setelah tanggal berakhir periode arsip terakhir (' . substr((string) $akhirArsip, 0, 10) . ').']);
        }
        $periode->update($data);

        return redirect()->route('jadwal.periode.index')->with('success', 'Periode jadwal berhasil diperbarui.');
    }
}
