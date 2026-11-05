<?php

namespace App\Http\Middleware;

use App\Models\Akun;
use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;

/** Akun dengan password awal wajib menggantinya dulu: semua halaman selain Profil Saya dialihkan ke Profil. */
class WajibGantiPassword
{
    public function handle(Request $request, Closure $next)
    {
        $email = AuthService::user()['email'] ?? null;
        if ($email && ! $request->routeIs('profil.*') && Akun::whereKey($email)->value('wajib_ganti_password')) {
            return redirect()->route('profil.index')->with('warning', 'Anda masih memakai password awal. Ganti password terlebih dahulu sebelum memakai fitur lain.');
        }

        return $next($request);
    }
}
