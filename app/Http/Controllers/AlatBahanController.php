<?php

namespace App\Http\Controllers;

use App\Models\AlatBahan;
use Illuminate\Http\Request;

class AlatBahanController extends Controller
{
    /**
     * Tampilkan daftar katalog inventaris (alat, bahan, ruangan) dari tabel alat_bahans.
     * Filter `?jenis=` dipakai untuk menyaring data berdasarkan kolom jenis.
     * Variabel $data dan $jenis diteruskan ke view alat-bahan.index.
     */
    public function index(Request $request)
    {
        $jenis = $request->query('jenis');

        $data = AlatBahan::query()
            ->when(in_array($jenis, AlatBahan::JENIS), function ($query) use ($jenis) {
                return $query->where('jenis', $jenis);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('alat-bahan.index', compact('data', 'jenis'));
    }

    /**
     * Tampilkan form tambah data.
     */
    public function create()
    {
        return view('alat-bahan.create');
    }

    /**
     * Simpan data baru ke tabel alat_bahans.
     * Validasi sesuai struktur kolom database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'       => 'required|string|max:255',
            'jenis'      => 'required|in:' . implode(',', AlatBahan::JENIS),
            'satuan'     => 'required|string|max:255',
            'stok'       => 'required|integer|min:0',
            'kondisi'    => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        AlatBahan::create($request->only([
            'nama', 'jenis', 'satuan', 'stok', 'kondisi', 'keterangan',
        ]));

        return redirect()
            ->route('alat-bahan.index')
            ->with('success', 'Data berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit data.
     * Variabel $alatBahan diteruskan ke view alat-bahan.edit.
     */
    public function edit(AlatBahan $alatBahan)
    {
        return view('alat-bahan.edit', compact('alatBahan'));
    }

    /**
     * Perbarui data di tabel alat_bahans.
     */
    public function update(Request $request, AlatBahan $alatBahan)
    {
        $request->validate([
            'nama'       => 'required|string|max:255',
            'jenis'      => 'required|in:' . implode(',', AlatBahan::JENIS),
            'satuan'     => 'required|string|max:255',
            'stok'       => 'required|integer|min:0',
            'kondisi'    => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $alatBahan->update($request->only([
            'nama', 'jenis', 'satuan', 'stok', 'kondisi', 'keterangan',
        ]));

        return redirect()
            ->route('alat-bahan.index')
            ->with('success', 'Data berhasil diperbarui.');
    }

    /**
     * Hapus data dari tabel alat_bahans.
     */
    public function destroy(AlatBahan $alatBahan)
    {
        $alatBahan->delete();

        return redirect()
            ->route('alat-bahan.index')
            ->with('success', 'Data berhasil dihapus.');
    }
}
