<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    // Alur status: menunggu -> disetujui -> selesai; atau ditolak / dibatalkan.
    public const STATUS = ['menunggu', 'disetujui', 'ditolak', 'selesai', 'dibatalkan'];

    public const JENIS = ['pribadi', 'atas_dosen', 'pembelajaran'];

    /** Jenis yang boleh dipilih Mahasiswa (Dosen selalu memakai "Pembelajaran"). */
    // Dosen tidak memilih jenis; sistem otomatis memakai 'pembelajaran'.
    public const JENIS_MAHASISWA = ['pribadi', 'atas_dosen'];

    public const JENIS_PEMBELAJARAN = 'pembelajaran';

    /** Tampilan status (nilai enum database tetap apa adanya). */
    public const LABELS = [
        'menunggu' => 'Menunggu Persetujuan',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
        'selesai' => 'Dikembalikan',
        'dibatalkan' => 'Dibatalkan',
    ];

    /** Tampilan jenis peminjaman (nilai enum database tetap apa adanya). */
    public const LABEL_JENIS = [
        'pribadi' => 'Peminjaman Mandiri',
        'atas_dosen' => 'Peminjaman dengan Penanggung Jawab Dosen',
        'pembelajaran' => 'Pembelajaran',
    ];

    protected $table = 'peminjamans';

    protected $primaryKey = 'id_peminjaman';

    public $timestamps = false;

    protected $fillable = [
        'email_peminjam', 'jenis_peminjaman', 'email_dosen', 'tanggal_pengajuan', 'tanggal_peminjaman',
        'tanggal_rencana_kembali', 'status', 'keterangan', 'email_pemroses', 'alasan_ditolak', 'tanggal_persetujuan', 'tanggal_persetujuan_dosen',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'datetime', 'tanggal_peminjaman' => 'datetime', 'tanggal_rencana_kembali' => 'datetime',
        'tanggal_persetujuan' => 'datetime', 'tanggal_persetujuan_dosen' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(PeminjamanDetail::class, 'id_peminjaman', 'id_peminjaman');
    }

    public function ruangans()
    {
        return $this->hasMany(PeminjamanRuangan::class, 'id_peminjaman', 'id_peminjaman');
    }

    public function pengembalians()
    {
        return $this->hasMany(Pengembalian::class, 'id_peminjaman', 'id_peminjaman');
    }

    public function mahasiswaPeminjam()
    {
        return $this->belongsTo(Mahasiswa::class, 'email_peminjam', 'email');
    }

    public function dosenPeminjam()
    {
        return $this->belongsTo(Dosen::class, 'email_peminjam', 'email');
    }

    public function dosenPenanggung()
    {
        return $this->belongsTo(Dosen::class, 'email_dosen', 'email');
    }

    public function akunPeminjam()
    {
        return $this->belongsTo(Akun::class, 'email_peminjam', 'email');
    }

    public function pemroses()
    {
        return $this->belongsTo(Akun::class, 'email_pemroses', 'email');
    }

    /** Relasi yang perlu di-eager-load pada daftar agar tidak N+1. */
    // Daftar relasi untuk eager loading supaya halaman daftar tidak melakukan query berulang (N+1).
    public static function withProfil(): array
    {
        return ['mahasiswaPeminjam', 'dosenPeminjam', 'dosenPenanggung', 'details.katalog.satuan', 'ruangans.ruangan'];
    }

    public function melibatkanRuangan(): bool
    {
        return $this->ruangans->isNotEmpty();
    }

    public function melibatkanBarang(): bool
    {
        return $this->details->isNotEmpty();
    }

    /** Pengajuan Kuliah/Praktikum yang masih menunggu keputusan Dosen penanggung jawab (tahap 1, SRS D.8). */
    public function menungguDosen(): bool
    {
        return $this->status === 'menunggu' && $this->jenis_peminjaman === 'atas_dosen' && $this->tanggal_persetujuan_dosen === null;
    }

    /** Pengajuan Kuliah/Praktikum yang sudah disetujui Dosen dan menunggu keputusan akhir Laboran (tahap 2). */
    public function diteruskanKeLaboran(): bool
    {
        return $this->status === 'menunggu' && $this->jenis_peminjaman === 'atas_dosen' && $this->tanggal_persetujuan_dosen !== null;
    }

    /**
     * Pengajuan yang boleh DILIHAT di antrean sebuah role (satu-satunya sumber aturan kewenangan):
     * - Laboran: semua jenis;
     * - Dosen: hanya jenis "atas_dosen" (Kuliah/Praktikum) yang menunjuk dirinya, bukan miliknya sendiri;
     * - role lain: tidak ada. Peminjaman Mandiri dan Pembelajaran Dosen hanya ditangani Laboran.
     */
    // Scope: siapa boleh MELIHAT pengajuan di antreannya. Ubah di sini bila aturan kewenangan berubah.
    public function scopeDapatDiprosesOleh($q, string $email, string $role)
    {
        return match ($role) {
            'laboran' => $q,
            'dosen' => $q->where('jenis_peminjaman', 'atas_dosen')->where('email_dosen', $email)->where('email_peminjam', '!=', $email),
            default => $q->whereRaw('1 = 0'),
        };
    }

    /** Yang menunggu TINDAKAN role itu sekarang: Dosen = tahap 1; Laboran = semua yang menunggu. */
<<<<<<< HEAD
=======
    // Scope: pengajuan yang menunggu tindakan role tersebut sekarang.
