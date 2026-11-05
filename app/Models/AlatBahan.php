<?php

namespace App\Models;

use App\Support\GambarKatalog;
use Illuminate\Database\Eloquent\Model;

/** Katalog Alat/Bahan. Harga = harga untuk 1 satuan. Tanpa kategori, kondisi, unit fisik, atau snapshot. */
// Katalog Alat/Bahan.
class AlatBahan extends Model
{
    public const JENIS = ['alat', 'bahan'];
    public const GAMBAR_DIR = 'uploads/katalog';

    protected $table = 'alat_bahans';
    protected $primaryKey = 'id_katalog';
    public $timestamps = false;
    protected $fillable = ['nama', 'jenis', 'id_satuan', 'stok', 'harga', 'id_ruangan', 'gambar', 'keterangan'];
    protected $casts = ['stok' => 'integer', 'harga' => 'decimal:2'];

    protected static function booted(): void
    {
        // Setelah pernah masuk PEMINJAMAN_DETAIL, jenis tidak boleh diubah.
        // Aturan bisnis: jenis (alat/bahan) dikunci setelah pernah dipinjam supaya riwayat tidak kacau.
        static::updating(function (AlatBahan $m) {
            if ($m->isDirty('jenis') && $m->pernahDipinjam()) {
                throw new \DomainException('Jenis katalog tidak dapat diubah karena sudah pernah dipakai pada peminjaman.');
            }
        });
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'id_satuan', 'id_satuan');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'id_ruangan', 'id_ruangan');
    }

    // URL gambar via GambarKatalog (mendukung lokasi lama dan storage baru).
    public function getGambarUrlAttribute(): ?string
    {
        return GambarKatalog::url($this->gambar, self::GAMBAR_DIR);
    }

    /** True bila katalog pernah masuk detail peminjaman (jenis tidak boleh diubah). */
    public function pernahDipinjam(): bool
    {
        return PeminjamanDetail::where('id_katalog', $this->getKey())->exists();
    }
}
