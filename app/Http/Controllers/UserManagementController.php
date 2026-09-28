<?php

namespace App\Http\Controllers;

use App\Http\Requests\MahasiswaRequest;
use App\Services\AuthService;
use App\Support\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Kelola Data User: satu menu, empat kategori dengan tabel/kolom/form masing-masing.
 * Password disimpan apa adanya (belum di-hash) pada tahap ini; lihat AuthService.
 */
class UserManagementController extends Controller
{
    public const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    private const WA_REGEX = '/^(?:\+62|62|0)8[1-9][0-9]{7,11}$/';

    /**
     * Konfigurasi tiap kategori.
     * columns: kolom tabel [key => [label, sortable]]; search: kolom yang dicari; fields: input form.
     */
    public static function config(string $kategori): array
    {
        $wa = ['name' => 'no_whatsapp', 'label' => 'WhatsApp', 'type' => 'text', 'placeholder' => '08xxxxxxxxxx', 'required' => true];
        $email = fn ($ph = 'nama@domain.ac.id') => ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'placeholder' => $ph, 'required' => true];
        $nama = ['name' => 'nama', 'label' => 'Nama', 'type' => 'text', 'placeholder' => 'Nama lengkap', 'required' => true];
        $prodi = ['name' => 'program_studi', 'label' => 'Prodi', 'type' => 'text', 'placeholder' => 'Contoh: Teknik Informatika', 'required' => true];

