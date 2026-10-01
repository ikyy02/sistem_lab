<?php

namespace App\Http\Controllers;

use App\Models\AlatBahan;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Kelola Data Master: Satuan dan Kelas (sumber dropdown). Pilihan statis (Kondisi, Status, Jenis, Prodi), Ruangan, dan Role tidak dikelola di sini.
 * Mengubah nama master otomatis memperbarui data yang memakainya; master yang masih dipakai tidak dapat dihapus.
 */
class MasterDataController extends Controller
{
    public const KATEGORI = ['satuan' => 'Satuan', 'kelas' => 'Kelas'];
    public const PER_PAGE = [10, 25, 50, 100];

    /** Konfigurasi tiap tab: model, batas panjang, hitung pemakaian, dan sinkronisasi nama ke data yang memakainya. */
    private function tab(string $k): array
    {
        $barang = fn () => AlatBahan::where('jenis', '!=', 'ruangan');

        return match ($k) {
            'satuan' => ['model' => Satuan::class, 'max' => 50,
                'used' => fn ($r) => $barang()->where('satuan', $r->nama)->count(),
                'rename' => fn ($r, $n) => $barang()->where('satuan', $r->nama)->update(['satuan' => $n])],
            'kelas' => ['model' => Kelas::class, 'max' => 20,
                'used' => fn ($r) => Mahasiswa::where('kelas', $r->nama)->count(),
                'rename' => fn ($r, $n) => Mahasiswa::where('kelas', $r->nama)->update(['kelas' => $n])],
            default => abort(404),
        };
    }

    private function query(string $k, array $cfg)
    {
        $q = $cfg['model']::query();
        if (isset($cfg['tipe'])) {
            $q->where('tipe', $cfg['tipe']);
            if (isset($cfg['grup'])) {
                $q->where('grup', $cfg['grup']);
            }
        }

        return $q;
    }

    public function index(Request $request)
    {
        $kategori = array_key_exists((string) $request->query('kategori'), self::KATEGORI) ? $request->query('kategori') : 'satuan';
        $cfg = $this->tab($kategori);

        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];

        $query = $this->query($kategori, $cfg);
        foreach (array_slice(preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 5) as $word) {
            $query->whereRaw("nama LIKE ? ESCAPE '!'", ['%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word) . '%']);
        }
        $query->orderBy(...($kategori === 'kondisi' ? ['grup'] : ['nama']));
        $data = $query->orderBy('nama')->paginate($perPage)->withQueryString();
        if ($data->currentPage() > $data->lastPage()) {
            return redirect()->route('master-data.index', array_merge($request->query(), ['page' => $data->lastPage()]));
        }
        $data->getCollection()->each(fn ($r) => $r->used = $cfg['used']($r));

        return view('master-data.index', [
            'kategori' => $kategori, 'labels' => self::KATEGORI, 'cfg' => $cfg, 'data' => $data,
            'search' => $search, 'perPage' => $perPage, 'perPageOptions' => self::PER_PAGE,
        ]);
    }

    public function store(Request $request, string $kategori)
    {
        $cfg = $this->tab($kategori);
                $v = $this->validated($request, $kategori, $cfg, null);
        if ($v instanceof \Illuminate\Http\RedirectResponse) {
            return $v;
        }
        $cfg['model']::create($v);

        return $this->back($kategori, self::KATEGORI[$kategori] . ' berhasil ditambahkan.');
    }

    public function update(Request $request, string $kategori, int $id)
    {
        $cfg = $this->tab($kategori);
                $record = $this->query($kategori, $cfg)->findOrFail($id);
        $v = $this->validated($request, $kategori, $cfg, $record);
        if ($v instanceof \Illuminate\Http\RedirectResponse) {
            return $v;
        }

        DB::transaction(function () use ($cfg, $record, $v, $kategori) {
            // Kondisi: pindah grup (Alat/Bahan) dilarang jika sudah dipakai, agar data tidak yatim.
            $cfg['rename']($record, $v['nama']);
            $record->update($v);
        });

        return $this->back($kategori, self::KATEGORI[$kategori] . ' berhasil diperbarui.', 'success', $request);
    }

    public function destroy(Request $request, string $kategori, int $id)
    {
        $cfg = $this->tab($kategori);
                $record = $this->query($kategori, $cfg)->findOrFail($id);
        $used = $cfg['used']($record);
        if ($used > 0) {
            return $this->back($kategori, self::KATEGORI[$kategori] . ' "' . $record->nama . '" masih dipakai ' . $used . ' data dan tidak dapat dihapus.', 'error', $request);
        }
        $record->delete();

        return $this->back($kategori, self::KATEGORI[$kategori] . ' berhasil dihapus.', 'success', $request);
    }

    /** @return array<string,string>|\Illuminate\Http\RedirectResponse */
    private function validated(Request $request, string $kategori, array $cfg, $record)
    {
        $label = self::KATEGORI[$kategori];
        $nama = trim(preg_replace('/\s+/u', ' ', (string) $request->input('nama')));
        $input = ['nama' => $nama, 'grup' => $request->input('grup')];

        $model = new $cfg['model'];
        $unique = Rule::unique($model->getTable(), 'nama')->ignore($record?->id);
        if (isset($cfg['tipe'])) {
            $grup = isset($cfg['grups']) ? (string) $request->input('grup') : ($cfg['grup'] ?? '');
            $unique->where('tipe', $cfg['tipe'])->where('grup', $grup);
        }

        $rules = ['nama' => ['required', 'string', 'max:' . $cfg['max'], $unique]];
        if (isset($cfg['grups'])) {
            $rules['grup'] = ['required', Rule::in(array_keys($cfg['grups']))];
        }

        $validator = \Illuminate\Support\Facades\Validator::make($input, $rules, [
            'nama.required' => $label . ' wajib diisi.',
            'nama.unique' => $label . ' sudah ada.',
            'nama.max' => $label . ' maksimal :max karakter.',
            'grup.required' => 'Berlaku untuk wajib dipilih.',
            'grup.in' => 'Pilih Berlaku untuk dari daftar.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('master-data.index', $request->only(['search', 'per_page', 'page']) + ['kategori' => $kategori])
                ->withErrors($validator)->withInput()
                ->with('open_modal', ['mode' => $record ? 'edit' : 'create', 'id' => $record?->id]);
        }

        $data = ['nama' => $nama];
        if (isset($cfg['tipe'])) {
            $data += ['tipe' => $cfg['tipe'], 'grup' => isset($cfg['grups']) ? $input['grup'] : ($cfg['grup'] ?? '')];
        }
        if ($record && isset($cfg['grups']) && $record->grup !== $data['grup'] && $cfg['used']($record) > 0) {
            return $this->back($kategori, 'Kondisi yang sudah dipakai tidak dapat dipindah ke jenis lain.', 'error', $request);
        }

        return $data;
    }

    private function back(string $kategori, string $message, string $type = 'success', ?Request $request = null)
    {
        $query = $request ? $request->only(['search', 'per_page', 'page']) : [];

        return redirect()->route('master-data.index', $query + ['kategori' => $kategori])->with($type, $message);
    }
}
