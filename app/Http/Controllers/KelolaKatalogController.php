<?php

namespace App\Http\Controllers;

use App\Models\AlatBahan;
use App\Models\Ruangan;
use App\Services\KatalogImportService;
use App\Support\Options;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Kelola Katalog: Alat & Bahan (tabel alat_bahans, dibedakan kolom jenis) dan Ruangan (tabel ruangans).
 * Lokasi penyimpanan alat/bahan = id_ruangan. Harga = harga untuk 1 satuan.
 */
class KelolaKatalogController extends Controller
{
    public const KATEGORI = ['alat' => 'Alat', 'bahan' => 'Bahan', 'ruangan' => 'Ruangan'];
    public const PER_PAGE = [10, 25, 50, 100];

    public static function config(string $kategori): array
    {
        $ket = ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'placeholder' => 'Keterangan tambahan', 'required' => false];
        if ($kategori === 'ruangan') {
            return [
                'columns' => ['nama_ruangan' => ['Nama', true], 'keterangan' => ['Keterangan', false]],
                'search' => ['nama_ruangan', 'keterangan'],
                'fields' => [
                    ['name' => 'nama_ruangan', 'label' => 'Nama', 'type' => 'text', 'placeholder' => 'Nama ruangan', 'required' => true],
                    $ket,
                ],
            ];
        }

