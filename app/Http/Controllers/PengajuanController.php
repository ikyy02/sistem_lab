<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Services\AuthService;
use App\Services\PeminjamanService;
use Illuminate\Http\Request;

/**
 * Pengajuan Peminjaman (Dosen & Laboran/Admin): antrean pengajuan beserta persetujuan/penolakan.
 * Aturan bisnis (stok, konflik ruangan, status) dikerjakan PeminjamanService.
 */
class PengajuanController extends Controller
{
    public const PER_PAGE = [10, 25, 50, 100];

    public const FILTER = ['menunggu', 'disetujui', 'ditolak', 'selesai', 'semua'];

    public function index(Request $request)
    {
        $status = $request->query('status', 'menunggu');
        $status = in_array($status, self::FILTER, true) ? $status : 'menunggu';
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];

        $query = Peminjaman::query()->with(Peminjaman::withProfil())
            ->when($status !== 'semua', fn ($q) => $q->where('status', $status));
        foreach (array_slice(preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 5) as $word) {
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word).'%';
            $query->where(function ($g) use ($like) {
                $g->whereRaw("email_peminjam LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("keterangan LIKE ? ESCAPE '!'", [$like])
                    ->orWhereHas('mahasiswaPeminjam', fn ($q) => $q
                        ->whereRaw("nama LIKE ? ESCAPE '!'", [$like])
                        ->orWhereRaw("nim LIKE ? ESCAPE '!'", [$like]))
                    ->orWhereHas('dosenPeminjam', fn ($q) => $q->whereRaw("nama LIKE ? ESCAPE '!'", [$like]));
            });
        }

        $data = $query->orderByDesc('tanggal_pengajuan')->paginate($perPage)->withQueryString();
        if ($data->currentPage() > $data->lastPage() && $data->lastPage() > 0) {
            return redirect()->route('pengajuan.index', array_merge($request->query(), ['page' => $data->lastPage()]));
        }

        return view('pengajuan.index', [
            'data' => $data,
            'status' => $status,
            'search' => $search,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
            'labels' => Peminjaman::LABELS,
            'jumlahMenunggu' => Peminjaman::where('status', 'menunggu')->count(),
        ]);
    }

    public function setujui(int $id, PeminjamanService $service)
    {
        $service->setujui($id, AuthService::user()['email']);

        return redirect()->back()->with('success', 'Pengajuan peminjaman disetujui dan stok barang telah diperbarui.');
    }

    public function tolak(Request $request, int $id, PeminjamanService $service)
    {
        $data = $request->validate([
            'alasan' => ['required', 'string', 'max:500'],
        ], [
            'required' => 'Alasan penolakan wajib diisi.',
            'max' => 'Alasan penolakan maksimal :max karakter.',
        ], ['alasan' => 'Alasan penolakan']);

        $service->tolak($id, AuthService::user()['email'], $data['alasan']);

        return redirect()->back()->with('success', 'Pengajuan peminjaman ditolak. Stok barang tidak berubah.');
    }
}
