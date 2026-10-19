<?php

namespace App\Http\Controllers;

use App\Models\PengaturanOperasional;
use App\Models\Ruangan;
use App\Services\AuthService;
use App\Support\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    /** Landing Page publik (tanpa formulir login); pengguna yang sudah login langsung ke Beranda sesuai role. */
    public function landing()
    {
        if ($user = AuthService::user()) {
            return redirect(Role::home($user['role']));
        }

        return view('landing', [
            'setting' => PengaturanOperasional::find(1),
            'ruangans' => Ruangan::where('nama_ruangan', '<>', 'Belum ditentukan')->orderBy('id_ruangan')->get(),
        ]);
    }

    /** Halaman login tersendiri (hanya formulir autentikasi). */
    public function showLogin()
    {
        if ($user = AuthService::user()) {
            return redirect(Role::home($user['role']));
        }

        return view('auth.login');
    }

    public function login(Request $request, AuthService $auth)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $kunci = 'login:'.mb_strtolower($data['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Terlalu banyak percobaan login. Coba lagi dalam '.RateLimiter::availableIn($kunci).' detik.']);
        }

        $identity = $auth->attempt($data['email'], $data['password']);

        if (! $identity) {
            RateLimiter::hit($kunci, 300);

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        RateLimiter::clear($kunci);
        $auth->login($request, $identity);

        return redirect(Role::home($identity['role']));
    }

    public function logout(Request $request, AuthService $auth)
    {
        $auth->logout($request);

        return redirect()->route('landing');
    }
}
