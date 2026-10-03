<?php

namespace Tests\Concerns;

use App\Models\Akun;
use App\Models\AlatBahan;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Laboran;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\Ruangan;
use App\Models\Satuan;
use App\Models\StaffProdi;

/** Pembuat data uji (setiap panggilan menghasilkan data unik). */
trait MembuatData
{
    private int $seq = 0;

    protected function n(): int
    {
        return ++$this->seq;
    }

    protected function prodi(?string $nama = null): Prodi
    {
        return Prodi::firstOrCreate(['nama_prodi' => $nama ?? 'Prodi Uji']);
    }

    protected function akun(string $role, ?string $email = null, string $password = 'rahasia'): Akun
    {
        return Akun::create(['email' => $email ?? "{$role}{$this->n()}@uji.test", 'password' => $password, 'role' => $role]);
    }

    protected function mahasiswa(array $o = []): Mahasiswa
    {
        $akun = isset($o['email']) ? (Akun::find($o['email']) ?? $this->akun('mahasiswa', $o['email'])) : $this->akun('mahasiswa');
        $n = $this->n();

        return Mahasiswa::create($o + ['nim' => sprintf('22%08d', $n), 'nama' => "Mahasiswa {$n}", 'id_prodi' => $this->prodi()->id_prodi, 'email' => $akun->email, 'no_whatsapp' => null]);
    }

    protected function dosen(array $o = []): Dosen
    {
        $akun = isset($o['email']) ? (Akun::find($o['email']) ?? $this->akun('dosen', $o['email'])) : $this->akun('dosen');
        $n = $this->n();

        return Dosen::create($o + ['nuptk_nidn' => sprintf('D%08d', $n), 'nama' => "Dosen {$n}", 'id_prodi' => $this->prodi()->id_prodi, 'email' => $akun->email, 'no_whatsapp' => null]);
    }

    protected function staff(): StaffProdi
    {
        $akun = $this->akun('staff_prodi');
        $n = $this->n();

        return StaffProdi::create(['id_pegawai' => "S{$n}", 'nama' => "Staff {$n}", 'id_prodi' => $this->prodi()->id_prodi, 'email' => $akun->email]);
    }

    protected function laboran(): Laboran
    {
        $akun = $this->akun('laboran');
        $n = $this->n();

        return Laboran::create(['id_pegawai' => "L{$n}", 'nama' => "Laboran {$n}", 'email' => $akun->email]);
    }

    protected function ruangan(?string $nama = null): Ruangan
    {
        return Ruangan::create(['nama_ruangan' => $nama ?? "Ruang {$this->n()}"]);
    }

    protected function satuan(?string $nama = null): Satuan
    {
        return Satuan::firstOrCreate(['nama_satuan' => $nama ?? 'buah']);
    }

    protected function katalog(string $jenis = 'alat', int $stok = 10, array $o = []): AlatBahan
    {
        $n = $this->n();

        return AlatBahan::create($o + [
            'nama' => "Barang {$n}", 'jenis' => $jenis, 'id_satuan' => $this->satuan()->id_satuan, 'stok' => $stok,
            'harga' => 1000, 'id_ruangan' => ($o['id_ruangan'] ?? $this->ruangan()->id_ruangan), 'gambar' => 'x.jpg',
        ]);
    }

    protected function kelas(): Kelas
    {
        return Kelas::create(['nama_kelas' => "Kelas {$this->n()}", 'id_prodi' => $this->prodi()->id_prodi]);
    }

    protected function jadwal(Ruangan $r, string $hari, string $mulai, string $selesai, array $o = []): Jadwal
    {
        return Jadwal::create($o + [
            'id_kelas' => $this->kelas()->id_kelas, 'nuptk_nidn' => ($this->dosen())->nuptk_nidn, 'id_ruangan' => $r->id_ruangan,
            'hari' => $hari, 'jam_mulai' => $mulai, 'jam_selesai' => $selesai, 'status' => 'aktif',
            'tahun_akademik' => '2026/2027', 'semester' => 'ganjil',
        ]);
    }
}
