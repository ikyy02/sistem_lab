<?php

namespace App\Services;

use App\Models\Jadwal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Jadwal kuliah. Jadwal 'aktif' pada kombinasi (ruangan, hari, tahun_akademik, semester) tidak boleh beririsan waktu;
 * jadwal 'dibatalkan' tidak memblokir ruangan. Jam yang bertemu (10:00 dan 10:00) bukan bentrok.
 */
class JadwalService
{
    public function simpan(array $data, ?Jadwal $jadwal = null): Jadwal
    {
        $v = Validator::make($data, [
            'id_mata_kuliah' => ['nullable', 'integer', Rule::exists('mata_kuliahs', 'id_mata_kuliah')],
            'id_kelas' => ['required', 'integer', Rule::exists('kelas', 'id_kelas')],
            'nuptk_nidn' => ['required', 'string', Rule::exists('dosens', 'nuptk_nidn')],
            'id_ruangan' => ['required', 'integer', Rule::exists('ruangans', 'id_ruangan')],
            'hari' => ['required', Rule::in(Jadwal::HARI)],
            'jam_mulai' => ['required', 'date_format:H:i:s,H:i'],
            'jam_selesai' => ['required', 'date_format:H:i:s,H:i'],
            'status' => ['required', Rule::in(['aktif', 'dibatalkan'])],
            'tahun_akademik' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
        ], [
            'tahun_akademik.regex' => 'Tahun akademik berformat 2025/2026.',
        ]);
        $v->after(function ($v) use ($data) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            [$a, $b] = array_map('intval', explode('/', $data['tahun_akademik']));
            if ($b !== $a + 1) {
                $v->errors()->add('tahun_akademik', 'Tahun akademik harus berurutan, contoh 2025/2026.');
            }
            if (strtotime($data['jam_mulai']) >= strtotime($data['jam_selesai'])) {
                $v->errors()->add('jam_selesai', 'Jam mulai harus sebelum jam selesai.');
            }
        });
        $valid = $v->validate();
        $valid['jam_mulai'] = date('H:i:s', strtotime($valid['jam_mulai']));
        $valid['jam_selesai'] = date('H:i:s', strtotime($valid['jam_selesai']));

        return DB::transaction(function () use ($valid, $jadwal) {
            if ($valid['status'] === 'aktif') {
                $bentrok = Jadwal::where('status', 'aktif')
                    ->where('id_ruangan', $valid['id_ruangan'])->where('hari', $valid['hari'])
                    ->where('tahun_akademik', $valid['tahun_akademik'])->where('semester', $valid['semester'])
                    ->when($jadwal, fn ($q) => $q->where('id_jadwal', '!=', $jadwal->id_jadwal))
                    ->where('jam_mulai', '<', $valid['jam_selesai'])->where('jam_selesai', '>', $valid['jam_mulai'])
                    ->lockForUpdate()->exists();
                if ($bentrok) {
                    throw ValidationException::withMessages(['jam_mulai' => 'Jadwal bentrok dengan jadwal aktif lain pada ruangan, hari, dan semester yang sama.']);
                }
            }
            if ($jadwal) {
                $jadwal->update($valid);

                return $jadwal->refresh();
            }

            return Jadwal::create($valid);
        });
    }
}
