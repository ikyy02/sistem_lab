<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Services\AuthService;
use App\Support\GambarProfil;
use App\Support\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/** Profil Saya: lihat/ubah profil sendiri dan ubah password (password disimpan apa adanya, sesuai project). */
class ProfilController extends Controller
{
    public function index()
    {
        $me = AuthService::user();
        $profil = $this->profil($me);

        return view('profil.index', [
            'profil' => $profil,
            'fotoUrl' => Akun::find($me['email'])?->foto_url,
            'roleLabel' => Role::LABELS[$me['role']],
            'idLabel' => match ($me['role']) {
                Role::MAHASISWA => 'NIM',
                Role::DOSEN => 'NUPTK/NIDN',
                default => 'ID Pegawai/NIP',
            },
            'prodi' => $profil->prodi->nama_prodi ?? null,
        ]);
    }

    // Pengguna hanya dapat mengubah nomor WhatsApp; identitas akun diubah melalui pengelola akun.
    public function update(Request $request)
    {
        $me = AuthService::user();
        $profil = $this->profil($me);
        $input = ['no_whatsapp' => preg_replace('/[\s\-]+/', '', (string) $request->input('no_whatsapp')) ?: null];
        $data = Validator::make($input, [
            'no_whatsapp' => ['nullable', 'string', 'max:20', 'regex:' . \App\Support\UserKonfigurasi::WA_REGEX],
        ], [
            'no_whatsapp.regex' => 'Nomor WhatsApp tidak valid. Gunakan format 08xxxxxxxxxx.',
            'no_whatsapp.max' => 'Nomor WhatsApp maksimal 20 karakter.',
        ], ['no_whatsapp' => 'Nomor WhatsApp'])->validate();
        $profil->update($data);

        return redirect()->route('profil.index')->with('success', 'Nomor WhatsApp berhasil diperbarui.');
    }
    // Tambah/ganti foto profil: file disimpan di disk 'public' dengan nama acak, namanya dicatat pada tabel akuns.
    public function foto(Request $request)
    {
        $akun = Akun::findOrFail(AuthService::user()['email']);
        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'foto.required' => 'Pilih foto terlebih dahulu.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $file = $request->file('foto');
        $nama = Str::uuid().'.'.($file->guessExtension() ?: 'jpg');
        $file->storeAs(GambarProfil::DIR, $nama, 'public'); // storage/app/public/profil; tampil lewat public/storage (storage:link)

        // Urutan: simpan nama baru ke database, baru hapus file lama.
        $lama = $akun->foto;
        $akun->update(['foto' => $nama]);
        GambarProfil::hapus($lama);

        return redirect()->route('profil.index')->with('success', 'Foto profil berhasil diperbarui.');
    }

    // Ganti password: cek password lama, password baru minimal 8 karakter dan berbeda dari yang lama.
    public function password(Request $request)
    {
        $me = AuthService::user();
        $akun = Akun::findOrFail($me['email']);
        $request->validate([
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:8', 'max:100', 'confirmed', 'different:password_lama'],
        ], [
            'required' => ':attribute wajib diisi.', 'min' => ':attribute minimal :min karakter.', 'max' => ':attribute maksimal :max karakter.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak sama.',
            'password_baru.different' => 'Password baru harus berbeda dari password lama.',
        ], ['password_lama' => 'Password lama', 'password_baru' => 'Password baru']);

        if (! hash_equals((string) $akun->password, (string) $request->input('password_lama'))) {
            return back()->withErrors(['password_lama' => 'Password lama salah.']);
        }
        $akun->update(['password' => $request->input('password_baru'), 'wajib_ganti_password' => false]);

        return redirect()->route('profil.index')->with('success', 'Password berhasil diubah.');
    }

    // Ambil profil sesuai role pengguna login.
    private function profil(array $me)
    {
        $model = Role::MODELS[$me['role']];

        return $model::where('email', $me['email'])->firstOrFail();
    }
}
