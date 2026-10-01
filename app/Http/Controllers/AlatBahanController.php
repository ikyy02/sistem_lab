<?php

namespace App\Http\Controllers;

use App\Http\Controllers\InventarisController as Inv;
use App\Models\AlatBahan;
use Illuminate\Http\Request;

/**
 * Katalog inventaris (read-only untuk semua role; Laboran/Admin tetap bisa lihat di sini,
 * tapi CRUD dilakukan di menu Kelola Inventaris). Search & sorting mengikuti pola halaman lain.
 */
class AlatBahanController extends Controller
{
    public function index(Request $request)
    {
        $jenis = in_array($request->query('jenis'), AlatBahan::JENIS, true) ? $request->query('jenis') : null;

        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $sortable = ['nama', 'satuan', 'stok', 'kondisi'];
        $sort = in_array($request->query('sort'), $sortable, true) ? $request->query('sort') : 'nama';
        $direction = strtolower((string) $request->query('direction')) === 'desc' ? 'desc' : 'asc';
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, Inv::PER_PAGE, true) ? $perPage : Inv::PER_PAGE[0];

        $query = AlatBahan::query()->when($jenis, fn ($q) => $q->where('jenis', $jenis));
        foreach (array_slice(preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 5) as $word) {
            $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word) . '%';
            $query->where(function ($g) use ($like) {
                $g->orWhereRaw('nama LIKE ? ESCAPE \'!\'', [$like])
                    ->orWhereRaw('satuan LIKE ? ESCAPE \'!\'', [$like])
                    ->orWhereRaw('kondisi LIKE ? ESCAPE \'!\'', [$like])
                    ->orWhereRaw('keterangan LIKE ? ESCAPE \'!\'', [$like]);
            });
        }

        $data = $query->orderBy($sort, $direction)->orderBy('id')->paginate($perPage)->withQueryString();
        if ($data->currentPage() > $data->lastPage()) {
            return redirect()->route('katalog', array_merge($request->query(), ['page' => $data->lastPage()]));
        }

        return view('alat-bahan.index', [
            'data' => $data, 'jenis' => $jenis, 'search' => $search, 'sort' => $sort,
            'direction' => $direction, 'perPage' => $perPage, 'perPageOptions' => Inv::PER_PAGE,
        ]);
    }
}
