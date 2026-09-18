<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    /**
     * Tampilkan daftar data mahasiswa dari tabel mahasiswas.
     * Variabel $data diteruskan ke view mahasiswa.index.
     */
    public function index()
    {
        $data = Mahasiswa::query()
            ->latest()
            ->paginate(10);

        return view('mahasiswa.index', compact('data'));
    }

    /**
     * Tampilkan form tambah data.
     */
    public function create()
    {
        return view('mahasiswa.create');
    }

    /**
     * Simpan data baru ke tabel mahasiswas.
     * Validasi sesuai struktur kolom database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nim'           => 'required|string|max:20|unique:mahasiswas,nim',
            'nama'          => 'required|string|max:255',
            'program_studi' => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:mahasiswas,email',
            'no_whatsapp'   => 'required|string|max:20',
        ]);

        Mahasiswa::create($request->only([
            'nim', 'nama', 'program_studi', 'email', 'no_whatsapp',
        ]));

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail data mahasiswa.
     * Variabel $mahasiswa diteruskan ke view mahasiswa.show.
     */
    public function show(Mahasiswa $mahasiswa)
    {
        return view('mahasiswa.show', compact('mahasiswa'));
    }

    /**
     * Tampilkan form edit data.
     * Variabel $mahasiswa diteruskan ke view mahasiswa.edit.
     */
    public function edit(Mahasiswa $mahasiswa)
    {
        return view('mahasiswa.edit', compact('mahasiswa'));
    }

    /**
     * Perbarui data di tabel mahasiswas.
     */
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $request->validate([
            'nim'           => 'required|string|max:20|unique:mahasiswas,nim,' . $mahasiswa->id,
            'nama'          => 'required|string|max:255',
            'program_studi' => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:mahasiswas,email,' . $mahasiswa->id,
            'no_whatsapp'   => 'required|string|max:20',
        ]);

        $mahasiswa->update($request->only([
            'nim', 'nama', 'program_studi', 'email', 'no_whatsapp',
        ]));

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    /**
     * Hapus data dari tabel mahasiswas.
     */
    public function destroy(Mahasiswa $mahasiswa)
    {
        $mahasiswa->delete();

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil dihapus.');
    }
}
