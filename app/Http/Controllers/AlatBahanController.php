<?php

namespace App\Http\Controllers;

use App\Models\AlatBahan;
use Illuminate\Http\Request;

/**
 * Katalog inventaris (read-only untuk Mahasiswa/Dosen/Staff Prodi).
 * CRUD Alat/Bahan/Ruangan ada di InventarisController (khusus Laboran/Admin).
 */
class AlatBahanController extends Controller
{
    public function index(Request $request)
    {
        $jenis = $request->query('jenis');

        $data = AlatBahan::query()
            ->when(in_array($jenis, AlatBahan::JENIS, true), fn ($q) => $q->where('jenis', $jenis))
            ->orderBy('nama')
            ->paginate(12)
            ->withQueryString();

        return view('alat-bahan.index', compact('data', 'jenis'));
    }
}
