<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\PengembalianDetail;
use App\Services\AuthService;
use App\Services\PeminjamanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Pengembalian (Dosen & Laboran/Admin): daftar peminjaman aktif dan pencatatan pengembalian.
 * Pemotongan/pengembalian stok serta penentuan status selesai dikerjakan PeminjamanService.
 */
class PengembalianController extends Controller
{
    public const PER_PAGE = [10, 25, 50, 100];

    public const FILTER = ['disetujui', 'selesai', 'semua'];

    public function index(Request $request)
    {
        $status = $request->query('status', 'disetujui');
        $status = in_array($status, self::FILTER, true) ? $status : 'disetujui';
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];

        $data = Peminjaman::query()
            ->with(array_merge(Peminjaman::withProfil(), ['details.katalog', 'ruangans.ruangan']))
            ->when($status !== 'semua', fn ($q) => $q->where('status', $status))
            ->orderBy('tanggal_rencana_kembali')
            ->paginate($perPage)
            ->withQueryString();
        if ($data->currentPage() > $data->lastPage() && $data->lastPage() > 0) {
            return redirect()->route('pengembalian.index', array_merge($request->query(), ['page' => $data->lastPage()]));
        }

        $ids = $data->pluck('id_peminjaman');
        // Jumlah yang sudah dikembalikan per detail (kunci: id_detail).
        $sudah = DB::table('pengembalian_details as pd')
            ->join('pengembalians as pg', 'pg.id_pengembalian', '=', 'pd.id_pengembalian')
            ->whereIn('pg.id_peminjaman', $ids)
            ->groupBy('pd.id_detail_peminjaman')
            ->selectRaw('pd.id_detail_peminjaman as id_detail, SUM(pd.jumlah_dikembalikan) as total')
            ->pluck('total', 'id_detail');
        // Riwayat pengembalian (tanggal aktual & penerima) per transaksi.
        $riwayat = Pengembalian::with('details.detailPeminjaman.katalog')
            ->whereIn('id_peminjaman', $ids)
            ->orderByDesc('tanggal_pengembalian')
            ->get()
            ->groupBy('id_peminjaman');

        return view('pengembalian.index', [
            'data' => $data,
            'sudah' => $sudah,
            'riwayat' => $riwayat,
            'status' => $status,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
        ]);
    }

    public function store(Request $request, int $id, PeminjamanService $service)
    {
        $data = $request->validate([
            'jumlah' => ['required', 'array', 'min:1'],
            'jumlah.*' => ['required', 'integer', 'min:1'],
            'kondisi' => ['nullable', 'array'],
            'kondisi.*' => ['nullable', Rule::in(PengembalianDetail::KONDISI)],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'required' => ':attribute wajib diisi.',
            'array' => ':attribute tidak valid.',
            'min' => 'Isi minimal satu jumlah barang yang dikembalikan.',
            'integer' => 'Jumlah dikembalikan harus berupa angka.',
            'in' => 'Kondisi harus baik atau rusak.',
            'max' => 'Keterangan maksimal :max karakter.',
        ], [
            'jumlah' => 'Jumlah dikembalikan', 'jumlah.*' => 'Jumlah dikembalikan',
            'kondisi.*' => 'Kondisi', 'keterangan' => 'Keterangan',
        ]);

        $rows = [];
        foreach ((array) $data['jumlah'] as $idDetail => $jumlah) {
            $rows[] = [
                'id_detail_peminjaman' => (int) $idDetail,
                'jumlah_dikembalikan' => (int) $jumlah,
                'kondisi' => ($data['kondisi'][(string) $idDetail] ?? $data['kondisi'][$idDetail] ?? null) ?: null,
            ];
        }
        if ($rows === []) {
            throw ValidationException::withMessages(['jumlah' => 'Isi minimal satu jumlah barang yang dikembalikan.']);
        }

        $service->kembalikan($id, AuthService::user()['email'], $rows, $data['keterangan'] ?? null);

        return redirect()->back()->with('success', 'Pengembalian berhasil dicatat dan stok barang telah diperbarui.');
    }
}
