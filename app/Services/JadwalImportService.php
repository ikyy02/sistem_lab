<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\Ruangan;
use App\Support\ExcelFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

class JadwalImportService
{
    public const MAX_ROWS = 500;
    public const MAX_ERRORS = 100;

    public static function columns(): array
    {
        return ['Kode MK' => 'kode_mk', 'Kelas' => 'nama_kelas', 'NUPTK/NIDN' => 'nuptk_nidn',
            'Ruangan' => 'nama_ruangan', 'Hari' => 'hari', 'Jam Mulai' => 'jam_mulai',
            'Jam Selesai' => 'jam_selesai', 'Tahun Akademik' => 'tahun_akademik',
            'Semester' => 'semester', 'Status' => 'status'];
    }

    /** @return array{0:Xlsx,1:string} */
    public function template(int $idProdi): array
    {
        $prodi = Prodi::find($idProdi);
        abort_if(! $prodi, 404);
        $book = new Spreadsheet();
        $data = $book->getActiveSheet()->setTitle('Data');
        $headers = array_keys(self::columns());
        $data->fromArray([$headers], null, 'A1');
        $data->freezePane('A2');
        $data->getStyle('A1:J1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $data->getStyle('A1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF17365D');
        foreach ([16, 14, 18, 30, 14, 14, 14, 20, 14, 14] as $i => $width) $data->getColumnDimensionByColumn($i + 1)->setWidth($width);
        foreach (['A', 'B', 'C', 'D', 'H'] as $column) $data->getStyle($column . '2:' . $column . '501')->getNumberFormat()->setFormatCode('@');
        $guide = $book->createSheet()->setTitle('Petunjuk');
        $values = [
            ['Panduan Impor Jadwal Perkuliahan'],
            ['Prodi', $prodi->nama_prodi],
            ['Isi sheet Data mulai baris ke-2. Jangan ubah nama kolom. Maksimal 500 baris per file.'],
            ['Gunakan kode MK, nama kelas, NUPTK/NIDN, dan ruangan yang tercantum pada data master prodi Anda.'],
            ['Hari: ' . implode(', ', Jadwal::HARI) . '. Semester: ganjil atau genap. Status: aktif atau dibatalkan.'],
            ['Jam gunakan format 24 jam HH:MM, contoh 08:00 dan 09:30. Jam mulai harus lebih awal dari jam selesai.'],
            ['Tahun Akademik gunakan format YYYY/YYYY berurutan, contoh 2026/2027.'],
            ['Jadwal aktif yang bertabrakan ruangan/hari/jam akan ditolak. Jadwal baru masuk ke periode yang sedang berjalan.'],
            ['Jika satu baris gagal atau bentrok, seluruh file dibatalkan dan tidak ada baris yang disimpan.'],
            [''],
            ['Data master yang dapat dipilih (gunakan nilai sesuai kolom):'],
            ['Kode MK', 'Mata Kuliah', 'Kelas', 'NUPTK/NIDN', 'Nama Dosen', 'Ruangan'],
        ];
        $courses = MataKuliah::batasProdi($idProdi)->orderBy('kode_mk')->get(['kode_mk', 'nama_mk']);
        $classes = Kelas::where('id_prodi', $idProdi)->orderBy('nama_kelas')->get(['nama_kelas']);
        $teachers = Dosen::batasProdi($idProdi)->orderBy('nama')->get(['nuptk_nidn', 'nama']);
        $rooms = Ruangan::orderBy('nama_ruangan')->get(['nama_ruangan']);
        $max = max($courses->count(), $classes->count(), $teachers->count(), $rooms->count(), 1);
        for ($i = 0; $i < $max; $i++) {
            $course = $courses->get($i); $teacher = $teachers->get($i); $room = $rooms->get($i);
            $values[] = [$course?->kode_mk, $course?->nama_mk, $classes->get($i)?->nama_kelas,
                $teacher?->nuptk_nidn, $teacher?->nama, $room?->nama_ruangan];
        }
        $guide->fromArray($values, null, 'A1');
        $headerRow = 12;
        $guide->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $guide->getStyle('A' . $headerRow . ':F' . $headerRow)->getFont()->setBold(true);
        $guide->getStyle('A2:F' . count($values))->getAlignment()->setWrapText(true);
        foreach (['A' => 18, 'B' => 36, 'C' => 16, 'D' => 20, 'E' => 30, 'F' => 32] as $column => $width) $guide->getColumnDimension($column)->setWidth($width);
        $book->setActiveSheetIndex(0);
        return [new Xlsx($book), 'template-impor-jadwal.xlsx'];
    }

    /** @return array{success:bool,total:int,inserted?:int,error_count?:int,errors?:array} */
    public function import(UploadedFile $file, int $idProdi, JadwalService $jadwalService): array
    {
        $rows = ExcelFile::rows($file, self::MAX_ROWS + 50);
        $index = ExcelFile::matchHeaders(array_shift($rows) ?? [], self::columns());
        $courses = MataKuliah::batasProdi($idProdi)->get(['id_mata_kuliah', 'kode_mk']);
        $classes = Kelas::where('id_prodi', $idProdi)->get(['id_kelas', 'nama_kelas']);
        $teachers = Dosen::batasProdi($idProdi)->get(['nuptk_nidn']);
        $rooms = Ruangan::get(['id_ruangan', 'nama_ruangan']);
        $map = static function ($items, string $key, string $value): array {
            $result = [];
            foreach ($items as $item) $result[mb_strtolower(trim((string) $item->{$key}))] = $item->{$value};
            return $result;
        };
        $courseMap = $map($courses, 'kode_mk', 'id_mata_kuliah');
        $classMap = $map($classes, 'nama_kelas', 'id_kelas');
        $teacherMap = $map($teachers, 'nuptk_nidn', 'nuptk_nidn');
        $roomMap = $map($rooms, 'nama_ruangan', 'id_ruangan');
        $valid = $errors = [];
        $total = 0;
        foreach ($rows as $i => $row) {
            if (! array_filter($row, fn ($v) => trim((string) $v) !== '')) continue;
            $line = $i + 2;
            if (++$total > self::MAX_ROWS) throw new RuntimeException('Maksimal ' . self::MAX_ROWS . ' baris jadwal per file.');
            $get = fn (string $field): string => trim((string) ($row[$index[$field]] ?? ''));
            $code = mb_strtolower($get('kode_mk')); $class = mb_strtolower($get('nama_kelas'));
            $teacher = mb_strtolower($get('nuptk_nidn')); $room = mb_strtolower($get('nama_ruangan'));
            $day = mb_strtolower($get('hari')); $semester = mb_strtolower($get('semester')); $status = mb_strtolower($get('status'));
            $messages = [];
            if (! isset($courseMap[$code])) $messages[] = 'Kode MK tidak ditemukan pada prodi Anda.';
            if (! isset($classMap[$class])) $messages[] = 'Kelas tidak ditemukan pada prodi Anda.';
            if (! isset($teacherMap[$teacher])) $messages[] = 'NUPTK/NIDN tidak ditemukan pada prodi Anda.';
            if (! isset($roomMap[$room])) $messages[] = 'Ruangan tidak ditemukan.';
            if (! in_array($day, Jadwal::HARI, true)) $messages[] = 'Hari harus Senin sampai Sabtu.';
            if (! in_array($semester, Jadwal::SEMESTER, true)) $messages[] = 'Semester harus ganjil atau genap.';
            if (! in_array($status, Jadwal::STATUS, true)) $messages[] = 'Status harus aktif atau dibatalkan.';
            $start = $this->time($get('jam_mulai')); $end = $this->time($get('jam_selesai'));
            if ($start === null || $end === null) $messages[] = 'Jam gunakan format 24 jam HH:MM (contoh 08:00).';
            $year = $get('tahun_akademik');
            if (! preg_match('/^\d{4}\/\d{4}$/', $year)) $messages[] = 'Tahun Akademik harus berformat YYYY/YYYY.';
            if ($messages) { $errors[] = ['row' => $line, 'messages' => $messages]; continue; }
            $valid[] = ['line' => $line, 'data' => [
                'id_mata_kuliah' => $courseMap[$code], 'id_kelas' => $classMap[$class], 'nuptk_nidn' => $teacherMap[$teacher],
                'id_ruangan' => $roomMap[$room], 'hari' => $day, 'jam_mulai' => $start, 'jam_selesai' => $end,
                'tahun_akademik' => $year, 'semester' => $semester, 'status' => $status,
            ]];
        }
        if ($total === 0) throw new RuntimeException('File tidak berisi jadwal. Isi sheet Data mulai baris ke-2.');
        if ($errors) return ['success' => false, 'total' => $total, 'error_count' => count($errors), 'errors' => array_slice($errors, 0, self::MAX_ERRORS)];
        try {
            DB::transaction(function () use ($valid, $jadwalService) {
                foreach ($valid as $item) {
                    try { $jadwalService->simpan($item['data']); }
                    catch (ValidationException $e) { throw new JadwalImportRowException($item['line'], implode(' ', array_merge(...array_values($e->errors())))); }
                }
            });
        } catch (JadwalImportRowException $e) {
            return ['success' => false, 'total' => $total, 'error_count' => 1, 'errors' => [['row' => $e->row, 'messages' => [$e->getMessage()]]]];
        }
        return ['success' => true, 'total' => $total, 'inserted' => count($valid)];
    }

    private function time(string $value): ?string
    {
        if ($value === '') return null;
        if (is_numeric($value)) { $seconds = (int) round(((float) $value - floor((float) $value)) * 86400); return sprintf('%02d:%02d', intdiv($seconds, 3600) % 24, intdiv($seconds % 3600, 60)); }
        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $value, $m) && (int) $m[1] < 24 && (int) $m[2] < 60) return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        return null;
    }
}

class JadwalImportRowException extends RuntimeException
{
    public function __construct(public int $row, string $message) { parent::__construct($message); }
}
