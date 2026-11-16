<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppService
{
    /**
     * Kirim pesan WhatsApp dengan filter anti-spam dan anti-banned.
     */
    public function kirim(string $nomor, string $pesan, bool $abaikanCooldown = false): bool
    {
        if (! config('notifikasi.wa.enabled')) {
            Log::info('Pengiriman WhatsApp dilewati karena WA_ENABLED=false.');

            return false;
        }

        $nomor = $this->normalisasiNomor($nomor);
        if ($nomor === null) {
            Log::warning('Pengiriman WhatsApp dibatalkan: nomor tidak valid.');

            return false;
        }

        // Filter Anti-Spam: Cek blacklist dan nomor dengan pola dummy/tidak wajar
        if ($this->apakahNomorDiblokirAtauPalsu($nomor)) {
            Log::warning('Pengiriman WhatsApp dibatalkan: nomor masuk daftar blokir atau memiliki pola tidak wajar.', [
                'nomor' => $nomor,
            ]);

            return false;
        }

        // Filter Anti-Spam: Jeda waktu (cooldown) per nomor penerima untuk mencegah spam flood
        if (! $abaikanCooldown && $this->apakahSedangCooldown($nomor)) {
            Log::warning('Pengiriman WhatsApp dibatalkan: nomor masih dalam masa cooldown anti-spam.', [
                'nomor' => $nomor,
            ]);

            return false;
        }

        try {
            $response = Http::withToken(config('notifikasi.wa.gateway_token'))
                ->timeout(30)
                ->post(rtrim(config('notifikasi.wa.gateway_url'), '/') . '/send', [
                    'to' => $nomor,
                    'message' => $pesan,
                ]);

            if (! $response->successful()) {
                Log::warning('Gateway WhatsApp menolak pengiriman.', [
                    'status' => $response->status(),
                    'error' => $response->json('error') ?? $response->body(),
                ]);

                return false;
            }

            // Catat cooldown sukses jika masa cooldown diatur (> 0 menit)
            $cooldownMenit = (int) config('notifikasi.wa.cooldown_menit', 30);
            if ($cooldownMenit > 0) {
                $this->setCooldown($nomor, $cooldownMenit);
            }

            return true;
        } catch (Throwable $exception) {
            Log::error('Pengiriman WhatsApp gagal.', [
                'exception' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Cek apakah nomor masuk daftar blacklist atau memiliki pola angka berulang (dummy).
     */
    public function apakahNomorDiblokirAtauPalsu(string $nomor): bool
    {
        $blacklist = config('notifikasi.wa.blacklist', []);
        if (is_array($blacklist) && (in_array($nomor, $blacklist, true) || in_array('+' . $nomor, $blacklist, true))) {
            return true;
        }

        // Deteksi pola dummy (contoh: semua angka setelah kode negara/prefix berulang seperti 6281111111111 atau 628000000000)
        $ekor = substr($nomor, 3); // buang '628' atau prefix 3 digit
        if (strlen($ekor) >= 7 && preg_match('/^(\d)\1+$/', $ekor)) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah nomor sedang dalam masa cooldown anti-spam.
     */
    public function apakahSedangCooldown(string $nomor): bool
    {
        return Cache::has($this->kunciCacheCooldown($nomor));
    }

    /**
     * Set penanda cooldown untuk nomor tertentu.
     */
    public function setCooldown(string $nomor, int $menit): void
    {
        Cache::put($this->kunciCacheCooldown($nomor), true, now()->addMinutes($menit));
    }

    /**
     * Hapus penanda cooldown untuk nomor tertentu.
     */
    public function bersihkanCooldown(string $nomor): void
    {
        Cache::forget($this->kunciCacheCooldown($nomor));
    }

    private function kunciCacheCooldown(string $nomor): string
    {
        return 'wa:cooldown:' . $nomor;
    }

    public function normalisasiNomor(string $nomor): ?string
    {
        $nomor = preg_replace('/\D+/', '', $nomor);

        if (str_starts_with($nomor, '0')) {
            $nomor = '62' . substr($nomor, 1);
        } elseif (! str_starts_with($nomor, '62')) {
            $nomor = '62' . $nomor;
        }

        return preg_match('/^62\d{8,13}$/', $nomor) ? $nomor : null;
    }
}
