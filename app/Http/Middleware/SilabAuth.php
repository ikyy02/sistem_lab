<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;

/** Wajib login. Usage: middleware('silab.auth') */
class SilabAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (! AuthService::user()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
