<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK PASSWORD DI DATABASE ===\n\n";

$users = DB::table('akuns')->get();

foreach ($users as $user) {
    echo "Email: {$user->email}\n";
    echo "Password length: " . strlen($user->password) . "\n";
    echo "Password starts with \$2y\$: " . (str_starts_with($user->password, '$2y$') ? 'YES' : 'NO') . "\n";
    echo "Password value: {$user->password}\n";
    echo "---\n";
}
