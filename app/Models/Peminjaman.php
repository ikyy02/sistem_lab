<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    public const STATUS = ['menunggu', 'disetujui', 'ditolak', 'selesai'];
    public const JENIS = ['pribadi', 'atas_dosen'];

    /** Tampilan status (nilai enum database tetap apa adanya). */
    public const LABELS = [
        'menunggu' => 'Menunggu Persetujuan',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
        'selesai' => 'Dikembalikan',
    ];

    protected $table = 'peminjamans';
    protected $primaryKey = 'id_peminjaman';
    public $timestamps = false;
    protected $fillable = [
        'email_peminjam', 'jenis_peminjaman', 'email_dosen', 'tanggal_pengajuan', 'tanggal_peminjaman',
        'tanggal_rencana_kembali', 'status', 'keterangan', 'email_pemroses', 'alasan_ditolak', 'tanggal_persetujuan',
    ];
    protected $casts = [
        'tanggal_pengajuan' => 'datetime', 'tanggal_peminjaman' => 'datetime', 'tanggal_rencana_kembali' => 'datetime',
        'tanggal_persetujuan' => 'datetime',
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
    public static function withProfil(): array
    {
        return ['mahasiswaPeminjam', 'dosenPeminjam', 'dosenPenanggung', 'details.katalog.satuan', 'ruangans.ruangan'];
    }

    public function getLabelStatusAttribute(): string
    {
        return self::LABELS[$this->status] ?? $this->status;
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
    public function getTerlambatAttribute(): bool
    {
        return $this->status === 'disetujui' && $this->tanggal_rencana_kembali !== null
            && $this->tanggal_rencana_kembali->isPast();
    }

    /** Ringkasan barang/ruangan untuk ditampilkan pada tabel. */
    public function getRingkasanAttribute(): string
    {
        $barang = $this->details->map(fn ($d) => $d->katalog?->nama . ' (' . $d->jumlah . ')')->filter()->values();
        $ruang = $this->ruangans->map(fn ($r) => $r->ruangan?->nama_ruangan)->filter()->values();
        $semua = $barang->concat($ruang);

        return $semua->isEmpty() ? '—' : $semua->implode(', ');
    }

    public function getJumlahBarangAttribute(): int
    {
        return (int) $this->details->sum('jumlah');
    }
}