>>>>>>> c4ec5d9 (refactor: rapikan SIARKA dan perbaiki akses serta peminjaman)
    public function scopeMenungguTindakan($q, string $email, string $role)
    {
        $q = $q->dapatDiprosesOleh($email, $role)->where('status', 'menunggu');

        return match ($role) {
            'laboran' => $q,
            default => $q->whereNull('tanggal_persetujuan_dosen'),
        };
    }

    /** Boleh memutuskan (setujui/tolak) pada tahapnya; status "menunggu" diperiksa tersendiri oleh pemanggil. */
    public function bisaDiprosesOleh(string $email, string $role): bool
    {
        if ($this->email_peminjam === $email) {
            return false;
        }

        return match ($role) {
            'laboran' => true, // Super Admin dapat memutuskan di tahap mana pun, termasuk menggantikan tahap Dosen
            'dosen' => $this->jenis_peminjaman === 'atas_dosen' && $this->email_dosen === $email && $this->tanggal_persetujuan_dosen === null,
            default => false,
        };
    }

    /** Detail boleh dibuka: peminjam sendiri, Laboran, atau Dosen penanggung jawab transaksinya. */
    public function bisaDilihatOleh(string $email, string $role): bool
    {
        return $this->email_peminjam === $email || $role === 'laboran'
            || ($role === 'dosen' && $this->email_dosen === $email);
    }

    /** Awal tanggal pelaksanaan: yang paling awal antara tanggal peminjaman dan mulai pemakaian ruangan. */
    public function tanggalPelaksanaan(): ?\Carbon\Carbon
    {
        $awal = $this->ruangans->map(fn ($r) => \Carbon\Carbon::parse($r->tanggal_mulai))
            ->push($this->tanggal_peminjaman)->filter()->sort()->first();

        return $awal?->copy()->startOfDay();
    }

    /** Dapat dibatalkan peminjam: melibatkan ruangan, belum diproses akhir, dan sebelum tanggal pelaksanaan (sampai 23.59 H-1). */
    // Syarat batal: milik sendiri, status menunggu/disetujui, ada ruangan, dan masih sebelum tanggal pelaksanaan.
    public function bisaDibatalkanOleh(string $email, ?\Carbon\CarbonInterface $sekarang = null): bool
    {
        $pelaksanaan = $this->tanggalPelaksanaan();

        return $this->email_peminjam === $email
            && in_array($this->status, ['menunggu', 'disetujui'], true)
            && $this->melibatkanRuangan()
            && $pelaksanaan !== null
            && ($sekarang ?? now())->lt($pelaksanaan);
    }

    /**
     * Pihak yang menekan Selesai (hanya bila melibatkan ruangan; transaksi tanpa ruangan mengikuti proses pengembalian):
     * - ruangan + alat/bahan -> Laboran;
     * - ruangan saja dengan Penanggung Jawab Dosen -> Dosen terkait;
     * - ruangan saja lainnya (mandiri/pembelajaran) -> peminjam sendiri.
     */
    // Menentukan siapa yang memegang tombol Selesai tergantung isi transaksi (ruangan/barang/dosen).
    public function bisaDiselesaikanOleh(string $email, string $role): bool
    {
        if ($this->status !== 'disetujui' || ! $this->melibatkanRuangan()) {
            return false;
        }
        if ($this->melibatkanBarang()) {
            return $role === 'laboran';
        }
        if ($this->jenis_peminjaman === 'atas_dosen') {
            return $role === 'dosen' && $this->email_dosen === $email;
        }

        return $this->email_peminjam === $email;
    }

    public function getLabelStatusAttribute(): string
    {
        return self::LABELS[$this->status] ?? $this->status;
    }

    public function getLabelJenisAttribute(): string
    {
        return self::LABEL_JENIS[$this->jenis_peminjaman] ?? $this->jenis_peminjaman;
    }

    /** Nama peminjam: profil Mahasiswa/Dosen, fallback ke email akun. */
    public function getNamaPeminjamAttribute(): string
    {
        return $this->mahasiswaPeminjam?->nama ?? $this->dosenPeminjam?->nama ?? $this->email_peminjam;
    }

    /** NIM / NIDN / email peminjam. */
    public function getIdentitasPeminjamAttribute(): string
    {
        return $this->mahasiswaPeminjam?->nim ?? $this->dosenPeminjam?->nuptk_nidn ?? $this->email_peminjam;
    }

    /** Peminjaman aktif yang sudah melewati batas pengembalian. */
    // Terlambat = masih disetujui tetapi rencana kembali sudah lewat.
    public function getTerlambatAttribute(): bool
    {
        return $this->status === 'disetujui' && $this->tanggal_rencana_kembali !== null
            && $this->tanggal_rencana_kembali->isPast();
    }

    /** Ringkasan barang/ruangan untuk ditampilkan pada tabel. */
    public function getRingkasanAttribute(): string
    {
        $barang = $this->details->map(fn ($d) => $d->katalog?->nama.' ('.$d->jumlah.')')->filter()->values();
        $ruang = $this->ruangans->map(fn ($r) => $r->ruangan?->nama_ruangan)->filter()->values();
        $semua = $barang->concat($ruang);

        return $semua->isEmpty() ? '—' : $semua->implode(', ');
    }

    public function getJumlahBarangAttribute(): int
    {
        return (int) $this->details->sum('jumlah');
    }
}
