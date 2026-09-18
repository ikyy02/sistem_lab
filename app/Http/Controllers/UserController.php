<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Daftar semua user dengan pencarian dan filter.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Pencarian berdasarkan nama, email, NIM, NIDN, atau NIP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('nidn', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Tampilkan form tambah user baru.
     * Hanya Laboran yang boleh.
     */
    public function create()
    {
        $this->authorizeLaboran();
        return view('users.create');
    }

    /**
     * Simpan user baru ke database.
     * Hanya Laboran yang boleh.
     */
    public function store(Request $request)
    {
        $this->authorizeLaboran();
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', Rule::in(['mahasiswa', 'dosen', 'staf_prodi', 'laboran'])],
            'nim'      => [
                Rule::requiredIf($request->role === 'mahasiswa'),
                'nullable', 'string', 'max:20',
            ],
            'nidn'     => [
                Rule::requiredIf($request->role === 'dosen'),
                'nullable', 'string', 'max:20',
            ],
            'nip'      => [
                Rule::requiredIf(in_array($request->role, ['staf_prodi', 'laboran'])),
                'nullable', 'string', 'max:20',
            ],
            'no_hp'    => ['nullable', 'string', 'max:20'],
            'status'   => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], $this->messages());

        // Bersihkan field identitas yang tidak relevan
        $data = $validated;
        $data['password'] = Hash::make($validated['password']);

        if ($data['role'] !== 'mahasiswa')  $data['nim']  = null;
        if ($data['role'] !== 'dosen')       $data['nidn'] = null;
        if (! in_array($data['role'], ['staf_prodi', 'laboran'])) $data['nip'] = null;

        User::create($data);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail satu user.
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Tampilkan form edit user.
     * Hanya Laboran yang boleh.
     */
    public function edit(User $user)
    {
        $this->authorizeLaboran();
        return view('users.edit', compact('user'));
    }

    /**
     * Update data user di database.
     * Hanya Laboran yang boleh.
     */
    public function update(Request $request, User $user)
    {
        $this->authorizeLaboran();
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', Rule::in(['mahasiswa', 'dosen', 'staf_prodi', 'laboran'])],
            'nim'      => [
                Rule::requiredIf($request->role === 'mahasiswa'),
                'nullable', 'string', 'max:20',
            ],
            'nidn'     => [
                Rule::requiredIf($request->role === 'dosen'),
                'nullable', 'string', 'max:20',
            ],
            'nip'      => [
                Rule::requiredIf(in_array($request->role, ['staf_prodi', 'laboran'])),
                'nullable', 'string', 'max:20',
            ],
            'no_hp'    => ['nullable', 'string', 'max:20'],
            'status'   => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], $this->messages());

        $data = $validated;

        // Update password hanya jika diisi
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Bersihkan field identitas yang tidak relevan dengan role baru
        if ($data['role'] !== 'mahasiswa')  $data['nim']  = null;
        if ($data['role'] !== 'dosen')       $data['nidn'] = null;
        if (! in_array($data['role'], ['staf_prodi', 'laboran'])) $data['nip'] = null;

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Data user berhasil diperbarui.');
    }

    /**
     * Hapus user dari database.
     * Hanya Laboran yang boleh.
     */
    public function destroy(User $user)
    {
        $this->authorizeLaboran();

        // Laboran tidak dapat menghapus dirinya sendiri
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    private function messages(): array
    {
        return [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'username.required'  => 'Username wajib diisi.',
            'username.unique'    => 'Username sudah digunakan.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah terdaftar.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required'      => 'Role wajib dipilih.',
            'role.in'            => 'Role tidak valid.',
            'nim.required'       => 'NIM wajib diisi untuk Mahasiswa.',
            'nidn.required'      => 'NIDN wajib diisi untuk Dosen.',
            'nip.required'       => 'NIP wajib diisi untuk Staf Prodi / Laboran.',
            'status.required'    => 'Status wajib dipilih.',
        ];
    }

    /**
     * Pastikan user yang sedang login adalah Laboran.
     * Dipanggil di action yang hanya boleh diakses Laboran.
     */
    private function authorizeLaboran(): void
    {
        if (! auth()->user()->isLaboran()) {
            abort(403, 'Hanya Laboran yang dapat melakukan aksi ini.');
        }
    }
}
