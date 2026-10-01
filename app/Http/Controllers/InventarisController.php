<?php

namespace App\Http\Controllers;

use App\Models\AlatBahan;
use App\Services\InventarisImportService;
use App\Support\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Kelola Inventaris: Alat, Bahan, Ruangan (satu tabel `alat_bahans`, dibedakan kolom `jenis`,
 * ditampilkan terpisah per kategori). Ruangan: stok = kapasitas, kondisi = status ketersediaan.
 * Lokasi barang ditulis pada kolom keterangan.
 */
class InventarisController extends Controller
{
    public const KATEGORI = ['alat' => 'Alat', 'bahan' => 'Bahan', 'ruangan' => 'Ruangan'];
    public const PER_PAGE = [10, 25, 50, 100];

    public static function config(string $kategori): array
    {
        $room = $kategori === 'ruangan';
        $nama = ['name' => 'nama', 'label' => 'Nama', 'type' => 'text', 'placeholder' => 'Nama ' . strtolower(self::KATEGORI[$kategori])];
        $kondisi = ['name' => 'kondisi', 'label' => $room ? 'Status' : 'Kondisi', 'type' => 'select', 'options' => Options::kondisi($kategori), 'placeholder' => 'Pilih ' . ($room ? 'Status' : 'Kondisi')];
        $ket = ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'placeholder' => 'Contoh: Disimpan di Laboratorium Komputer 1, ruangan laboran.'];

        if ($room) {
            return [
                'columns' => ['nama' => ['Nama', true], 'stok' => ['Kapasitas', true], 'kondisi' => ['Status', true], 'keterangan' => ['Keterangan', false]],
                'search' => ['nama', 'kondisi', 'keterangan'],
                'fields' => [$nama, ['name' => 'stok', 'label' => 'Kapasitas', 'type' => 'number', 'placeholder' => 'Jumlah orang'], $kondisi, $ket],
            ];
        }

