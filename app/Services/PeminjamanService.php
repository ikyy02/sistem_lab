<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\AlatBahan;
use App\Models\Jadwal;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\PeminjamanRuangan;
use App\Models\Pengembalian;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Pengajuan, persetujuan, penolakan, dan penentuan status selesai peminjaman.
 * Semua aturan bisnis diterapkan di sini (dalam transaksi database); constraint DB menjadi lapis kedua.
 */
class PeminjamanService
{
    /**
     * @param  array{jenis_peminjaman:string, email_dosen?:?string, tanggal_peminjaman:mixed, tanggal_rencana_kembali:mixed, keterangan?:?string,
     *               detail?:array<int,array{id_katalog:int,jumlah:int}>, ruangan?:array<int,array{id_ruangan:int,tanggal_mulai:mixed,tanggal_selesai:mixed,keterangan?:?string}>}  $data
     */
    public function ajukan(string $emailPengaju, array $data): Peminjaman
    {
        $sekarang = now();
        $err = [];

        $pengaju = Akun::find($emailPengaju);
        if (! $pengaju || ! in_array($pengaju->role, ['mahasiswa', 'dosen'], true)) {
            $this->gagal(['email_peminjam' => 'Hanya Mahasiswa dan Dosen yang dapat mengajukan peminjaman.']);
        }

        $jenis = $data['jenis_peminjaman'] ?? null;
        $emailDosen = $data['email_dosen'] ?? null;
        if (! in_array($jenis, Peminjaman::JENIS, true)) {
            $err['jenis_peminjaman'] = 'Jenis peminjaman tidak valid.';
        } elseif ($jenis === 'pribadi' && $emailDosen !== null && $emailDosen !== '') {
            $err['email_dosen'] = 'Peminjaman pribadi tidak boleh memiliki dosen.';
        } elseif ($jenis === 'atas_dosen') {
            if (! $emailDosen) {
                $err['email_dosen'] = 'Dosen wajib diisi untuk peminjaman atas dosen.';
            } elseif (! Akun::where('email', $emailDosen)->where('role', 'dosen')->exists()) {
                $err['email_dosen'] = 'Dosen harus berupa akun dengan role dosen.';
            }
        }
        if ($jenis === 'pribadi') {
            $emailDosen = null;
        }

        $pinjam = $this->tanggal($data['tanggal_peminjaman'] ?? null);
        $kembali = $this->tanggal($data['tanggal_rencana_kembali'] ?? null);
        if (! $pinjam || ! $kembali) {
            $err['tanggal_peminjaman'] = 'Tanggal peminjaman dan rencana kembali wajib diisi dengan format yang valid.';
        } elseif ($pinjam->lt($sekarang->copy()->startOfMinute())) {
            $err['tanggal_peminjaman'] = 'Tanggal peminjaman tidak boleh sebelum tanggal pengajuan.';
        } elseif ($kembali->lt($pinjam)) {
            $err['tanggal_rencana_kembali'] = 'Tanggal rencana kembali tidak boleh sebelum tanggal peminjaman.';
        }

        $barang = array_values($data['detail'] ?? []);
        $ruangan = array_values($data['ruangan'] ?? []);
        if ($barang === [] && $ruangan === []) {
            $err['detail'] = 'Pilih minimal satu alat/bahan atau ruangan.';
        }

        $ids = [];
        foreach ($barang as $b) {
            $id = (int) ($b['id_katalog'] ?? 0);
            if (isset($ids[$id])) {
                $err['detail'] = 'Satu katalog hanya boleh muncul sekali dalam satu transaksi.';
            }
            $ids[$id] = true;
            if ((int) ($b['jumlah'] ?? 0) < 1) {
                $err['detail'] = 'Jumlah harus lebih dari 0.';
            }
        }
        if ($ids && AlatBahan::whereIn('id_katalog', array_keys($ids))->count() !== count($ids)) {
            $err['detail'] = 'Ada katalog yang tidak ditemukan.';
        }

        $rid = [];
        foreach ($ruangan as $r) {
            $id = (int) ($r['id_ruangan'] ?? 0);
            if (isset($rid[$id])) {
                $err['ruangan'] = 'Satu ruangan hanya boleh muncul sekali dalam satu transaksi.';
            }
            $rid[$id] = true;
            $mulai = $this->tanggal($r['tanggal_mulai'] ?? null);
            $selesai = $this->tanggal($r['tanggal_selesai'] ?? null);
            if (! $mulai || ! $selesai || ! $mulai->lt($selesai)) {
                $err['ruangan'] = 'Tanggal mulai ruangan harus lebih awal dari tanggal selesai.';
            }
        }
        if ($rid && DB::table('ruangans')->whereIn('id_ruangan', array_keys($rid))->count() !== count($rid)) {
            $err['ruangan'] = 'Ada ruangan yang tidak ditemukan.';
        }

        if ($err) {
            $this->gagal($err);
        }

        return DB::transaction(function () use ($pengaju, $jenis, $emailDosen, $sekarang, $pinjam, $kembali, $data, $barang, $ruangan) {
            $p = Peminjaman::create([
                'email_peminjam' => $pengaju->email,
                'jenis_peminjaman' => $jenis,
                'email_dosen' => $emailDosen,
                'tanggal_pengajuan' => $sekarang,
                'tanggal_peminjaman' => $pinjam,
                'tanggal_rencana_kembali' => $kembali,
                'status' => 'menunggu',
                'keterangan' => $data['keterangan'] ?? null,
                'email_pemroses' => null,
            ]);
            foreach ($barang as $b) {
                $p->details()->create(['id_katalog' => (int) $b['id_katalog'], 'jumlah' => (int) $b['jumlah']]);
            }
            foreach ($ruangan as $r) {
                $p->ruangans()->create([
                    'id_ruangan' => (int) $r['id_ruangan'],
                    'tanggal_mulai' => $this->tanggal($r['tanggal_mulai']),
                    'tanggal_selesai' => $this->tanggal($r['tanggal_selesai']),
                    'keterangan' => $r['keterangan'] ?? null,
                ]);
            }

            return $p;
        });
    }

