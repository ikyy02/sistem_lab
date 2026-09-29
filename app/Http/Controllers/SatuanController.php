<?php

namespace App\Http\Controllers;

use App\Models\AlatBahan;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** CRUD Satuan (dipanggil dari modal pada halaman Alat/Bahan; tanpa menu terpisah). */
class SatuanController extends Controller
{
    public function store(Request $request)
    {
        try {
            $nama = $this->validated($request, null);
        } catch (ValidationException $e) {
            return $this->back($request, $e->validator->errors()->first(), 'error');
        }
        Satuan::create(['nama' => $nama]);

        return $this->back($request, 'Satuan berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        $satuan = Satuan::findOrFail($id);
        try {
            $nama = $this->validated($request, $id);
        } catch (ValidationException $e) {
            return $this->back($request, $e->validator->errors()->first(), 'error');
        }
        $old = $satuan->nama;

        DB::transaction(function () use ($satuan, $nama, $old) {
            $satuan->update(['nama' => $nama]);
            AlatBahan::where('jenis', '!=', 'ruangan')->where('satuan', $old)->update(['satuan' => $nama]);
        });

        return $this->back($request, 'Satuan berhasil diperbarui.');
    }

    public function destroy(Request $request, int $id)
    {
        $satuan = Satuan::findOrFail($id);
        $used = AlatBahan::where('jenis', '!=', 'ruangan')->where('satuan', $satuan->nama)->count();
        if ($used > 0) {
            return $this->back($request, 'Satuan "' . $satuan->nama . '" masih dipakai ' . $used . ' data dan tidak dapat dihapus.', 'error');
        }
        $satuan->delete();

        return $this->back($request, 'Satuan berhasil dihapus.');
    }

    private function validated(Request $request, ?int $id): string
    {
        $nama = trim(preg_replace('/\s+/u', ' ', (string) $request->input('nama')));
        $request->merge(['nama' => $nama]);
        $request->validate(['nama' => ['required', 'string', 'max:50', Rule::unique('satuans', 'nama')->ignore($id)]], [
            'nama.required' => 'Nama satuan wajib diisi.',
            'nama.unique' => 'Satuan sudah ada.',
            'nama.max' => 'Nama satuan maksimal 50 karakter.',
        ]);

        return $nama;
    }

    private function back(Request $request, string $message, string $type = 'success')
    {
        $kategori = in_array($request->input('kategori'), ['alat', 'bahan'], true) ? $request->input('kategori') : 'alat';

        return redirect()->route('inventaris.index', ['kategori' => $kategori])
            ->with($type, $message)->with('open_satuan', true);
    }
}
