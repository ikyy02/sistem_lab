<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ProfilMahasiswa;
use App\Models\ProfilDosen;
use App\Models\ProfilLaboran;
use App\Models\ProfilStaf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Hanya laboran yang bisa akses controller ini.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->isLaboran()) {
                abort(403, 'Anda tidak memiliki akses ke halaman ini.');
            }
            return $next($request);
        });
    }

    /**
     * Tampilkan daftar semua user.
     */
    public function index()
    {
        $users = User::with([
            'profilMahasiswa',
            'profilDosen',
            'profilLaboran',
            'profilStaf'
        ])->orderBy('created_at', 'desc')->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Form create user baru.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Simpan user baru beserta profilnya.
     */
    public function store(Request $request)
    {
        // Validasi dasar
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => ['required', Rule::in([
                User::ROLE_MAHASISWA,
                User::ROLE_DOSEN,
                User::ROLE_STAF_PRODI,
                User::ROLE_LABORAN,
            ])],
            'no_hp'    => 'nullable|string|max:20',
            'status'   => ['required', Rule::in([User::STATUS_AKTIF, User::STATUS_NONAKTIF])],
        ]);

        // Validasi profil sesuai role
        $this->validateProfileByRole($request);

        DB::beginTransaction();
        try {
            // 1. Buat user
            $user = User::create([
                'name'     => $validated['name'],
                'username' => $validated['username'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => $validated['role'],
                'no_hp'    => $validated['no_hp'] ?? null,
                'status'   => $validated['status'],
            ]);

            // 2. Buat profil sesuai role
            $this->createProfileByRole($user, $request);

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan user: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail user.
     */
    public function show(User $user)
    {
        $user->load([
            'profilMahasiswa',
            'profilDosen',
            'profilLaboran',
            'profilStaf'
        ]);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Form edit user.
     */
    public function edit(User $user)
    {
        $user->load([
            'profilMahasiswa',
            'profilDosen',
            'profilLaboran',
            'profilStaf'
        ]);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user dan profilnya.
     */
    public function update(Request $request, User $user)
    {
        // Validasi dasar
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role'     => ['required', Rule::in([
                User::ROLE_MAHASISWA,
                User::ROLE_DOSEN,
                User::ROLE_STAF_PRODI,
                User::ROLE_LABORAN,
            ])],
            'no_hp'    => 'nullable|string|max:20',
            'status'   => ['required', Rule::in([User::STATUS_AKTIF, User::STATUS_NONAKTIF])],
        ]);

        // Validasi profil sesuai role
        $this->validateProfileByRole($request);

        DB::beginTransaction();
        try {
            // 1. Update user
            $user->update([
                'name'     => $validated['name'],
                'username' => $validated['username'],
                'email'    => $validated['email'],
                'role'     => $validated['role'],
                'no_hp'    => $validated['no_hp'] ?? null,
                'status'   => $validated['status'],
            ]);

            // Update password jika diisi
            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            // 2. Hapus profil lama jika role berubah
            if ($user->wasChanged('role')) {
                $this->deleteAllProfiles($user);
            }

            // 3. Update atau buat profil baru sesuai role
            $this->updateOrCreateProfileByRole($user, $request);

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui user: ' . $e->getMessage());
        }
    }

    /**
     * Hapus user dan profilnya.
     */
    public function destroy(User $user)
    {
        DB::beginTransaction();
        try {
            // Hapus semua profil
            $this->deleteAllProfiles($user);

            // Hapus user
            $user->delete();

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    // -----------------------------------------------------------------------
    // Helper Methods
    // -----------------------------------------------------------------------

    /**
     * Validasi field profil sesuai role yang dipilih.
     */
    private function validateProfileByRole(Request $request)
    {
        $role = $request->input('role');

        if ($role === User::ROLE_MAHASISWA) {
            $request->validate([
                'nim'         => 'required|string|max:20',
                'prodi'       => 'required|string|max:100',
                'angkatan'    => 'required|integer|min:2000|max:' . (date('Y') + 1),
                'alamat'      => 'nullable|string',
            ]);
        } elseif ($role === User::ROLE_DOSEN) {
            $request->validate([
                'nidn'        => 'required|string|max:20',
                'prodi'       => 'required|string|max:100',
                'jabatan'     => 'nullable|string|max:100',
            ]);
        } elseif ($role === User::ROLE_LABORAN) {
            $request->validate([
                'nip'         => 'required|string|max:20',
                'lab'         => 'required|string|max:100',
            ]);
        } elseif ($role === User::ROLE_STAF_PRODI) {
            $request->validate([
                'nip'         => 'required|string|max:20',
                'prodi'       => 'required|string|max:100',
                'jabatan'     => 'nullable|string|max:100',
            ]);
        }
    }

    /**
     * Buat profil baru sesuai role.
     */
    private function createProfileByRole(User $user, Request $request)
    {
        if ($user->role === User::ROLE_MAHASISWA) {
            ProfilMahasiswa::create([
                'user_id'   => $user->id,
                'nim'       => $request->nim,
                'prodi'     => $request->prodi,
                'angkatan'  => $request->angkatan,
                'alamat'    => $request->alamat,
            ]);
        } elseif ($user->role === User::ROLE_DOSEN) {
            ProfilDosen::create([
                'user_id'   => $user->id,
                'nidn'      => $request->nidn,
                'prodi'     => $request->prodi,
                'jabatan'   => $request->jabatan,
            ]);
        } elseif ($user->role === User::ROLE_LABORAN) {
            ProfilLaboran::create([
                'user_id'   => $user->id,
                'nip'       => $request->nip,
                'lab'       => $request->lab,
            ]);
        } elseif ($user->role === User::ROLE_STAF_PRODI) {
            ProfilStaf::create([
                'user_id'   => $user->id,
                'nip'       => $request->nip,
                'prodi'     => $request->prodi,
                'jabatan'   => $request->jabatan,
            ]);
        }
    }

    /**
     * Update atau buat profil sesuai role.
     */
    private function updateOrCreateProfileByRole(User $user, Request $request)
    {
        if ($user->role === User::ROLE_MAHASISWA) {
            ProfilMahasiswa::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nim'      => $request->nim,
                    'prodi'    => $request->prodi,
                    'angkatan' => $request->angkatan,
                    'alamat'   => $request->alamat,
                ]
            );
        } elseif ($user->role === User::ROLE_DOSEN) {
            ProfilDosen::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nidn'    => $request->nidn,
                    'prodi'   => $request->prodi,
                    'jabatan' => $request->jabatan,
                ]
            );
        } elseif ($user->role === User::ROLE_LABORAN) {
            ProfilLaboran::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip' => $request->nip,
                    'lab' => $request->lab,
                ]
            );
        } elseif ($user->role === User::ROLE_STAF_PRODI) {
            ProfilStaf::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip'     => $request->nip,
                    'prodi'   => $request->prodi,
                    'jabatan' => $request->jabatan,
                ]
            );
        }
    }

    /**
     * Hapus semua profil user (untuk role switching).
     */
    private function deleteAllProfiles(User $user)
    {
        $user->profilMahasiswa()?->delete();
        $user->profilDosen()?->delete();
        $user->profilLaboran()?->delete();
        $user->profilStaf()?->delete();
    }
}
