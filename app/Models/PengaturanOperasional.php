<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/** Pengaturan jam & hari operasional laboratorium (satu baris, id = 1). */
class PengaturanOperasional extends Model
{
    public const HARI = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

    /** Nama hari sesuai index Carbon dayOfWeek / format('w') (0 = minggu). */
    public const NAMA_HARI = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

    protected $table = 'pengaturan_operasional';
    public $timestamps = false;
    protected $fillable = ['jam_buka', 'jam_tutup', 'hari_operasional'];

    // Selalu ada satu baris (id 1); dibuat dengan nilai awal bila belum ada.
    public static function ambil(): self
    {
        return self::firstOrCreate(['id' => 1], ['jam_buka' => '08:00:00', 'jam_tutup' => '16:00:00', 'hari_operasional' => 'senin,selasa,rabu,kamis,jumat']);
    }

    // hari_operasional disimpan sebagai teks dipisah koma; fungsi ini mengubahnya menjadi array.
    public function hariAktif(): array
    {
        return array_values(array_filter(explode(',', (string) $this->hari_operasional)));
    }

    /**
     * Aturan jam kerja untuk form dan validasi.
     *
     * @return array{jam_buka:string, jam_tutup:string, hari:array<int,string>}
     */
    public function aturan(): array
    {
        return [
            'jam_buka' => substr((string) $this->jam_buka, 0, 5),
            'jam_tutup' => substr((string) $this->jam_tutup, 0, 5),
            'hari' => $this->hariAktif(),
        ];
    }

    /**
     * Waktu paling awal yang masih boleh dipilih: hari operasional pertama yang jamnya
     * masih buka dan waktu tersebut belum lewat. null bila tidak ditemukan dalam 60 hari.
     */
    public function waktuPalingAwal(?CarbonInterface $sekarang = null): ?Carbon
    {
        $sekarang = ($sekarang ? Carbon::instance($sekarang) : now())->copy()->startOfMinute();
        $aturan = $this->aturan();
        [$jamBuka, $menitBuka, $detikBuka] = $this->pecahJam($aturan['jam_buka']);
        [$jamTutup, $menitTutup, $detikTutup] = $this->pecahJam($aturan['jam_tutup']);
        $hari = $sekarang->copy()->startOfDay();

        for ($i = 0; $i < 60; $i++, $hari->addDay()) {
            if (! in_array(self::NAMA_HARI[(int) $hari->format('w')], $aturan['hari'], true)) {
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
    public function cekSatuHari(CarbonInterface $mulai, CarbonInterface $selesai): ?array
    {
        $aturan = $this->aturan();
        $rentang = $aturan['jam_buka'].' – '.$aturan['jam_tutup'];
        if (! $mulai->isSameDay($selesai)) {
            return ['tanggal_rencana_kembali', "Peminjaman hanya berlaku dalam satu hari operasional: mulai dan selesai harus pada tanggal yang sama (jam operasional {$rentang})."];
        }
        if (! in_array(self::NAMA_HARI[(int) $mulai->format('w')], $aturan['hari'], true)) {
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
}
