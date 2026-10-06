<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use App\Models\PengaturanOperasional;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Pengaturan Operasional: jam operasional, hari operasional, dan hari/tanggal libur. */
class PengaturanOperasionalController extends Controller
{
    private const LABEL_HARI = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu'];

    public function index()
    {
        return view('pengaturan-operasional.index', [
            'setting' => PengaturanOperasional::ambil(),
            'labelHari' => self::LABEL_HARI,
            'libur' => HariLibur::orderBy('tanggal')->paginate(10),
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

    public function storeLibur(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date_format:Y-m-d', Rule::unique('hari_libur', 'tanggal')],
            'keterangan' => ['required', 'string', 'max:100'],
        ], [
            'required' => ':attribute wajib diisi.',
            'date_format' => ':attribute tidak valid.',
            'max' => ':attribute maksimal :max karakter.',
            'tanggal.unique' => 'Tanggal libur sudah terdaftar.',
        ], ['tanggal' => 'Tanggal', 'keterangan' => 'Keterangan']);
        $data['keterangan'] = trim(preg_replace('/\s+/u', ' ', $data['keterangan']));

        HariLibur::create($data);

        return redirect()->route('pengaturan-operasional.index')->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function destroyLibur(int $id)
    {
        HariLibur::findOrFail($id)->delete();

        return redirect()->route('pengaturan-operasional.index')->with('success', 'Hari libur berhasil dihapus.');
    }
}
