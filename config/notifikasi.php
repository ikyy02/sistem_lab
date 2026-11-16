<?php

return [
    'kirim_email_akun' => env('NOTIFIKASI_KIRIM_EMAIL_AKUN', true),
    'kirim_email_peminjaman' => env('NOTIFIKASI_KIRIM_EMAIL_PEMINJAMAN', true),
    'reply_to' => env('NOTIFIKASI_REPLY_TO', env('MAIL_FROM_ADDRESS')),

    'wa' => [
        'enabled' => env('WA_ENABLED', false),
        'gateway_url' => env('WA_GATEWAY_URL', 'http://127.0.0.1:3001'),
        'gateway_token' => env('WA_GATEWAY_TOKEN', ''),
        'jam_mulai' => env('WA_JAM_MULAI', '08:00'),
        'jam_selesai' => env('WA_JAM_SELESAI', '16:00'),
        'maks_pengingat' => (int) env('WA_MAKS_PENGINGAT', 3),
        'cooldown_menit' => (int) env('WA_COOLDOWN_MENIT', 30),
        'blacklist' => array_filter(array_map('trim', explode(',', env('WA_BLACKLIST', '')))),
    ],

    // Akun uji manual (NotifikasiUjiSeeder): arahkan email ke inbox yang benar-benar bisa menerima pesan.
    'uji' => [
        'password' => env('NOTIFIKASI_UJI_PASSWORD'),
        'email_mahasiswa' => env('NOTIFIKASI_UJI_EMAIL_MHS', 'uji.mhs@mhs.politala.ac.id'),
        'email_dosen' => env('NOTIFIKASI_UJI_EMAIL_DOSEN', 'uji.dosen@politala.ac.id'),
        'email_laboran' => env('NOTIFIKASI_UJI_EMAIL_LABORAN', 'uji.laboran@politala.ac.id'),
    ],
];
