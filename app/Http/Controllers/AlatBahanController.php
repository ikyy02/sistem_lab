<?php

namespace App\Http\Controllers;

use App\Http\Controllers\KelolaKatalogController as Kelola;
use App\Models\AlatBahan;
use App\Models\Ruangan;
use Illuminate\Http\Request;

/** Katalog (hanya-baca untuk semua role): Alat, Bahan, dan Ruangan. CRUD ada di Kelola Katalog. */
class AlatBahanController extends Controller
{
    public function index(Request $request)
    {
        $jenis = in_array($request->query('jenis'), ['alat', 'bahan', 'ruangan'], true) ? $request->query('jenis') : null;
        $room = $jenis === 'ruangan';
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $sortable = $room ? ['nama'] : ['nama', 'stok', 'harga'];
        $sort = in_array($request->query('sort'), $sortable, true) ? $request->query('sort') : 'nama';
        $direction = strtolower((string) $request->query('direction')) === 'desc' ? 'desc' : 'asc';
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, Kelola::PER_PAGE, true) ? $perPage : Kelola::PER_PAGE[0];

        if ($room) {
            $query = Ruangan::query();
            $columns = ['nama_ruangan', 'keterangan'];
            $sortColumn = 'nama_ruangan';
            $pk = 'id_ruangan';
        } else {
            $query = AlatBahan::query()
                ->select('alat_bahans.*', 'satuans.nama_satuan', 'ruangans.nama_ruangan')
                ->join('satuans', 'satuans.id_satuan', '=', 'alat_bahans.id_satuan')
                ->join('ruangans', 'ruangans.id_ruangan', '=', 'alat_bahans.id_ruangan')
                ->when($jenis, fn ($q) => $q->where('alat_bahans.jenis', $jenis));
            $columns = ['alat_bahans.nama', 'satuans.nama_satuan', 'ruangans.nama_ruangan', 'alat_bahans.keterangan'];
            $sortColumn = 'alat_bahans.' . $sort;
            $pk = 'alat_bahans.id_katalog';
        }
        foreach (array_slice(preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 5) as $word) {
            $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word) . '%';
            $query->where(function ($g) use ($columns, $like) {
                foreach ($columns as $col) {
                    $g->orWhereRaw("{$col} LIKE ? ESCAPE '!'", [$like]);
                }
            });
        }
        $data = $query->orderBy($sortColumn, $direction)->orderBy($pk)->paginate($perPage)->withQueryString();
        if ($data->currentPage() > $data->lastPage()) {
            return redirect()->route('katalog', array_merge($request->query(), ['page' => $data->lastPage()]));
        }

        return view('alat-bahan.index', [
            'data' => $data, 'jenis' => $jenis, 'search' => $search, 'sort' => $sort,
            'direction' => $direction, 'perPage' => $perPage, 'perPageOptions' => Kelola::PER_PAGE,
        ]);
    }
}
