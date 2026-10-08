<?php

namespace App\Http\Controllers;

use App\Models\PengaturanOperasional;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Pengaturan Operasional: jam operasional dan hari operasional. */
class PengaturanOperasionalController extends Controller
{
    private const LABEL_HARI = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu'];

    public function index()
    {
        return view('pengaturan-operasional.index', [
            'setting' => PengaturanOperasional::ambil(),
            'labelHari' => self::LABEL_HARI,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'jam_buka' => ['required', 'date_format:H:i'],
            'jam_tutup' => ['required', 'date_format:H:i', 'after:jam_buka'],
            'hari' => ['required', 'array', 'min:1'],
            'hari.*' => ['string', Rule::in(PengaturanOperasional::HARI)],
        ], [
            'required' => ':attribute wajib diisi.',
            'date_format' => ':attribute tidak valid.',
            'jam_tutup.after' => 'Jam tutup harus setelah jam buka.',
            'hari.required' => 'Pilih minimal satu hari operasional.',
            'hari.min' => 'Pilih minimal satu hari operasional.',
        ], ['jam_buka' => 'Jam buka', 'jam_tutup' => 'Jam tutup']);

        $hari = array_values(array_intersect(PengaturanOperasional::HARI, $data['hari'])); // urut & unik
        PengaturanOperasional::ambil()->update([
            'jam_buka' => $data['jam_buka'] . ':00',
            'jam_tutup' => $data['jam_tutup'] . ':00',
            'hari_operasional' => implode(',', $hari),
        ]);

        return redirect()->route('pengaturan-operasional.index')->with('success', 'Pengaturan operasional berhasil disimpan.');
    }
}
