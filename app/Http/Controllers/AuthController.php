<?php

namespace App\Http\Controllers;

use App\Models\PengaturanOperasional;
use App\Services\AuthService;
use App\Support\Role;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /** Landing Page (pengguna belum login) yang memuat formulir Login; pengguna yang sudah login langsung ke Beranda. */
    public function landing()
    {
        if ($user = AuthService::user()) {
            return redirect(Role::home($user['role']));
        }

        return view('auth.login', ['setting' => PengaturanOperasional::find(1)]);
    }

    public function showLogin()
    {
        return $this->landing();
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

        $identity = $auth->attempt($data['email'], $data['password']);

        if (! $identity) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        $auth->login($request, $identity);

        return redirect(Role::home($identity['role']));
    }

    public function logout(Request $request, AuthService $auth)
    {
        $auth->logout($request);

        return redirect()->route('landing');
    }
}
