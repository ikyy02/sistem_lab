<?php

namespace App\Http\Controllers;

use App\Models\AlatBahan;
use App\Models\PengaturanOperasional;
use App\Models\Satuan;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Pengaturan Laboratorium (Laboran/Admin): tab Satuan (sumber dropdown alat/bahan) dan tab Operasional (jam & hari buka).
 * Satuan yang sudah dipakai katalog tidak dapat dihapus (FK ON DELETE RESTRICT). Kelas dikelola di Data Akademik.
 */
class PengaturanLaboratoriumController extends Controller
{
    public const TABS = ['satuan' => 'Satuan', 'operasional' => 'Operasional'];

    public const PER_PAGE = [10, 25, 50, 100];

    private const LABEL_HARI = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu'];

    // Dua tab: Satuan (master dropdown katalog) dan Operasional (jam & hari buka).
    public function index(Request $request)
    {
        $tab = array_key_exists((string) $request->query('tab'), self::TABS) ? $request->query('tab') : 'satuan';
        if ($tab === 'operasional') {
            return view('pengaturan-lab.index', [
                'tab' => $tab, 'tabs' => self::TABS,
                'setting' => PengaturanOperasional::ambil(), 'labelHari' => self::LABEL_HARI,
            ]);
        }

        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];

        $query = Satuan::query()->selectRaw('id_satuan as id, nama_satuan as nama');
        foreach (array_slice(preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 5) as $word) {
            $query->whereRaw("nama_satuan LIKE ? ESCAPE '!'", ['%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word).'%']);
        }
        $data = $query->orderBy('nama_satuan')->orderBy('id_satuan')->paginate($perPage)->withQueryString();
        if ($data->currentPage() > $data->lastPage()) {
            return redirect()->route('pengaturan-lab.index', array_merge($request->query(), ['page' => $data->lastPage()]));
        }
        // Hitung berapa katalog yang memakai tiap satuan (untuk info sebelum hapus).
        $data->getCollection()->each(fn ($r) => $r->used = AlatBahan::where('id_satuan', $r->id)->count());

        return view('pengaturan-lab.index', [
            'tab' => $tab, 'tabs' => self::TABS, 'data' => $data,
            'search' => $search, 'perPage' => $perPage, 'perPageOptions' => self::PER_PAGE,
        ]);
    }

    public function store(Request $request)
    {
        $v = $this->validated($request, null);
        if ($v instanceof RedirectResponse) {
            return $v;
        }
        Satuan::create($v);

        return $this->back('Satuan berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        $record = Satuan::findOrFail($id);
        $v = $this->validated($request, $record);
        if ($v instanceof RedirectResponse) {
            return $v;
        }
        $record->update($v); // katalog memakai id sehingga otomatis mengikuti perubahan nama

        return $this->back('Satuan berhasil diperbarui.', 'success', $request);
    }

    public function destroy(Request $request, int $id)
    {
        $record = Satuan::findOrFail($id);
        try {
            $record->delete(); // ON DELETE RESTRICT bila sudah direferensikan
        } catch (QueryException $e) {
            return $this->back('Satuan "'.$record->nama_satuan.'" masih dipakai data lain dan tidak dapat dihapus.', 'error', $request);
        }

        return $this->back('Satuan berhasil dihapus.', 'success', $request);
    }

    // Jam & hari buka ini dipakai PeminjamanService untuk membatasi waktu peminjaman.
    public function updateOperasional(Request $request)
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

        // Urutkan sesuai urutan hari dan buang duplikat; disimpan sebagai teks dipisah koma.
        $hari = array_values(array_intersect(PengaturanOperasional::HARI, $data['hari'])); // urut & unik
        PengaturanOperasional::ambil()->update([
            'jam_buka' => $data['jam_buka'].':00',
            'jam_tutup' => $data['jam_tutup'].':00',
            'hari_operasional' => implode(',', $hari),
        ]);

        return redirect()->route('pengaturan-lab.index', ['tab' => 'operasional'])->with('success', 'Pengaturan operasional berhasil disimpan.');
    }

    /** @return array<string,string>|RedirectResponse */
    private function validated(Request $request, ?Satuan $record)
    {
        $nama = trim(preg_replace('/\s+/u', ' ', (string) $request->input('nama')));
        $validator = Validator::make(['nama' => $nama], [
            'nama' => ['required', 'string', 'max:50', Rule::unique('satuans', 'nama_satuan')->ignore($record?->getKey(), 'id_satuan')],
        ], [
            'nama.required' => 'Satuan wajib diisi.',
            'nama.unique' => 'Satuan sudah ada.',
            'nama.max' => 'Satuan maksimal :max karakter.',
        ]);
        if ($validator->fails()) {
            return redirect()->route('pengaturan-lab.index', $request->only(['search', 'per_page', 'page']) + ['tab' => 'satuan'])
                ->withErrors($validator)->withInput()
                ->with('open_modal', ['mode' => $record ? 'edit' : 'create', 'id' => $record?->getKey()]);
        }

        return ['nama_satuan' => $nama];
    }

    private function back(string $message, string $type = 'success', ?Request $request = null)
    {
        $query = $request ? $request->only(['search', 'per_page', 'page']) : [];

        return redirect()->route('pengaturan-lab.index', $query + ['tab' => 'satuan'])->with($type, $message);
    }
}
