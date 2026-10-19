<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\AlatBahan;
use App\Models\Jadwal;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\PeminjamanRuangan;
use App\Models\PengaturanOperasional;
use App\Models\PeriodeJadwal;
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
    /** Nama hari sesuai index Carbon format('w') (0 = minggu). */
    private const HARI = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

    /**
     * @param  array{jenis_peminjaman?:string, email_dosen?:?string, tanggal_peminjaman:mixed, tanggal_rencana_kembali:mixed, keterangan?:?string,
     *               detail?:array<int,array{id_katalog:int,jumlah:int}>, ruangan?:array<int,array{id_ruangan:int}>}  $data
     */
    public function ajukan(string $emailPengaju, array $data): Peminjaman
    {
        $sekarang = now();
        $err = [];

        $pengaju = Akun::find($emailPengaju);
        if (! $pengaju || ! in_array($pengaju->role, ['mahasiswa', 'dosen'], true)) {
            $this->gagal(['email_peminjam' => 'Hanya Mahasiswa dan Dosen yang dapat mengajukan peminjaman.']);
        }

        // Dosen: jenis peminjaman selalu "Pembelajaran" (otomatis, tanpa pilihan) dan tanpa dosen penanggung jawab.
        $roleDosen = $pengaju->role === 'dosen';

        if ($roleDosen) {
            $jenis = Peminjaman::JENIS_PEMBELAJARAN;
            $emailDosen = null;
        } else {
            $jenis = $data['jenis_peminjaman'] ?? null;
            $emailDosen = $data['email_dosen'] ?? null;
            if (! in_array($jenis, Peminjaman::JENIS_MAHASISWA, true)) {
                $err['jenis_peminjaman'] = 'Jenis peminjaman tidak valid.';
            } elseif ($jenis === 'pribadi' && $emailDosen !== null && $emailDosen !== '') {
                $err['email_dosen'] = 'Peminjaman mandiri tidak boleh memiliki dosen penanggung jawab.';
            } elseif ($jenis === 'atas_dosen') {
                if (! $emailDosen) {
                    $err['email_dosen'] = 'Dosen penanggung jawab wajib dipilih.';
                } elseif (! Akun::where('email', $emailDosen)->where('role', 'dosen')->exists()) {
                    $err['email_dosen'] = 'Dosen harus berupa akun dengan role dosen.';
                }
            }
            if ($jenis === 'pribadi') {
                $emailDosen = null;
            }
        }

        $pinjam = $this->tanggal($data['tanggal_peminjaman'] ?? null);
        $kembali = $this->tanggal($data['tanggal_rencana_kembali'] ?? null);
        if (! $pinjam || ! $kembali) {
            $err['tanggal_peminjaman'] = 'Tanggal peminjaman dan rencana kembali wajib diisi dengan format yang valid.';
        } else {
            if ($pinjam->lt($sekarang->copy()->startOfMinute())) {
                $err['tanggal_peminjaman'] = 'Tanggal peminjaman tidak boleh sebelum tanggal pengajuan.';
            }
            if ($kembali->lte($pinjam)) {
                $err['tanggal_rencana_kembali'] = 'Waktu selesai harus setelah waktu mulai.';
            }
            // Semua peminjaman (semua role & jenis sumber daya) maksimal satu hari operasional; dicek bersama urutan waktu.
            if (($salah = $this->cekSatuHariOperasional($pinjam, $kembali)) !== null) {
                $err[$salah[0]] ??= $salah[1];
            }
        }

        $barang = array_values($data['detail'] ?? []);
        $ruangan = array_values($data['ruangan'] ?? []);
        if ($barang === [] && $ruangan === []) {
            $err['detail'] = 'Pilih minimal satu ruangan, alat, atau bahan yang ingin dipinjam sebelum mengajukan peminjaman.';
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
                    'tanggal_mulai' => $pinjam,
                    'tanggal_selesai' => $kembali,
                    'keterangan' => null,
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
            $this->pastikanBerwenang($p, $pemroses);
            // Tahap 1 (SRS D.8): persetujuan Dosen hanya meneruskan ke Laboran; stok & ruangan baru diputuskan Laboran.
            if ($pemroses->role === 'dosen') {
                $p->update(['tanggal_persetujuan_dosen' => now()]);

                return $p->refresh();
            }
            $details = $p->details()->orderBy('id_katalog')->get();

            // Kunci baris ruangan (urut id) agar dua persetujuan bersamaan untuk ruangan yang sama diproses bergantian.
            DB::table('ruangans')->whereIn('id_ruangan', $p->ruangans()->pluck('id_ruangan'))->orderBy('id_ruangan')->lockForUpdate()->get();

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
            $this->pastikanBerwenang($p, $pemroses);
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
     * Status selesai otomatis untuk transaksi TANPA ruangan yang berstatus disetujui:
     * - ada Alat        → semua jumlah Alat sudah dikembalikan (baik/rusak);
     * - Bahan           → tidak punya kewajiban kembali (yang tidak dikembalikan dianggap terpakai);
     * - Bahan saja      → selesai setelah tanggal rencana kembali.
     * Transaksi yang melibatkan ruangan hanya selesai lewat selesaikan() (tombol Selesai).
     */
    public function evaluasiStatus(Peminjaman $p, ?CarbonInterface $sekarang = null): bool
    {
        $sekarang = $sekarang ? Carbon::instance($sekarang) : now();
        $p->refresh();
        if ($p->status !== 'disetujui') {
            return false;
        }

        if ($p->ruangans()->exists()) {
            return false;
        }
        $punyaAlat = DB::table('peminjaman_details as d')->join('alat_bahans as k', 'k.id_katalog', '=', 'd.id_katalog')
            ->where('d.id_peminjaman', $p->id_peminjaman)->where('k.jenis', 'alat')->exists();

        if ($punyaAlat && $this->alatBelumKembali($p)) {
            return false;
        }
        if (! $punyaAlat && $sekarang->lt($p->tanggal_rencana_kembali)) {
            return false; // bahan saja: menunggu rencana kembali
        }

        $p->update(['status' => 'selesai']);

        return true;
    }

    /** True bila ada Alat pada transaksi yang belum seluruhnya dikembalikan. */
    private function alatBelumKembali(Peminjaman $p): bool
    {
        return DB::table('peminjaman_details as d')
            ->join('alat_bahans as k', 'k.id_katalog', '=', 'd.id_katalog')
            ->where('d.id_peminjaman', $p->id_peminjaman)->where('k.jenis', 'alat')
            ->whereRaw('d.jumlah > COALESCE((SELECT SUM(pd.jumlah_dikembalikan) FROM pengembalian_details pd
                        JOIN pengembalians pg ON pg.id_pengembalian = pd.id_pengembalian
                        WHERE pd.id_detail_peminjaman = d.id_detail AND pg.id_peminjaman = d.id_peminjaman), 0)')
            ->exists();
    }

    /**
     * Batalkan peminjaman yang melibatkan ruangan oleh peminjam sendiri, paling lambat pukul 23.59 H-1
     * (berdasarkan tanggal kalender). Bila sudah disetujui, stok alat/bahan dikembalikan.
     */
    public function batalkan(int $idPeminjaman, string $emailPeminjam, ?CarbonInterface $sekarang = null): Peminjaman
    {
        $sekarang = $sekarang ? Carbon::instance($sekarang) : now();

        return DB::transaction(function () use ($idPeminjaman, $emailPeminjam, $sekarang) {
            $p = Peminjaman::whereKey($idPeminjaman)->lockForUpdate()->firstOrFail();
            if ($p->email_peminjam !== $emailPeminjam) {
                $this->gagal(['status' => 'Hanya peminjam yang dapat membatalkan peminjaman ini.']);
            }
            if (! in_array($p->status, ['menunggu', 'disetujui'], true)) {
                $this->gagal(['status' => 'Peminjaman dengan status ini tidak dapat dibatalkan.']);
            }
            if (! $p->melibatkanRuangan()) {
                $this->gagal(['status' => 'Hanya peminjaman yang melibatkan ruangan yang dapat dibatalkan.']);
            }
            if (! $p->bisaDibatalkanOleh($emailPeminjam, $sekarang)) {
                $this->gagal(['status' => 'Pembatalan hanya dapat dilakukan sampai pukul 23.59 pada H-1 tanggal pelaksanaan.']);
            }

            if ($p->status === 'disetujui') {
                $details = $p->details()->orderBy('id_katalog')->get();
                AlatBahan::whereIn('id_katalog', $details->pluck('id_katalog'))->orderBy('id_katalog')->lockForUpdate()->get();
                foreach ($details as $d) {
                    AlatBahan::where('id_katalog', $d->id_katalog)->increment('stok', $d->jumlah); // stok dipotong saat disetujui
                }
            }
            $p->update(['status' => 'dibatalkan']);

            return $p->refresh();
        });
    }

    /**
     * Tombol Selesai untuk peminjaman yang melibatkan ruangan. Pihak yang berhak: lihat Peminjaman::bisaDiselesaikanOleh().
     * Tidak ada syarat waktu (boleh kapan saja); bila ada Alat, seluruhnya harus sudah dikembalikan.
     */
    public function selesaikan(int $idPeminjaman, string $email): Peminjaman
    {
        $akun = Akun::find($email) ?? $this->gagal(['status' => 'Akun tidak ditemukan.']);

        return DB::transaction(function () use ($idPeminjaman, $akun) {
            $p = Peminjaman::whereKey($idPeminjaman)->lockForUpdate()->firstOrFail();
            if ($p->status !== 'disetujui') {
                $this->gagal(['status' => 'Hanya peminjaman berstatus disetujui yang dapat diselesaikan.']);
            }
            if (! $p->bisaDiselesaikanOleh($akun->email, $akun->role)) {
                $this->gagal(['status' => 'Anda tidak berwenang menyelesaikan peminjaman ini.']);
            }
            if ($pesan = $this->hambatanSelesai($p)) {
                $this->gagal(['status' => $pesan]);
            }
            $p->update(['status' => 'selesai']);

            return $p->refresh();
        });
    }

    /** Alasan Selesai belum boleh ditekan (alat belum kembali); null bila boleh. Tanpa syarat waktu. Dipakai tombol & backend. */
    public function hambatanSelesai(Peminjaman $p): ?string
    {
        if ($this->alatBelumKembali($p)) {
            return 'Masih ada alat yang belum dikembalikan. Catat pengembalian terlebih dahulu.';
        }

        return null;
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
        if ($penerima->role !== 'laboran') {
            $this->gagal(['email_pemroses' => 'Pengembalian hanya dapat dicatat oleh Laboran.']);
        }

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
                // Jadwal hanya berlaku pada tanggal di dalam periodenya.
                ->where(fn ($q) => $q->whereNull('id_periode')->orWhereIn('id_periode', PeriodeJadwal::query()
                    ->where('tanggal_mulai', '<=', $tgl->toDateString())->where('tanggal_berakhir', '>=', $tgl->toDateString())->select('id_periode')))
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

        return [$awal.'/'.($awal + 1), ($bulan >= 8 || $bulan === 1) ? 'ganjil' : 'genap'];
    }

    /**
     * Aturan jam kerja laboratorium dari Pengaturan Operasional (dapat diubah lewat menu tersebut).
     *
     * @return array{jam_buka:string, jam_tutup:string, hari:array<int,string>}
     */
    public function aturanJamKerja(): array
    {
        $pengaturan = PengaturanOperasional::ambil();

        return [
            'jam_buka' => substr((string) $pengaturan->jam_buka, 0, 5),
            'jam_tutup' => substr((string) $pengaturan->jam_tutup, 0, 5),
            'hari' => $pengaturan->hariAktif(),
        ];
    }

    /**
     * Waktu paling awal yang masih boleh dipilih Dosen: hari operasional pertama yang jamnya
     * masih buka dan waktu tersebut belum lewat. null bila tidak ditemukan dalam 60 hari.
     */
    public function waktuPalingAwal(?CarbonInterface $sekarang = null): ?Carbon
    {
        $sekarang = ($sekarang ? Carbon::instance($sekarang) : now())->copy()->startOfMinute();
        $aturan = $this->aturanJamKerja();
        [$jamBuka, $menitBuka, $detikBuka] = $this->pecahJam($aturan['jam_buka']);
        [$jamTutup, $menitTutup, $detikTutup] = $this->pecahJam($aturan['jam_tutup']);
        $hari = $sekarang->copy()->startOfDay();

        for ($i = 0; $i < 60; $i++, $hari->addDay()) {
            if (! in_array(self::HARI[(int) $hari->format('w')], $aturan['hari'], true)) {
                continue;
            }
            $buka = $hari->copy()->setTime($jamBuka, $menitBuka, $detikBuka);
            $tutup = $hari->copy()->setTime($jamTutup, $menitTutup, $detikTutup);
            $mulai = $sekarang->lt($buka) ? $buka : $sekarang->copy();
            if ($mulai->lt($tutup)) {
                return $mulai;
            }
        }

        return null;
    }

    /**
     * Aturan satu hari operasional: mulai dan selesai harus pada tanggal yang sama, pada hari operasional,
     * dan di dalam jam buka–tutup (selesai tepat pada jam tutup masih boleh). null bila memenuhi.
     *
     * @return array{0:string,1:string}|null [nama field, pesan]
     */
    public function cekSatuHariOperasional(CarbonInterface $mulai, CarbonInterface $selesai): ?array
    {
        $aturan = $this->aturanJamKerja();
        $rentang = $aturan['jam_buka'].' – '.$aturan['jam_tutup'];
        if (! $mulai->isSameDay($selesai)) {
            return ['tanggal_rencana_kembali', "Peminjaman hanya berlaku dalam satu hari operasional: mulai dan selesai harus pada tanggal yang sama (jam operasional {$rentang})."];
        }
        if (! in_array(self::HARI[(int) $mulai->format('w')], $aturan['hari'], true)) {
            return ['tanggal_peminjaman', 'Peminjaman hanya dapat dijadwalkan pada hari operasional laboratorium ('.implode(', ', $aturan['hari']).').'];
        }
        if ($mulai->format('H:i') < $aturan['jam_buka'] || $mulai->format('H:i') >= $aturan['jam_tutup']) {
            return ['tanggal_peminjaman', "Waktu mulai harus berada dalam jam operasional laboratorium ({$rentang})."];
        }
        if ($selesai->format('H:i') > $aturan['jam_tutup']) {
            return ['tanggal_rencana_kembali', "Waktu selesai tidak boleh melewati jam tutup laboratorium ({$rentang})."];
        }

        return null;
    }

    /** "08:00" | "08:00:00" -> [8, 0, 0]. */
    private function pecahJam(string $jam): array
    {
        $bagian = array_map('intval', explode(':', $jam));

        return [$bagian[0] ?? 0, $bagian[1] ?? 0, $bagian[2] ?? 0];
    }

    /** Kewenangan memutuskan menurut tahap alur persetujuan (Dosen tahap 1, Laboran tahap akhir). */
    private function pastikanBerwenang(Peminjaman $p, Akun $akun): void
    {
        if ($p->bisaDiprosesOleh($akun->email, $akun->role)) {
            return;
        }
        if ($akun->role === 'laboran' && $p->menungguDosen()) {
            $this->gagal(['status' => 'Pengajuan ini menunggu persetujuan Dosen penanggung jawab terlebih dahulu.']);
        }
        if ($akun->role === 'dosen' && $p->diteruskanKeLaboran() && $p->email_dosen === $akun->email) {
            $this->gagal(['status' => 'Pengajuan ini sudah Anda teruskan ke Laboran.']);
        }
        $this->gagal(['email_pemroses' => 'Anda tidak berwenang memproses pengajuan ini.']);
    }

    /** Pemroses peminjaman: Dosen (persetujuan, dibatasi bisaDiprosesOleh) atau Laboran/Admin. */
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
