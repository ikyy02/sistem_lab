<?php

namespace App\Http\Middleware;

use App\Models\Akun;
use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;

/** Wajib login. Usage: middleware('silab.auth') */
class SilabAuth
{
    public function handle(Request $request, Closure $next)
    {
        $user = AuthService::user();
        // Sesi tidak berlaku lagi bila akun dihapus atau role-nya berubah.
        // Cek sesi login dan akunnya masih ada dengan role yang sama; kalau tidak, paksa login ulang.
        if (! $user || ! Akun::where('email', $user['email'])->where('role', $user['role'])->exists()) {
            $request->session()->forget('silab_user');

            return redirect()->route('login');
        }

        return $next($request);
    }
}
