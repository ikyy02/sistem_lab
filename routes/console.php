<?php

use App\Services\PengingatPeminjamanService;
use App\Services\SinkronSelesaiService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Status "selesai" ditentukan sistem (ruangan berakhir, bahan saja setelah rencana kembali): evaluasi berkala
// sekaligus kirim PeminjamanSelesai sekali untuk tiap transaksi yang baru berubah menjadi selesai.
Schedule::call(fn () => app(SinkronSelesaiService::class)->jalankan())
    ->everyFiveMinutes()->name('peminjaman-sinkron-selesai')->withoutOverlapping();

// Pengingat harian peminjam: H-1 pengembalian dan peminjaman yang sudah terlambat (sekali per transaksi per tanggal).
Schedule::call(fn () => app(PengingatPeminjamanService::class)->kirim())
    ->dailyAt('07:00')->name('pengingat-peminjaman')->withoutOverlapping();

// Pengingat WhatsApp keterlambatan: setiap jam, dalam jam operasional, tanpa overlapping.
Schedule::command('wa:kirim-terlambat')->hourly()
    ->between('08:00', '16:00')->name('wa-kirim-terlambat')->withoutOverlapping();