        return match ($kategori) {
            Role::MAHASISWA => [
                'columns' => ['nim' => ['NIM', true], 'nama' => ['Nama', true], 'kelas' => ['Kelas', true], 'program_studi' => ['Prodi', true], 'email' => ['Email', true], 'no_whatsapp' => ['WhatsApp', false]],
                'search' => ['nim', 'nama', 'kelas', 'program_studi', 'email', 'no_whatsapp'],
                'default_sort' => 'nim',
                'fields' => [
                    ['name' => 'nim', 'label' => 'NIM', 'type' => 'text', 'placeholder' => 'Contoh: 2210101001', 'required' => true],
                    $nama,
                    ['name' => 'kelas', 'label' => 'Kelas', 'type' => 'text', 'placeholder' => 'Contoh: 3A', 'required' => true],
                    $prodi,
                    $email('nim@' . MahasiswaRequest::EMAIL_DOMAIN),
                    $wa,
                ],
            ],
            Role::DOSEN => [
                'columns' => ['nidn' => ['NIP/NIDN', true], 'nama' => ['Nama', true], 'program_studi' => ['Prodi', true], 'email' => ['Email', true], 'no_whatsapp' => ['WhatsApp', false]],
                'search' => ['nidn', 'nip', 'nama', 'program_studi', 'email', 'no_whatsapp'],
                'default_sort' => 'nama',
                'fields' => [
                    ['name' => 'nidn', 'label' => 'NIDN', 'type' => 'text', 'placeholder' => 'NIDN', 'required' => true],
                    ['name' => 'nip', 'label' => 'NIP', 'type' => 'text', 'placeholder' => 'NIP (opsional)', 'required' => false],
                    $nama, $prodi, $email(), $wa,
                ],
            ],
            Role::STAFF => [
                'columns' => ['nama' => ['Nama', true], 'program_studi' => ['Prodi', true], 'email' => ['Email', true], 'no_whatsapp' => ['WhatsApp', false]],
                'search' => ['nama', 'program_studi', 'email', 'no_whatsapp'],
                'default_sort' => 'nama',
                'fields' => [$nama, $prodi, $email(), $wa],
            ],
            default => [
                'columns' => ['nama' => ['Nama', true], 'email' => ['Email', true], 'no_whatsapp' => ['WhatsApp', false]],
                'search' => ['nama', 'email', 'no_whatsapp'],
                'default_sort' => 'nama',
                'fields' => [$nama, $email(), $wa],
            ],
        };
    }

    public function index(Request $request)
    {
        $kategori = $this->kategori($request->query('kategori'));
        $config = self::config($kategori);
        $model = Role::MODELS[$kategori];

        $search = mb_substr(trim($this->str($request->query('search'))), 0, 100);

        $sortable = array_keys(array_filter($config['columns'], fn ($c) => $c[1]));
        $sort = $this->str($request->query('sort'));
        $sort = in_array($sort, $sortable, true) ? $sort : $config['default_sort'];
        $direction = strtolower($this->str($request->query('direction'))) === 'desc' ? 'desc' : 'asc';

        $perPage = (int) $this->str($request->query('per_page'));
        $perPage = in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : self::PER_PAGE_OPTIONS[0];

        $query = $model::query();
        $words = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach (array_slice($words, 0, 5) as $word) {
            $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word) . '%';
            $query->where(function ($group) use ($config, $like) {
                foreach ($config['search'] as $col) {
                    $group->orWhereRaw("{$col} LIKE ? ESCAPE '!'", [$like]);
                }
            });
        }

        $data = $query->orderBy($sort, $direction)->orderBy('id')->paginate($perPage)->withQueryString();

        if ($data->currentPage() > $data->lastPage()) {
            return redirect()->route('kelola-user.index', array_merge($request->query(), ['page' => $data->lastPage()]));
        }

        return view('kelola-user.index', [
            'kategori' => $kategori,
            'config' => $config,
            'data' => $data,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'labels' => Role::LABELS,
            'me' => AuthService::user(),
        ]);
    }

    public function store(Request $request, string $kategori)
    {
        $model = Role::MODELS[$kategori];
        $data = $this->validated($request, $kategori, null);

        if (! $data instanceof \Illuminate\Contracts\Validation\Validator) {
            $model::create($data);

            return redirect()->route('kelola-user.index', ['kategori' => $kategori])
                ->with('success', 'Data ' . Role::LABELS[$kategori] . ' berhasil ditambahkan.');
        }

        return $this->fail($data, $kategori, 'create', null);
    }

    public function update(Request $request, string $kategori, int $id)
    {
        $record = Role::MODELS[$kategori]::findOrFail($id);
        $data = $this->validated($request, $kategori, $id);

        if (! $data instanceof \Illuminate\Contracts\Validation\Validator) {
            $record->update($data);

            return redirect()->route('kelola-user.index', $request->only(['kategori', 'search', 'sort', 'direction', 'per_page', 'page']) + ['kategori' => $kategori])
                ->with('success', 'Data ' . Role::LABELS[$kategori] . ' berhasil diperbarui.');
        }

        return $this->fail($data, $kategori, 'edit', $id);
    }

    public function destroy(Request $request, string $kategori, int $id)
    {
        $me = AuthService::user();
        if ($me && $me['role'] === $kategori && (int) $me['id'] === $id) {
            return back()->with('error', 'Akun yang sedang Anda gunakan tidak dapat dihapus.');
        }

        Role::MODELS[$kategori]::findOrFail($id)->delete();

        return redirect()->route('kelola-user.index', $request->only(['search', 'sort', 'direction', 'per_page', 'page']) + ['kategori' => $kategori])
            ->with('success', 'Data ' . Role::LABELS[$kategori] . ' berhasil dihapus.');
    }

    /** @return array<string,mixed>|\Illuminate\Contracts\Validation\Validator */
    private function validated(Request $request, string $kategori, ?int $id)
    {
        $table = (new (Role::MODELS[$kategori]))->getTable();
        $input = $this->normalize($request, $kategori);

        $emailUnique = function (string $attr, mixed $value, \Closure $fail) use ($kategori, $id) {
            foreach (Role::MODELS as $role => $model) {
                $q = $model::query()->whereRaw('LOWER(email) = ?', [mb_strtolower((string) $value)]);
                if ($role === $kategori && $id) {
                    $q->where('id', '!=', $id);
                }
                if ($q->exists()) {
                    $fail('Email sudah terdaftar di sistem.');
                    return;
                }
            }
        };

        $rules = match ($kategori) {
            Role::MAHASISWA => MahasiswaRequest::baseRules() + [
                'kelas' => ['bail', 'required', 'string', 'max:50'],
            ],
            Role::DOSEN => [
                'nidn' => ['bail', 'required', 'string', 'max:20', "unique:{$table},nidn" . ($id ? ",{$id}" : '')],
                'nip' => ['nullable', 'string', 'max:20', "unique:{$table},nip" . ($id ? ",{$id}" : '')],
                'nama' => ['bail', 'required', 'string', 'min:2', 'max:255'],
                'program_studi' => ['bail', 'required', 'string', 'max:255'],
                'email' => ['bail', 'required', 'email', 'max:255'],
                'no_whatsapp' => ['bail', 'required', 'string', 'max:20', 'regex:' . self::WA_REGEX],
            ],
            Role::STAFF => [
                'nama' => ['bail', 'required', 'string', 'min:2', 'max:255'],
                'program_studi' => ['bail', 'required', 'string', 'max:255'],
                'email' => ['bail', 'required', 'email', 'max:255'],
                'no_whatsapp' => ['bail', 'required', 'string', 'max:20', 'regex:' . self::WA_REGEX],
            ],
            default => [
                'nama' => ['bail', 'required', 'string', 'min:2', 'max:255'],
                'email' => ['bail', 'required', 'email', 'max:255'],
                'no_whatsapp' => ['bail', 'required', 'string', 'max:20', 'regex:' . self::WA_REGEX],
            ],
        };

        if ($kategori === Role::MAHASISWA) {
            $rules['nim'][] = "unique:{$table},nim" . ($id ? ",{$id}" : '');
        }
        $rules['email'][] = $emailUnique;
        $rules['password'] = [$id ? 'nullable' : 'required', 'string', 'min:4', 'max:100'];

        $validator = Validator::make($input, $rules, MahasiswaRequest::errorMessages() + [
            'no_whatsapp.regex' => 'Nomor WhatsApp tidak valid. Gunakan format 08xxxxxxxxxx.',
            'nidn.unique' => 'NIDN sudah terdaftar.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'password.min' => 'Password minimal :min karakter.',
        ], MahasiswaRequest::attributeNames() + ['kelas' => 'Kelas', 'nidn' => 'NIDN', 'nip' => 'NIP', 'password' => 'Password']);

        if ($validator->fails()) {
            return $validator;
        }

        $data = $validator->validated();
        // Password kosong saat edit = tidak diubah.
        if (empty($data['password'])) {
            unset($data['password']);
        }
        // Mahasiswa: email institusi diverifikasi oleh MahasiswaRequest::baseRules().
        return $data;
    }

    private function normalize(Request $request, string $kategori): array
    {
        $fields = array_column(self::config($kategori)['fields'], 'name');
        $in = $request->only($fields);
        $base = MahasiswaRequest::normalize($in + ['nim' => null, 'nama' => null, 'program_studi' => null, 'email' => null, 'no_whatsapp' => null]);

        $out = [];
        foreach ($fields as $f) {
            $out[$f] = $base[$f] ?? (is_string($in[$f] ?? null) ? trim(preg_replace('/\s+/u', ' ', $in[$f])) : ($in[$f] ?? null));
            if ($out[$f] === '') {
                $out[$f] = null;
            }
        }
        $out['password'] = $request->input('password');

        return $out;
    }

    private function fail($validator, string $kategori, string $mode, ?int $id)
    {
        return redirect()->route('kelola-user.index', ['kategori' => $kategori])
            ->withErrors($validator)
            ->withInput(request()->except('password'))
            ->with('open_modal', ['mode' => $mode, 'id' => $id]);
    }

    private function kategori(mixed $value): string
    {
        return is_string($value) && in_array($value, Role::ALL, true) ? $value : Role::MAHASISWA;
    }

    private function str(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
