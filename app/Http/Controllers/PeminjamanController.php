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
use Illuminate\Support\Facades\Cache;
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

    public const PESAN_MINIMAL_ITEM = 'Pilih minimal satu ruangan, alat, atau bahan yang ingin dipinjam sebelum mengajukan peminjaman.';

    /** Halaman Ajukan Peminjaman + data pilihan form (katalog, ruangan, dosen, jam kerja). */
    public function index(PeminjamanService $service)
    {
        $me = AuthService::user();
        $roleDosen = ($me['role'] ?? null) === Role::DOSEN;

        // Opsi dropdown: hanya katalog dengan stok tersedia.
        $opsiKatalog = AlatBahan::query()
            ->select('alat_bahans.id_katalog', 'alat_bahans.nama', 'alat_bahans.jenis', 'alat_bahans.stok', 'satuans.nama_satuan')
            ->join('satuans', 'satuans.id_satuan', '=', 'alat_bahans.id_satuan')
            ->where('alat_bahans.stok', '>', 0)
            ->orderBy('alat_bahans.nama')
            ->get();

        return view('peminjaman.index', [
            'opsiKatalog' => $opsiKatalog,
            'opsiRuangan' => Ruangan::orderBy('nama_ruangan')->get(),
            'opsiDosen' => Dosen::where('status', 'aktif')->orderBy('nama')->get(['nama', 'email']),
            'me' => $me,
            'roleDosen' => $roleDosen,
            'aturanJamKerja' => $service->aturanJamKerja(),
            // Batas waktu terendah untuk Dosen (hari & jam operasional pertama yang belum lewat).
            'awalIzin' => $roleDosen ? $service->waktuPalingAwal() : null,
        ]);
    }

    /** Simpan pengajuan peminjaman -> status "menunggu" (stok baru dipotong saat disetujui). */
    public function store(Request $request, PeminjamanService $service)
    {
        // Dosen: jenis selalu "Pembelajaran" (ditetapkan service), tanpa dosen penanggung jawab; input jenis dari klien diabaikan.
        $roleDosen = (AuthService::user()['role'] ?? null) === Role::DOSEN;
        if ($roleDosen) {
            $request->request->remove('jenis_peminjaman');
            $request->request->remove('email_dosen');
        }

        // Baris kosong/placeholder (belum memilih ruangan atau barang) tidak dihitung sebagai item.
        $request->merge([
            'detail' => $this->barisTerisi($request->input('detail'), 'id_katalog'),
            'ruangan' => $this->barisTerisi($request->input('ruangan'), 'id_ruangan'),
        ]);

        $data = $request->validate([
            'jenis_peminjaman' => $roleDosen ? ['nullable'] : ['required', Rule::in(Peminjaman::JENIS_MAHASISWA)],
            'email_dosen' => $roleDosen ? ['nullable'] : ['required_if:jenis_peminjaman,atas_dosen', 'nullable', 'email', 'max:50',
                Rule::exists('dosens', 'email')->where('status', 'aktif')],
            'tanggal_peminjaman' => ['required', 'date'],
            'tanggal_rencana_kembali' => ['required', 'date', 'after_or_equal:tanggal_peminjaman'],
            'keterangan' => [$roleDosen ? 'nullable' : 'required', 'string', 'max:500'],
            // Ruangan, alat, dan bahan dapat dipilih sendiri-sendiri; minimal salah satu harus ada.
            'detail' => ['required_without:ruangan', 'nullable', 'array'],
            'detail.*.id_katalog' => ['required', 'integer', 'distinct', Rule::exists('alat_bahans', 'id_katalog')],
            'detail.*.jumlah' => ['required', 'integer', 'min:1'],
            'ruangan' => ['nullable', 'array'],
            'ruangan.*.id_ruangan' => ['required', 'integer', 'distinct', Rule::exists('ruangans', 'id_ruangan')],
        ], [
            'required' => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib dipilih untuk peminjaman dengan penanggung jawab dosen.',
            'required_without' => self::PESAN_MINIMAL_ITEM,
            'date' => ':attribute tidak valid.',
            'after_or_equal' => 'Tanggal pengembalian tidak boleh sebelum tanggal peminjaman.',
            'distinct' => 'Satu katalog/ruangan hanya boleh dipilih sekali.',
            'exists' => 'Data :attribute tidak ditemukan.',
            'detail.*.jumlah.min' => 'Jumlah peminjaman minimal 1.',
            'max' => ':attribute maksimal :max karakter.',
        ], [
            'jenis_peminjaman' => 'Jenis peminjaman', 'email_dosen' => 'Dosen penanggung jawab',
            'tanggal_peminjaman' => 'Tanggal peminjaman', 'tanggal_rencana_kembali' => 'Tanggal pengembalian',
            'keterangan' => 'Keperluan peminjaman', 'detail' => 'Barang', 'detail.*.id_katalog' => 'Barang',
            'detail.*.jumlah' => 'Jumlah', 'ruangan.*.id_ruangan' => 'Ruangan',
        ]);

        // Validasi stok saat pengajuan (stok dicek ulang & dikunci lagi saat persetujuan).
        $detail = $data['detail'] ?? [];
        $stok = AlatBahan::whereIn('id_katalog', array_column($detail, 'id_katalog'))->get()->keyBy('id_katalog');
        $pesan = [];
        foreach ($detail as $row) {
            $k = $stok[$row['id_katalog']] ?? null;
            if ($k && (int) $row['jumlah'] > $k->stok) {
                $pesan[] = "Stok {$k->nama} tidak mencukupi (tersedia {$k->stok}, diminta {$row['jumlah']}).";
            }
        }
        if ($pesan) {
            throw ValidationException::withMessages(['detail' => implode(' ', $pesan)]);
        }

        $payload = [
            'jenis_peminjaman' => $data['jenis_peminjaman'] ?? null,
            'email_dosen' => $data['email_dosen'] ?? null,
            'tanggal_peminjaman' => $data['tanggal_peminjaman'],
            'tanggal_rencana_kembali' => $data['tanggal_rencana_kembali'],
            'keterangan' => $data['keterangan'] ?? null,
            'detail' => $detail,
            'ruangan' => array_map(fn ($r) => ['id_ruangan' => $r['id_ruangan']], $data['ruangan'] ?? []),
        ];

        // Perlindungan kirim ganda: satu token form hanya boleh menghasilkan satu pengajuan (dikonsumsi setelah validasi lolos).
        $kunci = null;
        if (is_string($request->input('token_pengajuan')) && $request->input('token_pengajuan') !== '') {
            $kunci = 'pengajuan:'.AuthService::user()['email'].':'.substr($request->input('token_pengajuan'), 0, 64);
            if (! Cache::add($kunci, 1, now()->addMinutes(10))) {
                return redirect()->route('peminjaman.riwayat')
                    ->with('success', 'Pengajuan Anda sudah diterima sebelumnya.');
            }
        }
        try {
            $service->ajukan(AuthService::user()['email'], $payload);
        } catch (\Throwable $e) {
            if ($kunci) {
                Cache::forget($kunci); // gagal: token boleh dipakai lagi setelah perbaikan
            }
            throw $e;
        }

        return redirect()->route('peminjaman.riwayat')
            ->with('success', 'Pengajuan peminjaman berhasil dikirim dan sedang menunggu persetujuan.');
    }

    /** Buang baris tanpa pilihan; hasil kosong menjadi null agar dihitung "belum ada item". */
    private function barisTerisi(mixed $baris, string $kunci): ?array
    {
        if (! is_array($baris)) {
            return null;
        }
        $isi = array_values(array_filter($baris, fn ($r) => is_array($r) && trim((string) ($r[$kunci] ?? '')) !== ''));

        return $isi === [] ? null : $isi;
    }

    /** Kolom yang dapat diurutkan pada Riwayat Peminjaman. */
    public const SORT = [
        'tanggal_pengajuan' => 'Waktu Pengajuan', 'tanggal_peminjaman' => 'Tanggal Peminjaman',
        'tanggal_rencana_kembali' => 'Tanggal Pengembalian', 'status' => 'Status',
    ];

    /** Riwayat peminjaman milik sendiri: terbaru (waktu pengajuan) di atas; ada pencarian, filter status, sorting, pagination. */
    public function riwayat(Request $request)
    {
        $me = AuthService::user();
        $status = $request->query('status');
        $status = in_array($status, Peminjaman::STATUS, true) ? $status : null;
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $sort = array_key_exists($request->query('sort'), self::SORT) ? $request->query('sort') : 'tanggal_pengajuan';
        $arah = strtolower((string) $request->query('direction'));
        $direction = in_array($arah, ['asc', 'desc'], true) ? $arah : ($sort === 'tanggal_pengajuan' ? 'desc' : 'asc');
        $perPage = (int) $request->query('per_page');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];

        $query = Peminjaman::query()
            ->with(Peminjaman::withProfil())
            ->where('email_peminjam', $me['email'])
            ->when($status, fn ($q) => $q->where('status', $status));
        foreach (array_slice(preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 5) as $word) {
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word).'%';
            $query->where(function ($g) use ($like) {
                $g->whereRaw("peminjamans.keterangan LIKE ? ESCAPE '!'", [$like])
                    ->orWhereExists(fn ($q) => $q->selectRaw('1')->from('peminjaman_details as d')
                        ->join('alat_bahans as k', 'k.id_katalog', '=', 'd.id_katalog')
                        ->whereColumn('d.id_peminjaman', 'peminjamans.id_peminjaman')
                        ->whereRaw("k.nama LIKE ? ESCAPE '!'", [$like]))
                    ->orWhereExists(fn ($q) => $q->selectRaw('1')->from('peminjaman_ruangans as pr')
                        ->join('ruangans as r', 'r.id_ruangan', '=', 'pr.id_ruangan')
                        ->whereColumn('pr.id_peminjaman', 'peminjamans.id_peminjaman')
                        ->whereRaw("r.nama_ruangan LIKE ? ESCAPE '!'", [$like]));
            });
        }
        // Urutan utama sesuai pilihan; waktu pengajuan terbaru dan id terbaru sebagai pembeda agar urutan stabil.
        $data = $query->orderBy($sort, $direction)
            ->when($sort !== 'tanggal_pengajuan', fn ($q) => $q->orderByDesc('tanggal_pengajuan'))
            ->orderByDesc('id_peminjaman')
            ->paginate($perPage)
            ->withQueryString();
        if ($data->currentPage() > $data->lastPage() && $data->lastPage() > 0) {
            return redirect()->route('peminjaman.riwayat', array_merge($request->query(), ['page' => $data->lastPage()]));
        }

        return view('peminjaman.riwayat', [
            'data' => $data,
            'status' => $status,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'sortLabels' => self::SORT,
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
            // Pengembalian hanya dicatat Laboran.
            'dapatKembalikan' => $p->status === 'disetujui' && $p->melibatkanBarang() && $me['role'] === Role::LABORAN,
            'dapatBatalkan' => $p->bisaDibatalkanOleh($me['email']),
            'dapatSelesai' => $p->bisaDiselesaikanOleh($me['email'], $me['role']),
        ]);
    }

    /** Batalkan peminjaman ber-ruangan (peminjam, sampai 23.59 H-1). */
    public function batalkan(int $id, PeminjamanService $service)
    {
        $service->batalkan($id, AuthService::user()['email']);

        return redirect()->route('peminjaman.show', $id)->with('success', 'Peminjaman berhasil dibatalkan.');
    }

    /** Tombol Selesai untuk peminjaman ber-ruangan (pihak berwenang dicek di service). */
    public function selesai(int $id, PeminjamanService $service)
    {
        $service->selesaikan($id, AuthService::user()['email']);

        return redirect()->route('peminjaman.show', $id)->with('success', 'Peminjaman ditandai selesai.');
    }
}