    /**
     * Setujui: cek ulang stok (dengan lock), cek konflik ruangan (JADWAL aktif + peminjaman lain yang sudah disetujui),
     * kurangi stok, lalu commit — semuanya satu transaksi. Dapat diproses Dosen atau Laboran.
     */
    public function setujui(int $idPeminjaman, string $emailPemroses): Peminjaman
    {
        $pemroses = $this->pastikanPemroses($emailPemroses);

        return DB::transaction(function () use ($idPeminjaman, $pemroses) {
            $p = Peminjaman::whereKey($idPeminjaman)->lockForUpdate()->firstOrFail();
            if ($p->status !== 'menunggu') {
                $this->gagal(['status' => 'Hanya peminjaman berstatus menunggu yang dapat diproses.']);
            }
            if ($p->email_peminjam === $pemroses->email) {
                $this->gagal(['email_pemroses' => 'Tidak dapat memproses pengajuan peminjaman yang diajukan sendiri.']);
            }
            $details = $p->details()->orderBy('id_katalog')->get();

            // Lock baris katalog (urut id agar tidak deadlock), lalu cek ulang stok terbaru.
            $katalog = AlatBahan::whereIn('id_katalog', $details->pluck('id_katalog'))->orderBy('id_katalog')->lockForUpdate()->get()->keyBy('id_katalog');
            $err = [];
            foreach ($details as $d) {
                $k = $katalog[$d->id_katalog];
                if ($k->stok < $d->jumlah) {
                    $err['stok'] = "Stok {$k->nama} tidak mencukupi (tersedia {$k->stok}, diminta {$d->jumlah}).";
                    break;
                }
            }
            foreach ($p->ruangans as $r) {
                if ($konflik = $this->konflikRuangan($r)) {
                    $err['ruangan'] = $konflik;
                    break;
                }
            }
            if ($err) {
                $this->gagal($err);
            }

            foreach ($details as $d) {
                // Guard "stok >= jumlah" pada UPDATE itu sendiri menjaga stok tidak pernah negatif walau tanpa lock.
                $n = AlatBahan::where('id_katalog', $d->id_katalog)->where('stok', '>=', $d->jumlah)->decrement('stok', $d->jumlah);
                if ($n !== 1) {
                    $this->gagal(['stok' => 'Stok berubah saat diproses. Silakan coba lagi.']);
                }
            }
            $p->update([
                'status' => 'disetujui',
                'email_pemroses' => $pemroses->email,
                'tanggal_persetujuan' => now(),
                'alasan_ditolak' => null,
            ]);

            return $p->refresh();
        });
    }

