<?php

namespace App\Services;

use App\Support\Role;
use Illuminate\Http\Request;

/**
 * Autentikasi email + password untuk 4 kategori pengguna.
 *
 * TAHAP INI: password dibandingkan apa adanya (tanpa hash/enkripsi).
 * Tahap berikutnya cukup mengubah passwordMatches() (mis. Hash::check) dan
 * menambahkan Hash::make() saat menyimpan data pada UserManagementController.
 */
class AuthService
{
    /** @return array{role: string, id: int, nama: string, email: string}|null */
    public function attempt(string $email, string $password): ?array
    {
        $email = mb_strtolower(trim($email));

        foreach (Role::MODELS as $role => $model) {
            $user = $model::query()->whereRaw('LOWER(email) = ?', [$email])->first();

            if ($user && $this->passwordMatches($password, (string) $user->password)) {
                return ['role' => $role, 'id' => $user->id, 'nama' => $user->nama, 'email' => $user->email];
            }
        }

        return null;
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

    private function passwordMatches(string $input, string $stored): bool
    {
        return $stored !== '' && hash_equals($stored, $input);
    }
}