        return [
            'columns' => [
                'nama' => ['Nama', true], 'nama_satuan' => ['Satuan', true], 'stok' => ['Stok', true],
                'harga' => ['Harga / Satuan', true], 'nama_ruangan' => ['Ruangan', true], 'keterangan' => ['Keterangan', false],
            ],
            'search' => ['alat_bahans.nama', 'satuans.nama_satuan', 'ruangans.nama_ruangan', 'alat_bahans.keterangan'],
            'fields' => [
                ['name' => 'nama', 'label' => 'Nama', 'type' => 'text', 'placeholder' => 'Nama ' . strtolower(self::KATEGORI[$kategori]), 'required' => true],
                ['name' => 'id_satuan', 'label' => 'Satuan', 'type' => 'select', 'options' => Options::satuan(), 'placeholder' => 'Pilih Satuan', 'required' => true],
                ['name' => 'stok', 'label' => 'Stok', 'type' => 'number', 'placeholder' => 'Jumlah stok', 'required' => true],
                ['name' => 'harga', 'label' => 'Harga per Satuan (Rp)', 'type' => 'number', 'placeholder' => 'Contoh: 491.80', 'required' => true],
                ['name' => 'id_ruangan', 'label' => 'Ruangan Penyimpanan', 'type' => 'select', 'options' => Options::ruangan(), 'placeholder' => 'Pilih Ruangan', 'required' => true],
                $ket,
            ],
        ];
    }

    public function index(Request $request)
    {
        $kategori = $this->kategori($request->query('kategori'));
        $config = self::config($kategori);
        $room = $kategori === 'ruangan';
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $sortable = array_keys(array_filter($config['columns'], fn ($c) => $c[1]));
        $default = $room ? 'nama_ruangan' : 'nama';
        $sort = in_array($request->query('sort'), $sortable, true) ? $request->query('sort') : $default;
        $direction = strtolower((string) $request->query('direction')) === 'desc' ? 'desc' : 'asc';
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];

        if ($room) {
            $query = Ruangan::query();
            $sortColumn = $sort;
            $pk = 'id_ruangan';
        } else {
            $query = AlatBahan::query()
                ->select('alat_bahans.*', 'satuans.nama_satuan', 'ruangans.nama_ruangan')
                ->join('satuans', 'satuans.id_satuan', '=', 'alat_bahans.id_satuan')
                ->join('ruangans', 'ruangans.id_ruangan', '=', 'alat_bahans.id_ruangan')
                ->where('alat_bahans.jenis', $kategori);
            $sortColumn = match ($sort) {
                'nama_satuan' => 'satuans.nama_satuan',
                'nama_ruangan' => 'ruangans.nama_ruangan',
                default => 'alat_bahans.' . $sort,
            };
            $pk = 'alat_bahans.id_katalog';
        }
        foreach (array_slice(preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 5) as $word) {
            $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word) . '%';
            $query->where(function ($g) use ($config, $like) {
                foreach ($config['search'] as $col) {
                    $g->orWhereRaw("{$col} LIKE ? ESCAPE '!'", [$like]);
                }
            });
        }
        $data = $query->orderBy($sortColumn, $direction)->orderBy($pk)->paginate($perPage)->withQueryString();
        if ($data->currentPage() > $data->lastPage()) {
            return redirect()->route('kelola-katalog.index', array_merge($request->query(), ['page' => $data->lastPage()]));
        }

        return view('kelola-katalog.index', [
            'kategori' => $kategori, 'labels' => self::KATEGORI, 'config' => $config, 'data' => $data,
            'search' => $search, 'sort' => $sort, 'direction' => $direction, 'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
        ]);
    }

    public function store(Request $request, string $kategori)
    {
        $v = $this->validator($request, $kategori, null);
        if ($v->fails()) {
            return $this->fail($v, $kategori, 'create', null);
        }
        $data = $v->validated();
        if ($kategori === 'ruangan') {
            Ruangan::create($data);
        } else {
            unset($data['gambar']);
            $data['jenis'] = $kategori;
            $data['gambar'] = $this->saveImage($request->file('gambar'));
            AlatBahan::create($data);
        }

        return redirect()->route('kelola-katalog.index', ['kategori' => $kategori])
            ->with('success', self::KATEGORI[$kategori] . ' berhasil ditambahkan.');
    }

    public function update(Request $request, string $kategori, int $id)
    {
        $item = $this->find($kategori, $id);
        $v = $this->validator($request, $kategori, $item);
        if ($v->fails()) {
            return $this->fail($v, $kategori, 'edit', $id);
        }
        $data = $v->validated();
        if ($kategori === 'ruangan') {
            $item->update($data);
        } else {
            unset($data['gambar']);
            if ($request->hasFile('gambar')) {
                $this->deleteImage($item->gambar);
                $data['gambar'] = $this->saveImage($request->file('gambar'));
            }
            $item->update($data);
        }

        return redirect()->route('kelola-katalog.index', $request->only(['search', 'sort', 'direction', 'per_page', 'page']) + ['kategori' => $kategori])
            ->with('success', self::KATEGORI[$kategori] . ' berhasil diperbarui.');
    }

    public function destroy(Request $request, string $kategori, int $id)
    {
        $item = $this->find($kategori, $id);
        $back = redirect()->route('kelola-katalog.index', $request->only(['search', 'sort', 'direction', 'per_page', 'page']) + ['kategori' => $kategori]);
        try {
            $item->delete(); // FK RESTRICT: ditolak bila sudah dipakai peminjaman/jadwal/katalog
        } catch (QueryException $e) {
            return $back->with('error', self::KATEGORI[$kategori] . ' "' . ($item->nama ?? $item->nama_ruangan) . '" sudah dipakai data lain dan tidak dapat dihapus.');
        }
        if ($kategori !== 'ruangan') {
            $this->deleteImage($item->gambar);
        }

        return $back->with('success', self::KATEGORI[$kategori] . ' berhasil dihapus.');
    }

    public function template(Request $request, string $kategori, KatalogImportService $svc)
    {
        [$writer, $name] = $svc->template($kategori, $request->query('format') === 'csv' ? 'csv' : 'xlsx');

        return response()->streamDownload(fn () => $writer->save('php://output'), $name);
    }

    public function import(Request $request, string $kategori, KatalogImportService $svc)
    {
        $request->validate(['file' => ['required', 'file', 'max:5120']], [
            'file.required' => 'Pilih file Excel atau CSV terlebih dahulu.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);
        $back = redirect()->route('kelola-katalog.index', ['kategori' => $kategori]);
        try {
            $result = $svc->import($request->file('file'), $kategori);
        } catch (\RuntimeException $e) {
            return $back->with('error', $e->getMessage());
        }

        return $result['success']
            ? $back->with('success', $result['inserted'] . ' data ' . self::KATEGORI[$kategori] . ' berhasil diimpor.')
            : $back->with('inv_import_report', $result);
    }

    private function find(string $kategori, int $id)
    {
        return $kategori === 'ruangan'
            ? Ruangan::findOrFail($id)
            : AlatBahan::where('jenis', $kategori)->findOrFail($id);
    }

    private function validator(Request $request, string $kategori, $item)
    {
        $squish = fn ($v) => trim(preg_replace('/\s+/u', ' ', (string) $v));
        if ($kategori === 'ruangan') {
            $input = ['nama_ruangan' => $squish($request->input('nama_ruangan')), 'keterangan' => $request->input('keterangan')];
            $rules = [
                'nama_ruangan' => ['bail', 'required', 'string', 'max:50', Rule::unique('ruangans', 'nama_ruangan')->ignore($item?->getKey(), 'id_ruangan')],
                'keterangan' => ['nullable', 'string', 'max:2000'],
            ];

            return Validator::make($input, $rules, [
                'required' => ':attribute wajib diisi.', 'max' => ':attribute maksimal :max karakter.',
                'nama_ruangan.unique' => 'Nama ruangan sudah terdaftar.',
            ], ['nama_ruangan' => 'Nama', 'keterangan' => 'Keterangan']);
        }

        $input = $request->only(['id_satuan', 'stok', 'harga', 'id_ruangan', 'keterangan']);
        $input['nama'] = $squish($request->input('nama'));
        $input['gambar'] = $request->file('gambar');
        $rules = [
            'nama' => ['bail', 'required', 'string', 'max:50', Rule::unique('alat_bahans', 'nama')->ignore($item?->getKey(), 'id_katalog')],
            'id_satuan' => ['bail', 'required', 'integer', Rule::exists('satuans', 'id_satuan')],
            'stok' => ['bail', 'required', 'integer', 'min:0', 'max:4294967295'],
            'harga' => ['bail', 'required', 'numeric', 'gt:0', 'max:9999999999999.99', 'regex:/^\d+(\.\d{1,2})?$/'],
            'id_ruangan' => ['bail', 'required', 'integer', Rule::exists('ruangans', 'id_ruangan')],
            'keterangan' => ['nullable', 'string', 'max:2000'],
            // Gambar wajib: pada tambah harus diunggah; pada edit sudah ada sehingga hanya perlu bila mengganti.
            'gambar' => [($item && $item->gambar) ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
        return Validator::make($input, $rules, [
            'required' => ':attribute wajib diisi.',
            'nama.unique' => 'Nama sudah terdaftar pada katalog.',
            'nama.max' => 'Nama maksimal :max karakter.',
            'integer' => ':attribute harus berupa angka bulat.',
            'min' => ':attribute minimal :min.',
            'gt' => ':attribute harus lebih dari 0.',
            'harga.regex' => 'Harga maksimal 2 angka di belakang koma.',
            'id_satuan.exists' => 'Pilih Satuan dari daftar.',
            'id_ruangan.exists' => 'Pilih Ruangan dari daftar.',
            'gambar.image' => 'File harus berupa gambar.',
            'gambar.mimes' => 'Gambar harus berformat JPG, PNG, atau WEBP.',
            'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
        ], [
            'nama' => 'Nama', 'id_satuan' => 'Satuan', 'stok' => 'Stok', 'harga' => 'Harga',
            'id_ruangan' => 'Ruangan', 'keterangan' => 'Keterangan', 'gambar' => 'Gambar',
        ]);
    }

    private function saveImage($file): string
    {
        $name = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $file->move(public_path(AlatBahan::GAMBAR_DIR), $name);

        return $name;
    }

    private function deleteImage(?string $name): void
    {
        if ($name) {
            File::delete(public_path(AlatBahan::GAMBAR_DIR . '/' . basename($name)));
        }
    }

    private function fail($validator, string $kategori, string $mode, ?int $id)
    {
        return redirect()->route('kelola-katalog.index', ['kategori' => $kategori])
            ->withErrors($validator)->withInput()
            ->with('open_modal', ['mode' => $mode, 'id' => $id]);
    }

    private function kategori(mixed $v): string
    {
        return is_string($v) && isset(self::KATEGORI[$v]) ? $v : 'alat';
    }
}
