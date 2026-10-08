<?php

namespace App\Http\Controllers;

use App\Models\AlatBahan;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\KelasMahasiswa;
use App\Models\Satuan;
use App\Support\Options;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Kelola Data Master: Satuan dan Kelas (sumber dropdown / jadwal). Prodi dan Ruangan tidak dikelola di sini.
 * Master yang sudah dipakai data lain tidak dapat dihapus (FK ON DELETE RESTRICT).
 */
class MasterDataController extends Controller
{
    public const KATEGORI = ['satuan' => 'Satuan', 'kelas' => 'Kelas'];

    public const PER_PAGE = [10, 25, 50, 100];

    private function tab(string $k): array
    {
        return match ($k) {
            'satuan' => ['model' => Satuan::class, 'pk' => 'id_satuan', 'name' => 'nama_satuan', 'max' => 50,
                'select' => 'id_satuan as id, nama_satuan as nama',
                'used' => fn ($r) => AlatBahan::where('id_satuan', $r->id)->count()],
            'kelas' => ['model' => Kelas::class, 'pk' => 'id_kelas', 'name' => 'nama_kelas', 'max' => 100,
                'select' => 'id_kelas as id, nama_kelas as nama, id_prodi as grup',
                'grups' => Options::prodi(), 'grup_label' => 'Prodi',
                'used' => fn ($r) => Jadwal::where('id_kelas', $r->id)->count()
                    + KelasMahasiswa::where('id_kelas', $r->id)->count()],
            default => abort(404),
        };
    }

    public function index(Request $request)
    {
        $kategori = array_key_exists((string) $request->query('kategori'), self::KATEGORI) ? $request->query('kategori') : 'satuan';
        $cfg = $this->tab($kategori);
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];

        $query = $cfg['model']::query()->selectRaw($cfg['select']);
        foreach (array_slice(preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 5) as $word) {
            $query->whereRaw("{$cfg['name']} LIKE ? ESCAPE '!'", ['%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word).'%']);
        }
        $data = $query->orderBy($cfg['name'])->orderBy($cfg['pk'])->paginate($perPage)->withQueryString();
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
        if ($v instanceof RedirectResponse) {
            return $v;
        }
        $cfg['model']::create($v);

        return $this->back($kategori, self::KATEGORI[$kategori].' berhasil ditambahkan.');
    }

    public function update(Request $request, string $kategori, int $id)
    {
        $cfg = $this->tab($kategori);
        $record = $cfg['model']::findOrFail($id);
        $v = $this->validated($request, $kategori, $cfg, $record);
        if ($v instanceof RedirectResponse) {
            return $v;
        }
        $record->update($v); // tabel lain memakai id sehingga otomatis mengikuti perubahan nama

        return $this->back($kategori, self::KATEGORI[$kategori].' berhasil diperbarui.', 'success', $request);
    }

    public function destroy(Request $request, string $kategori, int $id)
    {
        $cfg = $this->tab($kategori);
        $record = $cfg['model']::findOrFail($id);
        $nama = $record->{$cfg['name']};
        try {
            $record->delete(); // ON DELETE RESTRICT bila sudah direferensikan
        } catch (QueryException $e) {
            return $this->back($kategori, self::KATEGORI[$kategori].' "'.$nama.'" masih dipakai data lain dan tidak dapat dihapus.', 'error', $request);
        }

        return $this->back($kategori, self::KATEGORI[$kategori].' berhasil dihapus.', 'success', $request);
    }

    /** @return array<string,mixed>|RedirectResponse */
    private function validated(Request $request, string $kategori, array $cfg, $record)
    {
        $label = self::KATEGORI[$kategori];
        $nama = trim(preg_replace('/\s+/u', ' ', (string) $request->input('nama')));
        $input = ['nama' => $nama, 'grup' => $request->input('grup')];
        $table = (new $cfg['model'])->getTable();
        $unique = Rule::unique($table, $cfg['name'])->ignore($record?->getKey(), $cfg['pk']);
        if ($kategori === 'kelas') {
            $unique->where('id_prodi', (int) $request->input('grup')); // UNIQUE(id_prodi, nama_kelas)
        }
        $rules = ['nama' => ['required', 'string', 'max:'.$cfg['max'], $unique]];
        if (isset($cfg['grups'])) {
            $rules['grup'] = ['required', Rule::exists('prodis', 'id_prodi')];
        }
        $validator = Validator::make($input, $rules, [
            'nama.required' => $label.' wajib diisi.',
            'nama.unique' => $label.' sudah ada.',
            'nama.max' => $label.' maksimal :max karakter.',
            'grup.required' => 'Prodi wajib dipilih.',
            'grup.exists' => 'Pilih Prodi dari daftar.',
        ]);
        if ($validator->fails()) {
            return redirect()->route('master-data.index', $request->only(['search', 'per_page', 'page']) + ['kategori' => $kategori])
                ->withErrors($validator)->withInput()
                ->with('open_modal', ['mode' => $record ? 'edit' : 'create', 'id' => $record?->getKey()]);
        }
        $data = [$cfg['name'] => $nama];
        if (isset($cfg['grups'])) {
            $data['id_prodi'] = (int) $input['grup'];
        }

        return $data;
    }

    private function back(string $kategori, string $message, string $type = 'success', ?Request $request = null)
    {
        $query = $request ? $request->only(['search', 'per_page', 'page']) : [];

        return redirect()->route('master-data.index', $query + ['kategori' => $kategori])->with($type, $message);
    }
}
