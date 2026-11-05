<?php

namespace App\Http\Middleware;

use App\Models\Akun;
use App\Models\Dosen;
use App\Services\AuthService;
use App\Support\Role;
use Closure;
use Illuminate\Http\Request;

/** Wajib login. Usage: middleware('silab.auth') */
class SilabAuth
{
    public function handle(Request $request, Closure $next)
    {
        $user = AuthService::user();
        // Sesi tidak berlaku lagi bila akun dihapus, role-nya berubah, atau Dosen dinonaktifkan.
        // Cek sesi login dan akunnya masih ada dengan role yang sama; kalau tidak, paksa login ulang.
        if (! $user || ! Akun::where('email', $user['email'])->where('role', $user['role'])->exists()
            || ($user['role'] === Role::DOSEN && ! Dosen::where('email', $user['email'])->where('status', 'aktif')->exists())) {
            $request->session()->forget('silab_user');

            return redirect()->route('login');
        }

        return $next($request);
    }
}
