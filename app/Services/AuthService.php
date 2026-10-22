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
        // Bandingkan password apa adanya (hashing belum diterapkan, ditunda); hash_equals menghindari timing attack.
        if (! $akun || ! hash_equals((string) $akun->password, $password)) {
            return null;
        }
        // Cari profil sesuai role (Mahasiswa/Dosen/Staff/Laboran) lewat email.
        $model = Role::MODELS[$akun->role] ?? null;
        $profil = $model ? $model::query()->where('email', $akun->email)->first() : null;
        if (! $profil) {
            return null; // setiap role wajib punya profil
        }

        return ['role' => $akun->role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $akun->email];
    }

    public function login(Request $request, array $identity): void
    {
        // Ganti ID sesi saat login untuk mencegah session fixation.
        $request->session()->regenerate();
        // Identitas login disimpan di sesi, bukan memakai Auth bawaan Laravel.
        $request->session()->put('silab_user', $identity);
    }

    public function logout(Request $request): void
    {
        $request->session()->forget('silab_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    // Helper dipakai di mana-mana untuk membaca pengguna yang sedang login.
    public static function user(): ?array
    {
        return session('silab_user');
    }
}
