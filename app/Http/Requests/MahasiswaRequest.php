<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi data mahasiswa.
 *
 * Kelas ini dipakai oleh:
 *  - MahasiswaController@store  (tambah manual)
 *  - MahasiswaController@update (edit)
 *  - MahasiswaImportService     (import Excel, lewat baseRules() + normalize())
 *
 * Dengan begitu aturan validasi tambah manual dan import Excel selalu sama.
 */
class MahasiswaRequest extends FormRequest
{
    /** Domain email institusi mahasiswa: nama@mhs.politala.ac.id */
    public const EMAIL_DOMAIN = 'mhs.politala.ac.id';

    private const EMAIL_REGEX = '/^[A-Za-z0-9._%+\-]+@mhs\.politala\.ac\.id$/i';

    /** Nomor seluler Indonesia: 08xx..., 628xx..., atau +628xx... (total 10-14 digit) */
    private const WHATSAPP_REGEX = '/^(?:\+62|62|0)8[1-9][0-9]{7,11}$/';

    private const NIM_REGEX = '/^[A-Za-z0-9]+$/';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan lengkap untuk form (termasuk pengecekan duplikat NIM & email ke database).
     * Saat edit, data milik mahasiswa yang sedang diedit dikecualikan dari pengecekan.
     */
    public function rules(): array
    {
        $ignoreId = $this->route('mahasiswa')?->id;

        $rules = self::baseRules();
        $rules['nim'][] = Rule::unique('mahasiswas', 'nim')->ignore($ignoreId);
        $rules['email'][] = Rule::unique('mahasiswas', 'email')->ignore($ignoreId);

        return $rules;
    }

    /**
     * Aturan format tanpa pengecekan unique ke database.
     * Import Excel memeriksa duplikat sendiri secara massal (lebih efisien daripada 1 query per baris).
     *
     * 'bail' membuat hanya satu pesan error (yang pertama) yang muncul per kolom.
     */
    public static function baseRules(): array
    {
        return [
            'nim' => ['bail', 'required', 'string', 'max:20', 'regex:' . self::NIM_REGEX],
            'nama' => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'program_studi' => ['bail', 'required', 'string', 'max:255'],
            'no_whatsapp' => ['bail', 'required', 'string', 'max:20', 'regex:' . self::WHATSAPP_REGEX],
            'email' => ['bail', 'required', 'string', 'max:255', 'email', 'regex:' . self::EMAIL_REGEX],
        ];
    }

    /**
     * Rapikan input sebelum divalidasi & disimpan, agar data di database konsisten:
     * spasi berlebih dibuang, email jadi huruf kecil, nomor WhatsApp tanpa spasi/tanda hubung.
     */
    public static function normalize(array $input): array
    {
        $squish = static function (mixed $value): mixed {
            if (! is_string($value)) {
                return $value;
            }

            // \s + /u juga menangkap spasi non-breaking hasil copy-paste dari Excel/Web
            return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
        };

        $nim = $squish($input['nim'] ?? null);
        $nama = $squish($input['nama'] ?? null);
        $prodi = $squish($input['program_studi'] ?? null);
        $email = $squish($input['email'] ?? null);
        $wa = $squish($input['no_whatsapp'] ?? null);

        return [
            'nim' => $nim,
            'nama' => $nama,
            'program_studi' => $prodi,
            'email' => is_string($email) ? mb_strtolower($email) : $email,
            'no_whatsapp' => is_string($wa) ? preg_replace('/[\s\-\.\(\)]/', '', $wa) : $wa,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(self::normalize($this->only([
            'nim', 'nama', 'program_studi', 'no_whatsapp', 'email',
        ])));
    }

    public function messages(): array
    {
        return self::errorMessages();
    }

    public function attributes(): array
    {
        return self::attributeNames();
    }

    public static function errorMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'min' => ':attribute minimal :min karakter.',

            'nim.regex' => 'NIM hanya boleh berisi huruf dan angka, tanpa spasi atau simbol.',
            'nim.unique' => 'NIM sudah terdaftar di sistem.',

            'no_whatsapp.regex' => 'Nomor WhatsApp tidak valid. Gunakan format 08xxxxxxxxxx (atau diawali +62 / 62).',

            'email.email' => 'Format email tidak valid.',
            'email.regex' => 'Email harus menggunakan format nama@' . self::EMAIL_DOMAIN . '.',
            'email.unique' => 'Email sudah terdaftar di sistem.',
        ];
    }

    public static function attributeNames(): array
    {
        return [
            'nim' => 'NIM',
            'nama' => 'Nama',
            'program_studi' => 'Program Studi',
            'no_whatsapp' => 'Nomor WhatsApp',
            'email' => 'Email',
        ];
    }
}