    /** Tolak pengajuan: status menjadi ditolak beserta alasan dari Dosen/Laboran; stok tidak diubah. */
    public function tolak(int $idPeminjaman, string $emailPemroses, ?string $alasan = null): Peminjaman
    {
        $pemroses = $this->pastikanPemroses($emailPemroses);
        $alasan = $alasan !== null ? trim(preg_replace('/\s+/u', ' ', $alasan)) : null;

        return DB::transaction(function () use ($idPeminjaman, $pemroses, $alasan) {
            $p = Peminjaman::whereKey($idPeminjaman)->lockForUpdate()->firstOrFail();
            if ($p->status !== 'menunggu') {
                $this->gagal(['status' => 'Hanya peminjaman berstatus menunggu yang dapat ditolak.']);
            }
            if ($p->email_peminjam === $pemroses->email) {
                $this->gagal(['email_pemroses' => 'Tidak dapat memproses pengajuan peminjaman yang diajukan sendiri.']);
            }
            $p->update([
                'status' => 'ditolak',
                'email_pemroses' => $pemroses->email,
                'tanggal_persetujuan' => now(),
                'alasan_ditolak' => $alasan ?: null,
            ]);

            return $p;
        });
    }

    /**
     * Status selesai ditentukan sistem, bukan dipilih manual. Hanya untuk transaksi berstatus disetujui:
     * - ada Alat        → semua jumlah Alat sudah dikembalikan (baik/rusak);
     * - ada Ruangan     → semua periode ruangan sudah berakhir;
     * - Bahan           → tidak punya kewajiban kembali (yang tidak dikembalikan dianggap terpakai);
     * - Bahan saja      → selesai setelah tanggal rencana kembali.
     */
    public function evaluasiStatus(Peminjaman $p, ?CarbonInterface $sekarang = null): bool
    {
        $sekarang = $sekarang ? Carbon::instance($sekarang) : now();
        $p->refresh();
        if ($p->status !== 'disetujui') {
            return false;
        }

        $punyaAlat = DB::table('peminjaman_details as d')->join('alat_bahans as k', 'k.id_katalog', '=', 'd.id_katalog')
            ->where('d.id_peminjaman', $p->id_peminjaman)->where('k.jenis', 'alat')->exists();
        $punyaRuangan = $p->ruangans()->exists();

        if ($punyaAlat) {
            $alatBelumKembali = DB::table('peminjaman_details as d')
                ->join('alat_bahans as k', 'k.id_katalog', '=', 'd.id_katalog')
                ->where('d.id_peminjaman', $p->id_peminjaman)->where('k.jenis', 'alat')
                ->whereRaw('d.jumlah > COALESCE((SELECT SUM(pd.jumlah_dikembalikan) FROM pengembalian_details pd
                            JOIN pengembalians pg ON pg.id_pengembalian = pd.id_pengembalian
                            WHERE pd.id_detail_peminjaman = d.id_detail AND pg.id_peminjaman = d.id_peminjaman), 0)')
                ->exists();
            if ($alatBelumKembali) {
                return false;
            }
        }
        if ($punyaRuangan && $p->ruangans()->where('tanggal_selesai', '>', $sekarang)->exists()) {
            return false;
        }
        if (! $punyaAlat && ! $punyaRuangan && $sekarang->lt($p->tanggal_rencana_kembali)) {
            return false; // bahan saja: menunggu rencana kembali
        }

        $p->update(['status' => 'selesai']);

