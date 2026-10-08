<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\AuthService;
use App\Models\User;

echo "=== TEST AUTH SERVICE ===\n\n";

try {
    $auth = new AuthService();
    
    // Test login dengan admin
    echo "📝 Testing login dengan admin@silab.test...\n";
    $identity = $auth->attempt('admin@silab.test', 'admin123');
    
    if ($identity) {
        echo "✅ Login berhasil!\n";
        echo "   Role: {$identity['role']}\n";
        echo "   Nama: {$identity['nama']}\n";
        echo "   Email: {$identity['email']}\n";
        echo "   Key: {$identity['key']}\n";
    } else {
        echo "❌ Login gagal!\n";
    }
    
    // Cek profil laboran
    echo "\n📝 Checking profil laboran...\n";
    $user = User::find('admin@silab.test');
    if ($user) {
        echo "✅ User found\n";
        $profil = $user->getProfil();
        if ($profil) {
            echo "✅ Profil found: " . get_class($profil) . "\n";
            if (isset($profil->nama)) {
                echo "   Nama: {$profil->nama}\n";
            }
        } else {
            echo "❌ Profil NOT found\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