        return [
            'columns' => ['nama' => ['Nama', true], 'kategori' => ['Kategori', true], 'satuan' => ['Satuan', true], 'stok' => ['Stok', true], 'kondisi' => ['Kondisi', true], 'keterangan' => ['Keterangan', false]],
            'search' => ['nama', 'kategori', 'satuan', 'kondisi', 'keterangan'],
            'fields' => [
                $nama,
                ['name' => 'kategori', 'label' => 'Kategori', 'type' => 'select', 'options' => Options::kategori(), 'placeholder' => 'Pilih Kategori', 'required' => false],
                ['name' => 'satuan', 'label' => 'Satuan', 'type' => 'select', 'options' => Options::satuan(), 'placeholder' => 'Pilih Satuan'],
                ['name' => 'stok', 'label' => 'Stok', 'type' => 'number', 'placeholder' => 'Jumlah stok'],
                $kondisi, $ket,
            ],
        ];
    }

    public function index(Request $request)
    {
        $kategori = $this->kategori($request->query('kategori'));
        $config = self::config($kategori);

        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $sortable = array_keys(array_filter($config['columns'], fn ($c) => $c[1]));
        $sort = in_array($request->query('sort'), $sortable, true) ? $request->query('sort') : 'nama';
        $direction = strtolower((string) $request->query('direction')) === 'desc' ? 'desc' : 'asc';
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];

        $query = AlatBahan::where('jenis', $kategori);
        foreach (array_slice(preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 5) as $word) {
            $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word) . '%';
            $query->where(function ($g) use ($config, $like) {
                foreach ($config['search'] as $col) {
                    $g->orWhereRaw("{$col} LIKE ? ESCAPE '!'", [$like]);
                }
            });
        }

        $data = $query->orderBy($sort, $direction)->orderBy('id')->paginate($perPage)->withQueryString();
        if ($data->currentPage() > $data->lastPage()) {
            return redirect()->route('inventaris.index', array_merge($request->query(), ['page' => $data->lastPage()]));
        }

        return view('inventaris.index', [
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

        $data = $this->payload($v->validated(), $kategori);
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $this->saveImage($request->file('gambar'));
        }
        AlatBahan::create($data);

        return redirect()->route('inventaris.index', ['kategori' => $kategori])
            ->with('success', self::KATEGORI[$kategori] . ' berhasil ditambahkan.');
    }

    public function update(Request $request, string $kategori, int $id)
    {
        $item = AlatBahan::where('jenis', $kategori)->findOrFail($id);
        $v = $this->validator($request, $kategori, $id);
        if ($v->fails()) {
            return $this->fail($v, $kategori, 'edit', $id);
        }

        $data = $this->payload($v->validated(), $kategori);
        if ($request->hasFile('gambar')) {
            $this->deleteImage($item->gambar);
            $data['gambar'] = $this->saveImage($request->file('gambar'));
        }
        $item->update($data);

        return redirect()->route('inventaris.index', $request->only(['search', 'sort', 'direction', 'per_page', 'page']) + ['kategori' => $kategori])
            ->with('success', self::KATEGORI[$kategori] . ' berhasil diperbarui.');
    }

    public function destroy(Request $request, string $kategori, int $id)
    {
        $item = AlatBahan::where('jenis', $kategori)->findOrFail($id);
        $this->deleteImage($item->gambar);
        $item->delete();

        return redirect()->route('inventaris.index', $request->only(['search', 'sort', 'direction', 'per_page', 'page']) + ['kategori' => $kategori])
            ->with('success', self::KATEGORI[$kategori] . ' berhasil dihapus.');
    }

    public function template(Request $request, string $kategori, InventarisImportService $svc)
    {
        [$writer, $name] = $svc->template($kategori, $request->query('format') === 'csv' ? 'csv' : 'xlsx');

        return response()->streamDownload(fn () => $writer->save('php://output'), $name);
    }

    public function import(Request $request, string $kategori, InventarisImportService $svc)
    {
        $request->validate(['file' => ['required', 'file', 'max:5120']], [
            'file.required' => 'Pilih file Excel atau CSV terlebih dahulu.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);
        $back = redirect()->route('inventaris.index', ['kategori' => $kategori]);

        try {
            $result = $svc->import($request->file('file'), $kategori);
        } catch (\RuntimeException $e) {
            return $back->with('error', $e->getMessage());
        }

        return $result['success']
            ? $back->with('success', $result['inserted'] . ' data ' . self::KATEGORI[$kategori] . ' berhasil diimpor.')
            : $back->with('inv_import_report', $result);
    }

    private function validator(Request $request, string $kategori, ?int $id)
    {
        $room = $kategori === 'ruangan';
        $input = $request->only(['nama', 'kategori', 'satuan', 'stok', 'kondisi', 'keterangan', 'per_unit', 'harga_total', 'unit_dasar_harga']);
        $input['nama'] = trim(preg_replace('/\s+/u', ' ', (string) ($input['nama'] ?? '')));

        $rules = [
            'nama' => ['bail', 'required', 'string', 'max:255',
                Rule::unique('alat_bahans', 'nama')->where('jenis', $kategori)->ignore($id)],
            'stok' => ['bail', 'required', 'integer', 'min:' . ($room ? 1 : 0), 'max:9999999'],
            'kondisi' => ['required', Rule::in(Options::kondisi($kategori))],
            'keterangan' => ['nullable', 'string', 'max:2000'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
        if (! $room) {
            $rules['satuan'] = ['required', Rule::in(Options::satuan())];
            $rules['kategori'] = ['nullable', Rule::in(Options::kategori())];
            // Konversi unit (khusus TPK/SPK) -> data katalog tetap pakai satuan asli.
            $rules['per_unit'] = ['nullable', 'numeric', 'min:0.01'];
            $rules['harga_total'] = ['nullable', 'numeric', 'min:0', 'required_with:unit_dasar_harga'];
            $rules['unit_dasar_harga'] = ['nullable', 'numeric', 'min:0.01', 'required_with:harga_total'];
        }

        $label = $room ? 'Kapasitas' : 'Stok';
        return Validator::make($input + ['gambar' => $request->file('gambar')], $rules, [
            'required' => ':attribute wajib diisi.',
            'nama.unique' => 'Nama sudah terdaftar pada ' . strtolower(self::KATEGORI[$kategori]) . '.',
            'integer' => ':attribute harus berupa angka bulat.',
            'min' => ':attribute minimal :min.',
            'kondisi.in' => 'Pilih ' . ($room ? 'Status' : 'Kondisi') . ' dari daftar.',
            'satuan.in' => 'Pilih Satuan dari daftar.',
            'kategori.in' => 'Pilih Kategori dari daftar.',
            'gambar.image' => 'File harus berupa gambar.',
            'gambar.mimes' => 'Gambar harus berformat JPG, PNG, atau WEBP.',
            'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
            'per_unit.min' => 'Jumlah satuan asli per unit harus lebih dari 0.',
            'unit_dasar_harga.min' => 'Jumlah unit dasar harga harus lebih dari 0.',
            'harga_total.required_with' => 'Harga Total wajib diisi jika Jumlah Unit Dasar Harga diisi.',
            'unit_dasar_harga.required_with' => 'Jumlah Unit Dasar Harga wajib diisi jika Harga Total diisi.',
        ], [
            'nama' => 'Nama', 'stok' => $label, 'kondisi' => $room ? 'Status' : 'Kondisi', 'satuan' => 'Satuan', 'kategori' => 'Kategori',
            'keterangan' => 'Keterangan', 'gambar' => 'Gambar', 'per_unit' => 'Jumlah Satuan Asli per Unit',
            'harga_total' => 'Harga Total', 'unit_dasar_harga' => 'Jumlah Unit Dasar Harga',
        ]);
    }

    private function payload(array $data, string $kategori): array
    {
        unset($data['gambar']);
        $data['jenis'] = $kategori;
        $data['kategori'] = ($data['kategori'] ?? '') !== '' ? $data['kategori'] : null;
        $data['keterangan'] = ($data['keterangan'] ?? '') !== '' ? $data['keterangan'] : null;

        if ($kategori === 'ruangan') {
            $data['satuan'] = '';
            $data['kategori'] = null;
            $data['per_unit'] = $data['harga_total'] = $data['unit_dasar_harga'] = null;
        }

        return $data;
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
        return redirect()->route('inventaris.index', ['kategori' => $kategori])
            ->withErrors($validator)->withInput()
            ->with('open_modal', ['mode' => $mode, 'id' => $id]);
    }

    private function kategori(mixed $v): string
    {
        return is_string($v) && isset(self::KATEGORI[$v]) ? $v : 'alat';
    }
}
