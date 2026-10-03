<?php

namespace App\Services;

use App\Models\Akun;
use App\Support\Role;
use Illuminate\Http\Request;

/**
 * Autentikasi email + password untuk 4 kategori pengguna.
 */
class AuthService
{
    /** @return array{role: string, key: string, nama: string, email: string}|null */
    public function attempt(string $email, string $password): ?array
    {
        $akun = Akun::query()->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($email))])->first();
        if (! $akun || ! hash_equals((string) $akun->password, $password)) {
            return null;
        }
        $model = Role::MODELS[$akun->role] ?? null;
        $profil = $model ? $model::query()->where('email', $akun->email)->first() : null;
        if (! $profil) {
            return null; // setiap role wajib punya profil
        }

        return ['role' => $akun->role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $akun->email];
    }

    public function login(Request $request, array $identity): void
    {
        $request->session()->regenerate();
        $request->session()->put('silab_user', $identity);
    }

    public function logout(Request $request): void
    {
        $request->session()->forget('silab_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public static function user(): ?array
    {
        return session('silab_user');
    }
}