        return true;
    }

    /** Evaluasi semua transaksi disetujui (dijadwalkan berkala). Mengembalikan jumlah transaksi yang menjadi selesai. */
    public function sinkronSelesai(?CarbonInterface $sekarang = null): int
    {
        $n = 0;
        foreach (Peminjaman::where('status', 'disetujui')->orderBy('id_peminjaman')->get() as $p) {
            $n += $this->evaluasiStatus($p, $sekarang) ? 1 : 0;
        }

        return $n;
    }

    /**
     * Catat pengembalian (partial return boleh) dan sesuaikan stok:
     * Alat baik → stok bertambah; Alat rusak → tidak; Bahan (kondisi NULL) → stok bertambah.
     *
     * @param  array<int,array{id_detail_peminjaman:int,jumlah_dikembalikan:int,kondisi?:?string,keterangan?:?string}>  $rows
     */
    public function kembalikan(int $idPeminjaman, string $emailPenerima, array $rows, ?string $keterangan = null): Pengembalian
    {
        $penerima = $this->pastikanPemroses($emailPenerima);

        return DB::transaction(function () use ($idPeminjaman, $penerima, $rows, $keterangan) {
            $p = Peminjaman::whereKey($idPeminjaman)->lockForUpdate()->firstOrFail();
            if ($p->status !== 'disetujui') {
                $this->gagal(['status' => 'Pengembalian hanya dapat dibuat untuk peminjaman berstatus disetujui.']);
            }
            if ($rows === []) {
                $this->gagal(['detail' => 'Isi minimal satu barang yang dikembalikan.']);
            }

            $details = PeminjamanDetail::where('id_peminjaman', $p->id_peminjaman)->orderBy('id_detail')->lockForUpdate()->get()->keyBy('id_detail');
            $katalog = AlatBahan::whereIn('id_katalog', $details->pluck('id_katalog'))->orderBy('id_katalog')->lockForUpdate()->get()->keyBy('id_katalog');
            $sudah = DB::table('pengembalian_details as pd')
                ->join('pengembalians as pg', 'pg.id_pengembalian', '=', 'pd.id_pengembalian')
                ->where('pg.id_peminjaman', $p->id_peminjaman)
                ->groupBy('pd.id_detail_peminjaman')
                ->selectRaw('pd.id_detail_peminjaman as id, SUM(pd.jumlah_dikembalikan) as total')
                ->pluck('total', 'id');

            $baru = [];       // akumulasi per detail dalam pengembalian ini (baris duplikat dijumlahkan)
            $tambahStok = []; // id_katalog => jumlah
            foreach ($rows as $row) {
                $idDetail = (int) ($row['id_detail_peminjaman'] ?? 0);
                $jumlah = (int) ($row['jumlah_dikembalikan'] ?? 0);
                $kondisi = ($row['kondisi'] ?? null) ?: null;
                $d = $details[$idDetail] ?? null;
                if (! $d) {
                    $this->gagal(['detail' => 'Detail pengembalian harus berasal dari detail peminjaman pada transaksi yang sama.']);
                }
                if ($jumlah < 1) {
                    $this->gagal(['jumlah_dikembalikan' => 'Jumlah dikembalikan harus lebih dari 0.']);
                }
                $item = $katalog[$d->id_katalog];
                $baru[$idDetail] = ($baru[$idDetail] ?? 0) + $jumlah;
                if ((int) ($sudah[$idDetail] ?? 0) + $baru[$idDetail] > $d->jumlah) {
                    $this->gagal(['jumlah_dikembalikan' => "Total pengembalian {$item->nama} melebihi jumlah yang dipinjam ({$d->jumlah})."]);
                }
                if ($item->jenis === 'alat') {
                    if (! in_array($kondisi, ['baik', 'rusak'], true)) {
                        $this->gagal(['kondisi' => "Kondisi {$item->nama} wajib dipilih: baik atau rusak."]);
                    }
                    if ($kondisi === 'baik') {
                        $tambahStok[$item->id_katalog] = ($tambahStok[$item->id_katalog] ?? 0) + $jumlah;
                    }
                } else {
                    if ($kondisi !== null) {
                        $this->gagal(['kondisi' => "Bahan {$item->nama} tidak memakai kondisi."]);
                    }
                    $tambahStok[$item->id_katalog] = ($tambahStok[$item->id_katalog] ?? 0) + $jumlah;
                }
            }

            $pg = Pengembalian::create([
                'id_peminjaman' => $p->id_peminjaman,
                'tanggal_pengembalian' => now(),
                'email_penerima' => $penerima->email,
                'keterangan' => $keterangan,
            ]);
            foreach ($rows as $row) {
                $pg->details()->create([
                    'id_detail_peminjaman' => (int) $row['id_detail_peminjaman'],
                    'jumlah_dikembalikan' => (int) $row['jumlah_dikembalikan'],
                    'kondisi' => ($row['kondisi'] ?? null) ?: null,
                    'keterangan' => $row['keterangan'] ?? null,
                ]);
            }
            foreach ($tambahStok as $idKatalog => $jml) {
                AlatBahan::where('id_katalog', $idKatalog)->increment('stok', $jml);
            }
            $this->evaluasiStatus($p);

            return $pg;
        });
    }

    /** Pesan konflik bila periode ruangan beririsan dengan JADWAL aktif atau peminjaman ruangan lain yang disetujui; null bila bebas. */
    public function konflikRuangan(PeminjamanRuangan $r): ?string
    {
        $mulai = Carbon::parse($r->tanggal_mulai);
        $selesai = Carbon::parse($r->tanggal_selesai);

        // 1) Peminjaman lain yang sudah disetujui (selesai = sudah pernah disetujui, tetap dihitung bila periodenya beririsan).
        $bentrok = PeminjamanRuangan::query()
            ->join('peminjamans as p', 'p.id_peminjaman', '=', 'peminjaman_ruangans.id_peminjaman')
            ->where('peminjaman_ruangans.id_ruangan', $r->id_ruangan)
            ->where('peminjaman_ruangans.id_peminjaman', '!=', $r->id_peminjaman)
            ->whereIn('p.status', ['disetujui', 'selesai'])
            ->where('peminjaman_ruangans.tanggal_mulai', '<', $selesai)   // batas menempel (10:00–10:00) tidak dianggap irisan
            ->where('peminjaman_ruangans.tanggal_selesai', '>', $mulai)
            ->exists();
        if ($bentrok) {
            return 'Ruangan bentrok dengan peminjaman lain yang sudah disetujui.';
        }

        // 2) Jadwal kuliah aktif (mingguan): periksa tiap tanggal pada rentang.
        $hari = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        $tgl = $mulai->copy()->startOfDay();
        $batas = $selesai->copy()->startOfDay();
        for ($i = 0; $tgl->lte($batas) && $i < 400; $i++, $tgl->addDay()) {
            $namaHari = $hari[$tgl->dayOfWeek];
            if ($namaHari === 'minggu') {
                continue;
            }
            $awal = $tgl->isSameDay($mulai) ? $mulai->format('H:i:s') : '00:00:00';
            $akhir = $tgl->isSameDay($selesai) ? $selesai->format('H:i:s') : '24:00:00';
            [$tahun, $semester] = self::periodeAkademik($tgl);
            $ada = Jadwal::where('id_ruangan', $r->id_ruangan)
                ->where('status', 'aktif')->where('hari', $namaHari)
                ->where('tahun_akademik', $tahun)->where('semester', $semester)
                ->where('jam_mulai', '<', $akhir)->where('jam_selesai', '>', $awal)
                ->exists();
            if ($ada) {
                return 'Ruangan bentrok dengan jadwal kuliah aktif.';
            }
        }

        return null;
    }

    /**
     * Tahun akademik & semester untuk sebuah tanggal. Asumsi kalender akademik:
     * Agu–Jan = ganjil, Feb–Jul = genap; tahun akademik mulai Agustus (mis. 2026/2027).
     *
     * @return array{0:string,1:string}
     */
    public static function periodeAkademik(CarbonInterface $tanggal): array
    {
        $bulan = (int) $tanggal->format('n');
        $tahun = (int) $tanggal->format('Y');
        $awal = $bulan >= 8 ? $tahun : $tahun - 1;

        return [$awal . '/' . ($awal + 1), ($bulan >= 8 || $bulan === 1) ? 'ganjil' : 'genap'];
    }

    /** Pemroses peminjaman: Dosen (persetujuan/pengembalian) atau Laboran/Admin. */
    private function pastikanPemroses(string $email): Akun
    {
        $akun = Akun::find($email);
        if (! $akun || ! in_array($akun->role, ['dosen', 'laboran'], true)) {
            $this->gagal(['email_pemroses' => 'Hanya Dosen dan Laboran yang dapat memproses peminjaman.']);
        }

        return $akun;
    }

    private function tanggal(mixed $v): ?Carbon
    {
        if ($v instanceof CarbonInterface) {
            return Carbon::instance($v);
        }
        if (! is_string($v) || trim($v) === '') {
            return null;
        }
        try {
            return Carbon::parse($v);
        } catch (\Throwable) {
            return null;
        }
    }

    private function gagal(array $errors): never
    {
        throw ValidationException::withMessages($errors);
    }
}
