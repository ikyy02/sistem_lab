<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Status "selesai" ditentukan sistem (ruangan berakhir, bahan saja setelah rencana kembali): evaluasi berkala.
Schedule::call(fn () => app(\App\Services\PeminjamanService::class)->sinkronSelesai())
    ->everyFiveMinutes()->name('peminjaman-sinkron-selesai')->withoutOverlapping();
