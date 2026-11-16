<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class WaKirimTes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wa:tes 
                            {nomor : Nomor WhatsApp tujuan (contoh: 081234567890)} 
                            {pesan=Halo, ini pesan uji coba dari sistem lab! : Pesan yang ingin dikirim}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim pesan uji coba WhatsApp secara instan.';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppService $wa): int
    {
        $nomor = $this->argument('nomor');
        $pesan = $this->argument('pesan');

        $this->info("Mengirim pesan ke {$nomor}...");

        $berhasil = $wa->kirim($nomor, $pesan, true);

        if ($berhasil) {
            $this->info("✅ Pesan berhasil dikirim ke {$nomor}!");
            return Command::SUCCESS;
        }

        $this->error("❌ Gagal mengirim pesan. Pastikan nomor valid dan WhatsApp Gateway aktif.");
        return Command::FAILURE;
    }
}
