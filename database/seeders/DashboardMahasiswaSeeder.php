<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\AlatBahan;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\KelasMahasiswa;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\PeminjamanRuangan;
use App\Models\Pengembalian;
use App\Models\PengembalianDetail;
use App\Models\Prodi;
use App\Models\Ruangan;
use App\Models\Satuan;
use App\Services\PeminjamanService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data contoh khusus Dashboard Mahasiswa. Idempoten (firstOrCreate), TIDAK dipanggil DatabaseSeeder.
 * Jalankan: php artisan db:seed --class=DashboardMahasiswaSeeder (hanya local/testing).
 *
 * Akun yang dibuat (password mengikuti konvensi DatabaseSeeder, disimpan apa adanya):
 * - dosen1@silab.test  / dosen123      (Dosen, pemroses pengajuan demo)
 * - mhs1@silab.test    / mahasiswa123   (NIM 24110001, semua kasus peminjaman)
 * - mhs2@silab.test    / mahasiswa123   (NIM 24110002, data berbeda untuk uji isolasi)
 *
 * Isi: 1 prodi, 2 kelas, 2 ruangan lab, 3 satuan, 6 katalog (alat & bahan), keanggotaan kelas
 * periode berjalan, 5 jadwal aktif (satu untuk hari ini) + 1 dibatalkan, serta peminjaman mhs1 yang
 * mencakup menunggu, disetujui (belum jatuh tempo), disetujui terlambat, ditolak ber-alasan, selesai
 * dengan pengembalian parsial (satu baris kondisi rusak), atas_dosen, dan peminjaman ber-ruangan.
 * Stok dikurangi saat disetujui/selesai dan ditambah kembali saat dikembalikan.
 *
 * Catatan: jadwal "untuk hari ini" dibuat hanya bila hari ini adalah hari kuliah (senin–sabtu);
 * jadwal laboratorium tidak memakai hari Minggu (enum jadwals).
 */
class DashboardMahasiswaSeeder extends Seeder
{
    private const GAMBAR = '69be8357-025d-498e-bc3e-e4f18a03c7c9.png';

