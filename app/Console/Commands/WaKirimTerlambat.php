<?php

namespace App\Console\Commands;

use App\Models\Peminjaman;
use App\Models\WaLog;
use App\Services\PeminjamanService;
use App\Services\WhatsAppService;
use App\Support\PesanPengingatWa;
use Carbon\Carbon;
use Illuminate\Console\Command;

class WaKirimTerlambat extends Command
{
    protected $signature = 'wa:kirim-terlambat {--dry-run}';

    protected $description = 'Kirim pengingat WhatsApp untuk peminjaman yang terlambat.';

    public function __construct(
        private PeminjamanService $peminjamanService,
        private WhatsAppService $whatsAppService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $jamMulai = config('notifikasi.wa.jam_mulai');
        $jamSelesai = config('notifikasi.wa.jam_selesai');
        $sekarang = now();

        // Validasi jam operasional
        if (! $this->dalamJam($sekarang, $jamMulai, $jamSelesai)) {
            $this->info("Diluar jam operasional ({$jamMulai}-{$jamSelesai}). Berhenti.");

            return 0;
        }

        $maksPengingat = config('notifikasi.wa.maks_pengingat');
        $dryRun = $this->option('dry-run');

        // Query peminjaman terlambat: status disetujui, no ruangan atau alat belum kembali, past deadline
        $peminjamans = Peminjaman::with(Peminjaman::withProfil())
            ->where('status', 'disetujui')
            ->where('tanggal_rencana_kembali', '<', $sekarang)
            ->orderBy('id_peminjaman')
            ->get();

        $daftar = [];
        $terproses = 0;

        foreach ($peminjamans as $p) {
            // Cek kondisi: tanpa ruangan, dengan alat yang belum kembali
            if ($p->melibatkanRuangan() || ! $this->peminjamanService->alatBelumKembali($p)) {
                continue;
            }

            // Nomor penerima: nomor yang diisi peminjam saat pengajuan, fallback ke nomor profil (mahasiswa/dosen).
            $profil = $p->mahasiswaPeminjam ?? $p->dosenPeminjam;
            $nomor = $p->no_whatsapp ?: $profil?->no_whatsapp;
            if (! $nomor) {
                $daftar[] = [
                    'id' => $p->id_peminjaman,
                    'status' => 'dilewati',
                    'alasan' => 'Nomor WhatsApp kosong',
                ];
                if (! $dryRun) {
                    WaLog::updateOrCreate(
                        ['id_peminjaman' => $p->id_peminjaman, 'ke_ke' => 1],
                        ['jenis' => 'terlambat', 'nomor' => '0', 'status' => 'dilewati', 'dikirim_at' => $sekarang],
                    );
                }

                continue;
            }

            // Tentukan nomor pengingat: ke berapa?
            $keKe = $this->tentukan_ke_ke($p->id_peminjaman, $maksPengingat);
            if ($keKe === null) {
                continue; // Sudah max pengingat
            }

            // Siapkan pesan
            $nama = $profil?->nama ?? $p->email_peminjam;
            $barang = $p->details
                ->map(fn ($detail) => [
                    'nama' => $detail->katalog?->nama ?? '-',
                    'jumlah' => (int) $detail->jumlah,
                ])
                ->values()
                ->all();

            $pesan = PesanPengingatWa::buat(
                $nama,
                $barang,
                $p->tanggal_rencana_kembali,
                $keKe,
                $sekarang,
                config('app.timezone'),
            );

            if ($dryRun) {
                $daftar[] = [
                    'id' => $p->id_peminjaman,
                    'ke_ke' => $keKe,
                    'nomor' => $this->samarkanNomor($nomor),
                    'pesan' => $pesan,
                ];
            } else {
                // Kirim pesan
                $berhasil = $this->whatsAppService->kirim($nomor, $pesan);

                $status = $berhasil ? 'terkirim' : 'gagal';
                $daftar[] = [
                    'id' => $p->id_peminjaman,
                    'ke_ke' => $keKe,
                    'status' => $status,
                ];

                WaLog::updateOrCreate(
                    ['id_peminjaman' => $p->id_peminjaman, 'ke_ke' => $keKe],
                    [
                        'jenis' => 'terlambat',
                        'nomor' => $nomor,
                        'status' => $status,
                        'dikirim_at' => $berhasil ? $sekarang : null,
                    ],
                );
            }

            $terproses++;
        }

        // Tampilkan hasil
        if ($dryRun) {
            $this->info("DRY RUN: {$terproses} peminjaman akan diproses.");
            if (! empty($daftar)) {
                $this->table(['ID', 'Ke-', 'Nomor', 'Pesan'], $daftar);
            }
        } else {
            $this->info("Selesai: {$terproses} pengingat diproses.");
        }

        return 0;
    }

    private function dalamJam(Carbon $waktu, string $jamMulai, string $jamSelesai): bool
    {
        $mulai = Carbon::parse($jamMulai);
        $selesai = Carbon::parse($jamSelesai);
        $cek = $waktu->copy()->setTime($waktu->hour, $waktu->minute, 0);

        return $cek->between($mulai, $selesai);
    }

    private function tentukan_ke_ke(int $idPeminjaman, int $maksPengingat): ?int
    {
        // Hanya pengiriman berhasil yang dihitung; yang gagal dicoba lagi dengan nomor yang sama.
        $terakhir = WaLog::where('id_peminjaman', $idPeminjaman)
            ->where('jenis', 'terlambat')
            ->where('status', 'terkirim')
            ->orderBy('ke_ke', 'desc')
            ->first();

        if ($terakhir === null) {
            // Pertama kali
            return 1;
        }

        if ($terakhir->ke_ke >= $maksPengingat) {
            // Sudah max
            return null;
        }

        // Cek interval 24 jam
        $sekarang = now();
        if ($terakhir->dikirim_at && $terakhir->dikirim_at->addHours(24)->isFuture()) {
            // Belum 24 jam
            return null;
        }

        return $terakhir->ke_ke + 1;
    }

    private function samarkanNomor(string $nomor): string
    {
        if (strlen($nomor) < 8) {
            return $nomor;
        }

        $awal = substr($nomor, 0, 2);
        $akhir = substr($nomor, -2);

        return "{$awal}****{$akhir}";
    }
}
