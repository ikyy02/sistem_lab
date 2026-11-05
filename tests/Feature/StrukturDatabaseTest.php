<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\AlatBahan;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use App\Models\PeminjamanDetail;
use App\Models\PeminjamanRuangan;
use App\Models\Pengembalian;
use App\Models\PengembalianDetail;
use App\Models\Prodi;
use App\Models\Kelas;
use App\Models\Ruangan;
use App\Models\Satuan;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class StrukturDatabaseTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private const TABEL = [
        'akuns', 'mahasiswas', 'dosens', 'staff_prodis', 'laborans', 'prodis', 'kelas', 'satuans', 'ruangans',
        'alat_bahans', 'peminjamans', 'peminjaman_details', 'peminjaman_ruangans', 'pengembalians', 'pengembalian_details', 'jadwals',
    ];

    public function test_semua_16_tabel_ada_dan_tabel_terlarang_tidak_ada(): void
    {
        foreach (self::TABEL as $t) {
            $this->assertTrue(Schema::hasTable($t), "Tabel {$t} harus ada");
        }
        $this->assertCount(16, self::TABEL);
        foreach (['users', 'roles', 'kategoris', 'ahp', 'saw', 'ranking', 'master_options', 'stok_histori', 'unit_alat', 'status_pengembalian'] as $t) {
            $this->assertFalse(Schema::hasTable($t), "Tabel {$t} tidak boleh ada");
        }
    }

    public function test_kolom_terlarang_tidak_ada_dan_kolom_wajib_ada(): void
    {
        foreach (['kategori', 'kondisi', 'kode_unit', 'jumlah_unit', 'satuan', 'per_unit', 'harga_total'] as $c) {
            $this->assertFalse(Schema::hasColumn('alat_bahans', $c), "alat_bahans.{$c} tidak boleh ada");
        }
        $this->assertTrue(Schema::hasColumns('alat_bahans', ['id_katalog', 'nama', 'jenis', 'id_satuan', 'stok', 'harga', 'id_ruangan', 'gambar', 'keterangan']));
        $this->assertFalse(Schema::hasColumn('mahasiswas', 'kelas'));
        $this->assertFalse(Schema::hasColumn('laborans', 'id_prodi'));
        $this->assertFalse(Schema::hasColumn('jadwals', 'mata_kuliah'));
        $this->assertFalse(Schema::hasColumn('mahasiswas', 'password'));
        $this->assertTrue(Schema::hasColumns('peminjaman_details', ['id_detail', 'id_peminjaman', 'id_katalog', 'jumlah']));
        $this->assertFalse(Schema::hasColumn('peminjaman_details', 'nama_barang_snapshot'));
        $this->assertFalse(Schema::hasColumn('pengembalian_details', 'jumlah_rusak'));
        $this->assertFalse(Schema::hasColumn('pengembalians', 'status_pengembalian'));
    }

    public function test_primary_key_profil_dan_akun(): void
    {
        $this->assertSame('email', (new Akun)->getKeyName());
        $this->assertSame('nim', (new Mahasiswa)->getKeyName());
        $this->assertSame('nuptk_nidn', $this->dosen()->getKeyName());
        $this->assertSame('id_pegawai', $this->staff()->getKeyName());
        $this->assertSame('id_pegawai', $this->laboran()->getKeyName());
    }

    public function test_unique_email_akun_dan_email_profil(): void
    {
        $m = $this->mahasiswa();
        $this->expectException(QueryException::class);
        Akun::create(['email' => $m->email, 'password' => 'x', 'role' => 'mahasiswa']);
    }

    public function test_email_profil_harus_unik_dan_harus_ada_di_akun(): void
    {
        $m = $this->mahasiswa();
        $this->expectException(QueryException::class);
        Mahasiswa::create(['nim' => 'X1', 'nama' => 'A', 'id_prodi' => $m->id_prodi, 'email' => $m->email]); // email sama (1:1)
    }

    public function test_profil_tanpa_akun_ditolak_fk(): void
    {
        $this->expectException(QueryException::class);
        Mahasiswa::create(['nim' => 'X2', 'nama' => 'A', 'id_prodi' => $this->prodi()->id_prodi, 'email' => 'tidakada@uji.test']);
    }

    public function test_role_hanya_empat_nilai(): void
    {
        $this->expectException(QueryException::class);
        Akun::create(['email' => 'a@b.c', 'password' => 'x', 'role' => 'admin']);
    }

    public function test_unique_master_prodi_satuan_ruangan_kelas_katalog(): void
    {
        $this->prodi('A');
        $this->assertThrowsQuery(fn () => Prodi::create(['nama_prodi' => 'A']));
        $this->satuan('meter');
        $this->assertThrowsQuery(fn () => Satuan::create(['nama_satuan' => 'meter']));
        $this->ruangan('Lab 1');
        $this->assertThrowsQuery(fn () => Ruangan::create(['nama_ruangan' => 'Lab 1']));
        $p = $this->prodi('B');
        Kelas::create(['id_prodi' => $p->id_prodi, 'nama_kelas' => '1A']);
        $this->assertThrowsQuery(fn () => Kelas::create(['id_prodi' => $p->id_prodi, 'nama_kelas' => '1A']));
        Kelas::create(['id_prodi' => $this->prodi('C')->id_prodi, 'nama_kelas' => '1A']); // prodi lain boleh
        $this->katalog('alat', 1, ['nama' => 'Obeng']);
        $this->assertThrowsQuery(fn () => $this->katalog('bahan', 1, ['nama' => 'Obeng'])); // nama unik global
    }

    public function test_unique_detail_dan_ruangan_per_transaksi(): void
    {
        $p = $this->peminjamanDasar();
        $k = $this->katalog();
        PeminjamanDetail::create(['id_peminjaman' => $p->id_peminjaman, 'id_katalog' => $k->id_katalog, 'jumlah' => 1]);
        $this->assertThrowsQuery(fn () => PeminjamanDetail::create(['id_peminjaman' => $p->id_peminjaman, 'id_katalog' => $k->id_katalog, 'jumlah' => 2]));
        $r = $this->ruangan();
        $row = ['id_peminjaman' => $p->id_peminjaman, 'id_ruangan' => $r->id_ruangan, 'tanggal_mulai' => '2030-01-01 08:00', 'tanggal_selesai' => '2030-01-01 10:00'];
        PeminjamanRuangan::create($row);
        $this->assertThrowsQuery(fn () => PeminjamanRuangan::create($row));
    }

    public function test_master_yang_dipakai_tidak_bisa_dihapus_restrict(): void
    {
        $k = $this->katalog();
        $this->assertThrowsQuery(fn () => Satuan::whereKey($k->id_satuan)->delete());
        $this->assertThrowsQuery(fn () => Ruangan::whereKey($k->id_ruangan)->delete());

        $m = $this->mahasiswa();
        $this->assertThrowsQuery(fn () => Prodi::whereKey($m->id_prodi)->delete());

        $d = $this->dosen();
        $this->jadwal(Ruangan::find($k->id_ruangan), 'senin', '08:00', '10:00', ['nuptk_nidn' => $d->nuptk_nidn]);
        $this->assertThrowsQuery(fn () => $d->delete());
    }

    public function test_histori_transaksi_tidak_hilang_saat_master_dihapus(): void
    {
        $m = $this->mahasiswa();
        $k = $this->katalog();
        $p = $this->peminjamanDasar($m->email);
        PeminjamanDetail::create(['id_peminjaman' => $p->id_peminjaman, 'id_katalog' => $k->id_katalog, 'jumlah' => 1]);

        $this->assertThrowsQuery(fn () => AlatBahan::whereKey($k->id_katalog)->delete());
        $this->assertThrowsQuery(fn () => Peminjaman::whereKey($p->id_peminjaman)->delete());
        $this->assertThrowsQuery(fn () => Akun::whereKey($m->email)->delete());
        $this->assertSame(1, PeminjamanDetail::count());
    }

    public function test_pengembalian_dan_detail_dilindungi_restrict(): void
    {
        $lab = $this->laboran();
        $k = $this->katalog();
        $p = $this->peminjamanDasar();
        $d = PeminjamanDetail::create(['id_peminjaman' => $p->id_peminjaman, 'id_katalog' => $k->id_katalog, 'jumlah' => 2]);
        $pg = Pengembalian::create(['id_peminjaman' => $p->id_peminjaman, 'tanggal_pengembalian' => now(), 'email_penerima' => $lab->email]);
        PengembalianDetail::create(['id_pengembalian' => $pg->id_pengembalian, 'id_detail_peminjaman' => $d->id_detail, 'jumlah_dikembalikan' => 1, 'kondisi' => 'baik']);

        $this->assertThrowsQuery(fn () => $d->delete());
        $this->assertThrowsQuery(fn () => $pg->delete());
        $this->assertThrowsQuery(fn () => PengembalianDetail::create(['id_pengembalian' => $pg->id_pengembalian, 'id_detail_peminjaman' => $d->id_detail, 'jumlah_dikembalikan' => 1, 'kondisi' => 'hancur']));
    }

    private function peminjamanDasar(?string $email = null): Peminjaman
    {
        return Peminjaman::create([
            'email_peminjam' => $email ?? $this->mahasiswa()->email, 'jenis_peminjaman' => 'pribadi',
            'tanggal_pengajuan' => '2030-01-01 07:00', 'tanggal_peminjaman' => '2030-01-01 08:00', 'tanggal_rencana_kembali' => '2030-01-02 08:00',
            'status' => 'menunggu',
        ]);
    }

    private function assertThrowsQuery(callable $fn): void
    {
        try {
            $fn();
        } catch (QueryException) {
            $this->addToAssertionCount(1);

            return;
        }
        $this->fail('QueryException diharapkan (constraint database).');
    }
}
