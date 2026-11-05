<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\StaffProdi;
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
        // Dosen nonaktif tidak boleh masuk; hasilnya sama dengan password salah agar status tidak bocor.
        if ($akun->role === Role::DOSEN && $profil->status !== 'aktif') {
            return null;
        }

        return ['role' => $akun->role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $akun->email];
    }

    /** id_prodi yang membatasi data pengguna: Staf Prodi = prodinya sendiri; role lain (Laboran) = null (tanpa batas). */
    public static function batasProdi(): ?int
    {
        $me = self::user();
        if (($me['role'] ?? null) !== Role::STAFF) {
            return null;
        }

        return (int) StaffProdi::where('email', $me['email'])->value('id_prodi') ?: abort(403);
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
