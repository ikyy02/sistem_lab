<?php

namespace App\Services;

use App\Models\Akun;
use App\Support\Role;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Akun + profil dibuat/diubah/dihapus secara atomik sehingga role selalu punya tepat satu profil yang sesuai.
 */
class AkunService
{
    /** @param array<string,mixed> $profil kolom profil (tanpa email/password) */
    public function buat(string $role, string $email, string $password, array $profil): object
    {
        $model = Role::MODELS[$role] ?? throw new \InvalidArgumentException('Role tidak valid.');

        // Akun dan profil dibuat dalam satu transaksi: gagal salah satu, keduanya dibatalkan.
        return DB::transaction(function () use ($model, $role, $email, $password, $profil) {
            Akun::create(['email' => $email, 'password' => $password, 'role' => $role]);

            return $model::create($profil + ['email' => $email]);
        });
    }

    /** Password null/'' = tidak diubah. Perubahan email ikut ke profil lewat ON UPDATE CASCADE. */
    public function ubah(object $profil, string $role, array $data, ?string $password): object
    {
        return DB::transaction(function () use ($profil, $role, $data, $password) {
            // lockForUpdate mengunci baris akun supaya tidak ada perubahan bersamaan.
            $akun = Akun::where('email', $profil->email)->where('role', $role)->lockForUpdate()->firstOrFail();
            $email = $data['email'] ?? $profil->email;
            // Email adalah kunci akun; mengubahnya otomatis ikut ke tabel lain lewat ON UPDATE CASCADE.
            if ($email !== $akun->email) {
                Akun::where('email', $akun->email)->update(['email' => $email]); // FK profil/peminjaman ikut ter-update (CASCADE)
                $akun = Akun::findOrFail($email);
            }
            if ($password !== null && $password !== '') {
                $akun->update(['password' => $password]);
            }
            unset($data['email']);
            $profil = $profil->newQuery()->where($profil->getKeyName(), $profil->getKey())->firstOrFail();
            $profil->update($data);

            return $profil->refresh();
        });
    }

    /** @throws \RuntimeException bila akun masih dipakai histori transaksi / jadwal. */
    public function hapus(object $profil): void
    {
        try {
            DB::transaction(function () use ($profil) {
                $email = $profil->email;
                $profil->delete();
                Akun::where('email', $email)->delete();
            });
        // Foreign key RESTRICT menolak hapus bila masih dipakai peminjaman/jadwal.
        } catch (QueryException $e) {
            throw new \RuntimeException('Data tidak dapat dihapus karena sudah dipakai pada data lain (histori transaksi atau jadwal).');
        }
    }
}
