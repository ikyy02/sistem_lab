<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Services\AkunService;
use App\Services\AuthService;
use App\Support\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/** Profil Saya: lihat/ubah profil sendiri dan ubah password (password disimpan apa adanya, sesuai project). */
class ProfilController extends Controller
{
    private const WA_REGEX = '/^(?:\+62|62|0)8[1-9][0-9]{7,11}$/';

    public function index()
    {
        $me = AuthService::user();
        $profil = $this->profil($me);

        return view('profil.index', [
            'profil' => $profil,
            'roleLabel' => Role::LABELS[$me['role']],
            'idLabel' => match ($me['role']) {
                Role::MAHASISWA => 'NIM',
                Role::DOSEN => 'NUPTK/NIDN',
                default => 'ID Pegawai/NIP',
            },
            'prodi' => $profil->prodi->nama_prodi ?? null,
        ]);
    }

    public function update(Request $request, AkunService $akun)
    {
        $me = AuthService::user();
        $profil = $this->profil($me);
        $input = [
            'nama' => trim(preg_replace('/\s+/u', ' ', (string) $request->input('nama'))),
            'email' => mb_strtolower(trim((string) $request->input('email'))),
            'no_whatsapp' => preg_replace('/[\s\-]+/', '', (string) $request->input('no_whatsapp')) ?: null,
        ];
        $email = ['bail', 'required', 'email', 'max:50', Rule::unique('akuns', 'email')->ignore($profil->email, 'email')];
        if ($me['role'] === Role::MAHASISWA) {
            $email[] = 'regex:' . \App\Http\Requests\MahasiswaRequest::EMAIL_REGEX;
        }
        $v = Validator::make($input, [
            'nama' => ['bail', 'required', 'string', 'min:2', 'max:100'],
            'email' => $email,
            'no_whatsapp' => ['nullable', 'string', 'max:20', 'regex:' . self::WA_REGEX],
        ], [
            'required' => ':attribute wajib diisi.', 'min' => ':attribute minimal :min karakter.', 'max' => ':attribute maksimal :max karakter.',
            'email' => 'Format email tidak valid.', 'email.unique' => 'Email sudah terdaftar di sistem.',
            'email.regex' => 'Email mahasiswa harus menggunakan domain @mhs.politala.ac.id.',
            'no_whatsapp.regex' => 'Nomor WhatsApp tidak valid. Gunakan format 08xxxxxxxxxx.',
        ], ['nama' => 'Nama', 'email' => 'Email', 'no_whatsapp' => 'Nomor WhatsApp']);
        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        $profil = $akun->ubah($profil, $me['role'], $v->validated(), null);
        session()->put('silab_user', ['key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $profil->email] + $me);

        return redirect()->route('profil.index')->with('success', 'Profil berhasil diperbarui.');
    }

    public function password(Request $request)
    {
        $me = AuthService::user();
        $akun = Akun::findOrFail($me['email']);
        $request->validate([
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:4', 'max:100', 'confirmed', 'different:password_lama'],
        ], [
            'required' => ':attribute wajib diisi.', 'min' => ':attribute minimal :min karakter.', 'max' => ':attribute maksimal :max karakter.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak sama.',
            'password_baru.different' => 'Password baru harus berbeda dari password lama.',
        ], ['password_lama' => 'Password lama', 'password_baru' => 'Password baru']);

        if (! hash_equals((string) $akun->password, (string) $request->input('password_lama'))) {
            return back()->withErrors(['password_lama' => 'Password lama salah.']);
        }
        $akun->update(['password' => $request->input('password_baru')]);

        return redirect()->route('profil.index')->with('success', 'Password berhasil diubah.');
    }

    private function profil(array $me)
    {
        $model = Role::MODELS[$me['role']];

        return $model::where('email', $me['email'])->firstOrFail();
    }
}
