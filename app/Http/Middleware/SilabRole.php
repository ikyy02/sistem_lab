<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;

/** Batasi akses berdasarkan role. Usage: middleware('silab.role:laboran') atau 'silab.role:mahasiswa,dosen' */
class SilabRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = AuthService::user();

        if (! $user || ! in_array($user['role'], $roles, true)) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}
