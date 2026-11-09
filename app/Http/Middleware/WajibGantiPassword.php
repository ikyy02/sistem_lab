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
        $akun = $request->attributes->get('akun');
        if ($akun === null && ($email = AuthService::user()['email'] ?? null)) {
            $akun = Akun::find($email);
        }
        if ($akun?->wajib_ganti_password && ! $request->routeIs('profil.*')) {
            return redirect()->route('profil.index')->with('warning', 'Anda masih memakai password awal. Ganti password terlebih dahulu sebelum memakai fitur lain.');
        }

        return $next($request);
    }
}
