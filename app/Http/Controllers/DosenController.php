<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use Illuminate\Http\Request;

class DosenController extends Controller
{
    /**
     * Tampilkan daftar data dosen dari tabel dosens.
     * Variabel $data diteruskan ke view dosen.index.
     */
    public function index()
    {
        $data = Dosen::query()
            ->latest()
            ->paginate(10);

        return view('dosen.index', compact('data'));
    }

    /**
     * Tampilkan form tambah data.
     */
    public function create()
    {
        return view('dosen.create');
    }

    /**
     * Simpan data baru ke tabel dosens.
     * Validasi sesuai struktur kolom database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nidn'           => 'required|string|max:20|unique:dosens,nidn',
            'nip'            => 'nullable|string|max:20|unique:dosens,nip',
            'nama'           => 'required|string|max:255',
            'program_studi'  => 'required|string|max:255',
            'email'          => 'required|email|max:255|unique:dosens,email',
            'no_whatsapp'    => 'required|string|max:20',
        ]);

        try {
            Dosen::create($request->only([
                'nidn', 'nip', 'nama', 'program_studi', 'email', 'no_whatsapp',
            ]));
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data dosen. Silakan coba lagi.');
        }

        return redirect()
            ->route('dosen.index')
            ->with('success', 'Data dosen berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail data dosen.
     * Variabel $dosen diteruskan ke view dosen.show.
     */
    public function show(Dosen $dosen)
    {
        return view('dosen.show', compact('dosen'));
    }

    /**
     * Tampilkan form edit data.
     * Variabel $dosen diteruskan ke view dosen.edit.
     */
    public function edit(Dosen $dosen)
    {
        return view('dosen.edit', compact('dosen'));
    }

    /**
     * Perbarui data di tabel dosens.
     */
    public function update(Request $request, Dosen $dosen)
    {
        $request->validate([
            'nidn'           => 'required|string|max:20|unique:dosens,nidn,' . $dosen->id,
            'nip'            => 'nullable|string|max:20|unique:dosens,nip,' . $dosen->id,
            'nama'           => 'required|string|max:255',
            'program_studi'  => 'required|string|max:255',
            'email'          => 'required|email|max:255|unique:dosens,email,' . $dosen->id,
            'no_whatsapp'    => 'required|string|max:20',
        ]);

        try {
            $dosen->update($request->only([
                'nidn', 'nip', 'nama', 'program_studi', 'email', 'no_whatsapp',
            ]));
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data dosen. Silakan coba lagi.');
        }

        return redirect()
            ->route('dosen.index')
            ->with('success', 'Data dosen berhasil diperbarui.');
    }

    /**
     * Hapus data dari tabel dosens.
     */
    public function destroy(Dosen $dosen)
    {
        try {
            $dosen->delete();
        } catch (\Throwable $e) {
            return back()
                ->with('error', 'Gagal menghapus data dosen. Silakan coba lagi.');
        }

        return redirect()
            ->route('dosen.index')
            ->with('success', 'Data dosen berhasil dihapus.');
    }
}