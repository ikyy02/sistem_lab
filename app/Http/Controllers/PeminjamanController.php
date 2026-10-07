<?php

namespace App\Http\Controllers;

use App\Models\AlatBahan;
use App\Models\Dosen;
use App\Models\Peminjaman;
use App\Models\Ruangan;
use App\Services\AuthService;
use App\Services\PeminjamanService;
use App\Support\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Peminjaman: pengajuan (Mahasiswa/Dosen), riwayat milik sendiri, dan detail transaksi.
 * Proses persetujuan & pengembalian ada di PengajuanController dan PengembalianController.
 */
class PeminjamanController extends Controller
{
    public const PER_PAGE = [10, 25, 50, 100];

    /** Halaman Ajukan Peminjaman + daftar katalog/ruangan yang tersedia. */
    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];

        $query = AlatBahan::query()
            ->select('alat_bahans.*', 'satuans.nama_satuan', 'ruangans.nama_ruangan')
            ->join('satuans', 'satuans.id_satuan', '=', 'alat_bahans.id_satuan')
            ->join('ruangans', 'ruangans.id_ruangan', '=', 'alat_bahans.id_ruangan')
            ->where('alat_bahans.stok', '>', 0);
        foreach (array_slice(preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 5) as $word) {
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word).'%';
            $query->where(function ($g) use ($like) {
                $g->orWhereRaw("alat_bahans.nama LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("satuans.nama_satuan LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("ruangans.nama_ruangan LIKE ? ESCAPE '!'", [$like]);
            });
        }
        $katalog = $query->orderBy('alat_bahans.nama')->paginate($perPage)->withQueryString();
        if ($katalog->currentPage() > $katalog->lastPage() && $katalog->lastPage() > 0) {
            return redirect()->route('peminjaman.index', array_merge($request->query(), ['page' => $katalog->lastPage()]));
        }

        // Opsi dropdown: hanya katalog dengan stok tersedia.
        $opsiKatalog = AlatBahan::query()
            ->select('alat_bahans.id_katalog', 'alat_bahans.nama', 'alat_bahans.jenis', 'alat_bahans.stok', 'satuans.nama_satuan')
            ->join('satuans', 'satuans.id_satuan', '=', 'alat_bahans.id_satuan')
            ->where('alat_bahans.stok', '>', 0)
            ->orderBy('alat_bahans.nama')
            ->get();

        return view('peminjaman.index', [
            'katalog' => $katalog,
            'search' => $search,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
            'opsiKatalog' => $opsiKatalog,
            'opsiRuangan' => Ruangan::orderBy('nama_ruangan')->get(),
            'opsiDosen' => Dosen::orderBy('nama')->get(['nama', 'email']),
            'me' => AuthService::user(),
        ]);
    }

    /** Simpan pengajuan peminjaman -> status "menunggu" (stok baru dipotong saat disetujui). */
    public function store(Request $request, PeminjamanService $service)
    {
        $data = $request->validate([
            'jenis_peminjaman' => ['required', Rule::in(Peminjaman::JENIS)],
            'email_dosen' => ['required_if:jenis_peminjaman,atas_dosen', 'nullable', 'email', 'max:50',
                Rule::exists('dosens', 'email')],
            'tanggal_peminjaman' => ['required', 'date'],
            'tanggal_rencana_kembali' => ['required', 'date', 'after_or_equal:tanggal_peminjaman'],
            'keterangan' => ['required', 'string', 'max:500'],
            'detail' => ['required', 'array', 'min:1'],
            'detail.*.id_katalog' => ['required', 'integer', 'distinct', Rule::exists('alat_bahans', 'id_katalog')],
            'detail.*.jumlah' => ['required', 'integer', 'min:1'],
            'ruangan' => ['nullable', 'array'],
            'ruangan.*.id_ruangan' => ['required', 'integer', 'distinct', Rule::exists('ruangans', 'id_ruangan')],
            'ruangan.*.tanggal_mulai' => ['required', 'date'],
            'ruangan.*.tanggal_selesai' => ['required', 'date', 'after:ruangan.*.tanggal_mulai'],
            'ruangan.*.keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'required' => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib diisi untuk peminjaman atas dosen.',
            'date' => ':attribute tidak valid.',
            'after_or_equal' => 'Tanggal pengembalian tidak boleh sebelum tanggal peminjaman.',
            'after' => 'Tanggal selesai ruangan harus setelah tanggal mulai.',
            'distinct' => 'Satu katalog/ruangan hanya boleh dipilih sekali.',
            'exists' => 'Data :attribute tidak ditemukan.',
            'detail.min' => 'Pilih minimal satu alat atau bahan yang akan dipinjam.',
            'detail.*.jumlah.min' => 'Jumlah peminjaman minimal 1.',
            'max' => ':attribute maksimal :max karakter.',
        ], [
            'jenis_peminjaman' => 'Jenis peminjaman', 'email_dosen' => 'Dosen',
            'tanggal_peminjaman' => 'Tanggal peminjaman', 'tanggal_rencana_kembali' => 'Tanggal pengembalian',
            'keterangan' => 'Keperluan peminjaman', 'detail' => 'Barang', 'detail.*.id_katalog' => 'Barang',
            'detail.*.jumlah' => 'Jumlah', 'ruangan.*.id_ruangan' => 'Ruangan',
            'ruangan.*.tanggal_mulai' => 'Tanggal mulai ruangan', 'ruangan.*.tanggal_selesai' => 'Tanggal selesai ruangan',
            'ruangan.*.keterangan' => 'Keterangan ruangan',
        ]);

        // Validasi stok saat pengajuan (stok dicek ulang & dikunci lagi saat persetujuan).
        $stok = AlatBahan::whereIn('id_katalog', array_column($data['detail'], 'id_katalog'))->get()->keyBy('id_katalog');
        $pesan = [];
        foreach ($data['detail'] as $row) {
            $k = $stok[$row['id_katalog']] ?? null;
            if ($k && (int) $row['jumlah'] > $k->stok) {
                $pesan[] = "Stok {$k->nama} tidak mencukupi (tersedia {$k->stok}, diminta {$row['jumlah']}).";
            }
        }
        if ($pesan) {
            throw ValidationException::withMessages(['detail' => implode(' ', $pesan)]);
        }

        $service->ajukan(AuthService::user()['email'], [
            'jenis_peminjaman' => $data['jenis_peminjaman'],
            'email_dosen' => $data['email_dosen'] ?? null,
            'tanggal_peminjaman' => $data['tanggal_peminjaman'],
            'tanggal_rencana_kembali' => $data['tanggal_rencana_kembali'],
            'keterangan' => $data['keterangan'],
            'detail' => $data['detail'],
            'ruangan' => array_map(fn ($r) => $r + ['keterangan' => $r['keterangan'] ?? null], $data['ruangan'] ?? []),
        ]);

        return redirect()->route('peminjaman.riwayat')
            ->with('success', 'Pengajuan peminjaman berhasil dikirim dan sedang menunggu persetujuan.');
    }

    /** Riwayat peminjaman milik sendiri. */
    public function riwayat(Request $request)
    {
        $me = AuthService::user();
        $status = $request->query('status');
        $status = in_array($status, Peminjaman::STATUS, true) ? $status : null;
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];

        $data = Peminjaman::query()
            ->with(Peminjaman::withProfil())
            ->where('email_peminjam', $me['email'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('tanggal_pengajuan')
            ->paginate($perPage)
            ->withQueryString();

        return view('peminjaman.riwayat', [
            'data' => $data,
            'status' => $status,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
            'labels' => Peminjaman::LABELS,
        ]);
    }

    /** Detail satu transaksi: pemilik, pemroses, Dosen, dan Laboran/Admin. */
    public function show(int $id)
    {
        $p = Peminjaman::with(array_merge(Peminjaman::withProfil(), [
            'pengembalians.details.detailPeminjaman.katalog', 'pemroses',
        ]))->findOrFail($id);

        $me = AuthService::user();
        $boleh = $p->email_peminjam === $me['email']
            || $p->email_pemroses === $me['email']
            || in_array($me['role'], [Role::DOSEN, Role::LABORAN], true);
        abort_unless($boleh, 403);

        // Jumlah yang sudah dikembalikan per detail peminjaman.
        $sudah = DB::table('pengembalian_details as pd')
            ->join('pengembalians as pg', 'pg.id_pengembalian', '=', 'pd.id_pengembalian')
            ->where('pg.id_peminjaman', $p->id_peminjaman)
            ->groupBy('pd.id_detail_peminjaman')
            ->selectRaw('pd.id_detail_peminjaman as id_detail, SUM(pd.jumlah_dikembalikan) as total')
            ->pluck('total', 'id_detail');

        $dapatMemproses = $p->status === 'menunggu'
            && in_array($me['role'], [Role::DOSEN, Role::LABORAN], true)
            && $p->email_peminjam !== $me['email'];

        return view('peminjaman.show', [
            'p' => $p,
            'sudah' => $sudah,
            'dapatMemproses' => $dapatMemproses,
            'dapatKembalikan' => $p->status === 'disetujui' && in_array($me['role'], [Role::DOSEN, Role::LABORAN], true),
        ]);
    }
}
