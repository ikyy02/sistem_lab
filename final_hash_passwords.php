<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== FINAL PASSWORD HASHING ===\n\n";

// Definisikan password untuk setiap user
$passwords = [
    'admin@silab.test' => 'admin123',
    'nurmutia@stafprodi' => 'stafprodi',
];

foreach ($passwords as $email => $plainPassword) {
    $hashedPassword = Hash::make($plainPassword);
    
    DB::table('akuns')
        ->where('email', $email)
        ->update(['password' => $hashedPassword]);
    
    echo "✅ Password hashed untuk: $email\n";
    echo "   Plain password: $plainPassword\n";
    echo "   Hashed: " . substr($hashedPassword, 0, 30) . "...\n\n";
}

echo "========================================\n";
echo "✅ SELESAI! Login dengan:\n";
echo "========================================\n\n";

foreach ($passwords as $email => $plainPassword) {
    echo "Email: $email\n";
    echo "Password: $plainPassword\n\n";
}