    /** Hari kerja laboratorium (Jadwal::HARI) diawali hari ini untuk penempatan jadwal. */
    private const HARI_URUT = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        DB::transaction(function () {
            [$tahun, $semester] = PeminjamanService::periodeAkademik(now());

            $prodi = Prodi::firstOrCreate(['nama_prodi' => 'Teknik Informatika']);
            $kelasA = Kelas::firstOrCreate(['id_prodi' => $prodi->id_prodi, 'nama_kelas' => 'TI-2A']);
            $kelasB = Kelas::firstOrCreate(['id_prodi' => $prodi->id_prodi, 'nama_kelas' => 'TI-2B']);
            $labJaringan = Ruangan::firstOrCreate(['nama_ruangan' => 'Lab Jaringan']);
            $labBasis = Ruangan::firstOrCreate(['nama_ruangan' => 'Lab Basis Data']);
            $unit = Satuan::firstOrCreate(['nama_satuan' => 'unit']);
            $meter = Satuan::firstOrCreate(['nama_satuan' => 'meter']);
            $botol = Satuan::firstOrCreate(['nama_satuan' => 'botol']);

            $katalog = $this->katalog($labJaringan, $labBasis, $unit, $meter, $botol);
            $dosen = $this->dosen();
            $mhs1 = $this->mahasiswa('24110001', 'mhs1@silab.test', 'Rina Oktaviani', $prodi);
            $mhs2 = $this->mahasiswa('24110002', 'mhs2@silab.test', 'Bima Saputra', $prodi);

            KelasMahasiswa::firstOrCreate(
                ['nim' => $mhs1->nim, 'tahun_akademik' => $tahun, 'semester' => $semester],
                ['id_kelas' => $kelasA->id_kelas]
            );
            KelasMahasiswa::firstOrCreate(
                ['nim' => $mhs2->nim, 'tahun_akademik' => $tahun, 'semester' => $semester],
                ['id_kelas' => $kelasB->id_kelas]
            );

            $this->jadwal($dosen, $kelasA, $kelasB, $labJaringan, $labBasis, $tahun, $semester);
            $this->peminjamanMhs1($mhs1, $dosen, $katalog, $labJaringan);
            $this->peminjamanMhs2($mhs2, $katalog);
        });
    }

    /** @return array<string,AlatBahan> */
    private function katalog(Ruangan $labJaringan, Ruangan $labBasis, Satuan $unit, Satuan $meter, Satuan $botol): array
    {
        $baris = [
            ['nama' => 'Oscilloscope 100MHz', 'jenis' => 'alat', 'satuan' => $unit, 'stok' => 4, 'harga' => 3500000, 'ruangan' => $labJaringan],
            ['nama' => 'Multimeter Digital', 'jenis' => 'alat', 'satuan' => $unit, 'stok' => 8, 'harga' => 275000, 'ruangan' => $labJaringan],
            ['nama' => 'Switch Jaringan 24 Port', 'jenis' => 'alat', 'satuan' => $unit, 'stok' => 3, 'harga' => 1250000, 'ruangan' => $labJaringan],
            ['nama' => 'Kabel UTP Cat6', 'jenis' => 'bahan', 'satuan' => $meter, 'stok' => 120, 'harga' => 8000, 'ruangan' => $labBasis],
            ['nama' => 'Larutan Pembersih Kontak', 'jenis' => 'bahan', 'satuan' => $botol, 'stok' => 15, 'harga' => 45000, 'ruangan' => $labBasis],
            ['nama' => 'Tinta Printer Lab', 'jenis' => 'bahan', 'satuan' => $botol, 'stok' => 10, 'harga' => 95000, 'ruangan' => $labBasis],
        ];
        $hasil = [];
        foreach ($baris as $b) {
            $hasil[$b['nama']] = AlatBahan::firstOrCreate(['nama' => $b['nama']], [
                'jenis' => $b['jenis'], 'id_satuan' => $b['satuan']->id_satuan, 'stok' => $b['stok'],
                'harga' => $b['harga'], 'id_ruangan' => $b['ruangan']->id_ruangan, 'gambar' => self::GAMBAR,
                'keterangan' => 'Katalog contoh dashboard mahasiswa.',
            ]);
        }

        return $hasil;
    }

    private function dosen(): Dosen
    {
        Akun::firstOrCreate(['email' => 'dosen1@silab.test'], ['password' => 'dosen123', 'role' => 'dosen']);

        return Dosen::firstOrCreate(['nuptk_nidn' => 'D20260001'], [
            'nama' => 'Dr. Surya Wijaya, M.Kom.', 'id_prodi' => Prodi::firstOrCreate(['nama_prodi' => 'Teknik Informatika'])->id_prodi,
            'email' => 'dosen1@silab.test', 'no_whatsapp' => null,
        ]);
    }

    private function mahasiswa(string $nim, string $email, string $nama, Prodi $prodi): Mahasiswa
    {
        Akun::firstOrCreate(['email' => $email], ['password' => 'mahasiswa123', 'role' => 'mahasiswa']);

        return Mahasiswa::firstOrCreate(['nim' => $nim], [
            'nama' => $nama, 'id_prodi' => $prodi->id_prodi, 'email' => $email, 'no_whatsapp' => null,
        ]);
    }

    /** 5 jadwal aktif (satu pada hari ini) + 1 dibatalkan, tersebar pada beberapa hari. */
    private function jadwal(Dosen $dosen, Kelas $kelasA, Kelas $kelasB, Ruangan $labJaringan, Ruangan $labBasis, string $tahun, string $semester): void
    {
        $hariIni = self::HARI_URUT[(int) now()->format('w')];
        $sisa = array_values(array_filter(Jadwal::HARI, fn ($h) => $h !== $hariIni));
        $slot = [
            ['08:00:00', '09:40:00', $labJaringan],
            ['09:00:00', '10:40:00', $labBasis],
            ['10:00:00', '11:40:00', $labJaringan],
            ['13:00:00', '14:40:00', $labBasis],
            ['15:00:00', '16:30:00', $labJaringan],
        ];
        $baris = [];
        if (in_array($hariIni, Jadwal::HARI, true)) {
            $baris[] = [$hariIni, $kelasA, 3, 'aktif']; // jadwal untuk hari ini
        }
        foreach ([0, 1, 2] as $i) { // 3 jadwal aktif kelas A pada hari lain
            $baris[] = [$sisa[$i], $kelasA, $i, 'aktif'];
        }
        $baris[] = [$sisa[3], $kelasB, 4, 'aktif'];
        $baris[] = [$sisa[4], $kelasA, 0, 'dibatalkan'];

        foreach ($baris as [$hari, $kelas, $i, $status]) {
            Jadwal::firstOrCreate(
                ['id_kelas' => $kelas->id_kelas, 'hari' => $hari, 'jam_mulai' => $slot[$i][0], 'tahun_akademik' => $tahun, 'semester' => $semester],
                ['nuptk_nidn' => $dosen->nuptk_nidn, 'id_ruangan' => $slot[$i][2]->id_ruangan, 'jam_selesai' => $slot[$i][1],
                    'status' => $status]
            );
        }
    }

    /** Semua kasus status peminjaman untuk mahasiswa 1 (stok konsisten dengan aturan bisnis). */
    private function peminjamanMhs1(Mahasiswa $mhs, Dosen $dosen, array $k, Ruangan $ruangan): void
    {
        $kini = now();
        $kirim = fn (string $kasus, array $isi) => Peminjaman::firstOrCreate(
            ['email_peminjam' => $mhs->email, 'keterangan' => $kasus],
            $isi + ['email_peminjam' => $mhs->email, 'email_pemroses' => null, 'keterangan' => $kasus]
        );

        // 1) menunggu: stok belum dipotong
        $p = $kirim('Dashboard demo: menunggu praktikum jaringan', [
            'jenis_peminjaman' => 'pribadi', 'email_dosen' => null,
            'tanggal_pengajuan' => $kini->copy()->subDays(2), 'tanggal_peminjaman' => $kini->copy()->addDay()->setTime(8, 0),
            'tanggal_rencana_kembali' => $kini->copy()->addDays(3)->setTime(16, 0), 'status' => 'menunggu',
        ]);
        $this->detail($p, $k['Oscilloscope 100MHz'], 1);

        // 2) disetujui, belum jatuh tempo: stok berkurang
        $p = $kirim('Dashboard demo: disetujui belum jatuh tempo', [
            'jenis_peminjaman' => 'pribadi', 'email_dosen' => null,
            'tanggal_pengajuan' => $kini->copy()->subDays(2), 'tanggal_peminjaman' => $kini->copy()->subDay()->setTime(9, 0),
            'tanggal_rencana_kembali' => $kini->copy()->addDays(2)->setTime(16, 0), 'status' => 'disetujui',
            'email_pemroses' => $dosen->email, 'tanggal_persetujuan' => $kini->copy()->subDay(),
        ]);
        $this->detail($p, $k['Multimeter Digital'], 2);

        // 3) disetujui & terlambat: alat belum dikembalikan, stok berkurang
        $p = $kirim('Dashboard demo: disetujui terlambat', [
            'jenis_peminjaman' => 'pribadi', 'email_dosen' => null,
            'tanggal_pengajuan' => $kini->copy()->subDays(9), 'tanggal_peminjaman' => $kini->copy()->subDays(8)->setTime(8, 0),
            'tanggal_rencana_kembali' => $kini->copy()->subDays(2)->setTime(16, 0), 'status' => 'disetujui',
            'email_pemroses' => $dosen->email, 'tanggal_persetujuan' => $kini->copy()->subDays(8),
        ]);
        $this->detail($p, $k['Switch Jaringan 24 Port'], 1);

        // 4) ditolak ber-alasan: stok tidak berubah
        $p = $kirim('Dashboard demo: ditolak dengan alasan', [
            'jenis_peminjaman' => 'pribadi', 'email_dosen' => null,
            'tanggal_pengajuan' => $kini->copy()->subDays(4), 'tanggal_peminjaman' => $kini->copy()->addDays(4)->setTime(8, 0),
            'tanggal_rencana_kembali' => $kini->copy()->addDays(5)->setTime(16, 0), 'status' => 'ditolak',
            'email_pemroses' => $dosen->email, 'tanggal_persetujuan' => $kini->copy()->subDays(3),
            'alasan_ditolak' => 'Stok dipakai praktikum lain pada tanggal tersebut.',
        ]);
        $this->detail($p, $k['Kabel UTP Cat6'], 10);

        // 5) selesai: pengembalian parsial (alat 1 baik + 1 rusak, bahan sebagian) -> stok kembali sebagian
        $p = $kirim('Dashboard demo: selesai dengan pengembalian parsial', [
            'jenis_peminjaman' => 'pribadi', 'email_dosen' => null,
            'tanggal_pengajuan' => $kini->copy()->subDays(10), 'tanggal_peminjaman' => $kini->copy()->subDays(9)->setTime(8, 0),
            'tanggal_rencana_kembali' => $kini->copy()->subDays(7)->setTime(16, 0), 'status' => 'selesai',
            'email_pemroses' => $dosen->email, 'tanggal_persetujuan' => $kini->copy()->subDays(9),
        ]);
        $this->detail($p, $k['Multimeter Digital'], 2);
        $this->detail($p, $k['Kabel UTP Cat6'], 20);
        $this->pengembalianParsial($p, $dosen, [
            ['nama' => 'Multimeter Digital', 'jumlah' => 1, 'kondisi' => 'baik'],
            ['nama' => 'Multimeter Digital', 'jumlah' => 1, 'kondisi' => 'rusak'],
            ['nama' => 'Kabel UTP Cat6', 'jumlah' => 12, 'kondisi' => null],
        ]);

        // 6) atas_dosen, disetujui, jatuh tempo masih dekat
        $p = $kirim('Dashboard demo: peminjaman atas dosen', [
            'jenis_peminjaman' => 'atas_dosen', 'email_dosen' => $dosen->email,
            'tanggal_pengajuan' => $kini->copy()->subDay(), 'tanggal_peminjaman' => $kini->copy()->addDay()->setTime(10, 0),
            'tanggal_rencana_kembali' => $kini->copy()->addDays(4)->setTime(16, 0), 'status' => 'disetujui',
            'email_pemroses' => $dosen->email, 'tanggal_persetujuan' => $kini->copy()->subHours(12),
        ]);
        $this->detail($p, $k['Oscilloscope 100MHz'], 1);

        // 7) memuat ruangan (tanpa barang): stok tidak berubah
        $p = $kirim('Dashboard demo: peminjaman ruangan', [
            'jenis_peminjaman' => 'pribadi', 'email_dosen' => null,
            'tanggal_pengajuan' => $kini->copy()->subDays(3), 'tanggal_peminjaman' => $kini->copy()->subDay()->setTime(8, 0),
            'tanggal_rencana_kembali' => $kini->copy()->addDays(2)->setTime(16, 0), 'status' => 'disetujui',
            'email_pemroses' => $dosen->email, 'tanggal_persetujuan' => $kini->copy()->subDays(2),
        ]);
        PeminjamanRuangan::firstOrCreate(
            ['id_peminjaman' => $p->id_peminjaman, 'id_ruangan' => $ruangan->id_ruangan],
            ['tanggal_mulai' => $kini->copy()->addDay()->setTime(8, 0), 'tanggal_selesai' => $kini->copy()->addDay()->setTime(12, 0),
                'keterangan' => 'Dashboard demo: booking ruang praktikum.']
        );
    }

    /** Data mahasiswa 2 berbeda (uji isolasi: tidak boleh terlihat di dashboard mahasiswa 1). */
    private function peminjamanMhs2(Mahasiswa $mhs, array $k): void
    {
        $kini = now();
        $p = Peminjaman::firstOrCreate(
            ['email_peminjam' => $mhs->email, 'keterangan' => 'Dashboard demo mhs2: menunggu bahan praktikum'],
            ['email_peminjam' => $mhs->email, 'jenis_peminjaman' => 'pribadi', 'email_dosen' => null,
                'tanggal_pengajuan' => $kini->copy()->subDay(), 'tanggal_peminjaman' => $kini->copy()->addDays(2)->setTime(8, 0),
                'tanggal_rencana_kembali' => $kini->copy()->addDays(4)->setTime(16, 0), 'status' => 'menunggu',
                'email_pemroses' => null, 'keterangan' => 'Dashboard demo mhs2: menunggu bahan praktikum']
        );
        $this->detail($p, $k['Tinta Printer Lab'], 1);
    }

    /** Tambah detail bila belum ada; stok hanya dipotong saat baris detail baru dibuat (aman diulang). */
    private function detail(Peminjaman $p, AlatBahan $k, int $jumlah): void
    {
        $row = PeminjamanDetail::firstOrCreate(
            ['id_peminjaman' => $p->id_peminjaman, 'id_katalog' => $k->id_katalog],
            ['jumlah' => $jumlah]
        );
        if ($row->wasRecentlyCreated && in_array($p->status, ['disetujui', 'selesai'], true)) {
            AlatBahan::whereKey($k->id_katalog)->decrement('stok', $jumlah);
        }
    }

    /**
     * Pengembalian parsial untuk peminjaman "selesai": alat baik/rusak (rusak tidak menambah stok),
     * bahan hanya sebagian (sisanya dianggap terpakai).
     *
     * @param  array<int,array{nama:string,jumlah:int,kondisi:?string}>  $baris
     */
    private function pengembalianParsial(Peminjaman $p, Dosen $dosen, array $baris): void
    {
        $pg = Pengembalian::firstOrCreate(['id_peminjaman' => $p->id_peminjaman], [
            'tanggal_pengembalian' => now()->copy()->subDays(7), 'email_penerima' => $dosen->email,
            'keterangan' => 'Dashboard demo: pengembalian parsial, satu barang rusak.',
        ]);
        if (! $pg->wasRecentlyCreated) {
            return;
        }
        foreach ($baris as $b) {
            $detail = PeminjamanDetail::where('id_peminjaman', $p->id_peminjaman)
                ->where('id_katalog', AlatBahan::where('nama', $b['nama'])->firstOrFail()->id_katalog)
                ->firstOrFail();
            PengembalianDetail::create([
                'id_pengembalian' => $pg->id_pengembalian, 'id_detail_peminjaman' => $detail->id_detail,
                'jumlah_dikembalikan' => $b['jumlah'], 'kondisi' => $b['kondisi'], 'keterangan' => null,
            ]);
            if ($b['kondisi'] !== 'rusak') { // alat rusak tidak mengembalikan stok; bahan baik menambah stok
                AlatBahan::whereKey($detail->id_katalog)->increment('stok', $b['jumlah']);
            }
        }
    }
}
