<?php

namespace App\Http\Controllers;

use App\Models\AlatBahan;
use Illuminate\Http\Request;

class AlatBahanController extends Controller
{
    /**
     * Tampilkan daftar alat dan bahan dari tabel alat_bahans.
     * Variabel $data diteruskan ke view alat-bahan.index.
     */
    public function index()
    {
        $data = AlatBahan::latest()->paginate(10);

        return view('alat-bahan.index', compact('data'));
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
            'jenis'      => 'required|in:alat,bahan',
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
            'jenis'      => 'required|in:alat,bahan',
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
