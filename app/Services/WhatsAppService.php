<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppService
{
    public function kirim(string $nomor, string $pesan): bool
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

        try {
            $response = Http::withToken(config('notifikasi.wa.gateway_token'))
                ->timeout(10)
                ->post(rtrim(config('notifikasi.wa.gateway_url'), '/') . '/send', [
                    'to' => $nomor,
                    'message' => $pesan,
                ]);

            if (! $response->successful()) {
                Log::warning('Gateway WhatsApp menolak pengiriman.', [
                    'status' => $response->status(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $exception) {
            Log::error('Pengiriman WhatsApp gagal.', [
                'exception' => $exception->getMessage(),
            ]);

            return false;
        }
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
