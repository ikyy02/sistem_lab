<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Services\MahasiswaTemplateService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * Pengujian fitur Kelola Mahasiswa: search, sorting, pagination, tambah manual,
 * template Excel, dan import Excel massal.
 *
 * Jalankan: php artisan test --filter=MahasiswaKelolaTest
 * (membutuhkan: composer require phpoffice/phpspreadsheet)
 */
class MahasiswaKelolaTest extends TestCase
{
    use RefreshDatabase;

    private const HEADERS = ['NIM', 'Nama', 'Program Studi', 'No WhatsApp', 'Email'];

    // ------------------------------------------------------------------ helpers

    private function student(array $overrides = []): Mahasiswa
    {
        static $n = 0;
        $n++;

        return Mahasiswa::create($overrides + [
            'nim' => sprintf('22101%05d', $n),
            'nama' => "Mahasiswa {$n}",
            'program_studi' => 'Teknik Informatika',
            'email' => "mhs{$n}@mhs.politala.ac.id",
            'no_whatsapp' => sprintf('0812345%05d', $n),
        ]);
    }

    private function validRow(int $i, array $overrides = []): array
    {
        return array_replace([
            'nim' => sprintf('33101%05d', $i),
            'nama' => "Import {$i}",
            'prodi' => 'Akuntansi',
            'wa' => sprintf('0857123%05d', $i),
            'email' => "import{$i}@mhs.politala.ac.id",
        ], $overrides);
    }

    /** Buat file .xlsx dari baris data. Semua nilai ditulis sebagai teks kecuali diberi tipe angka. */
    private function excel(array $rows, ?array $headers = self::HEADERS): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Mahasiswa');

        $all = $headers === null ? $rows : array_merge([$headers], $rows);

        foreach ($all as $r => $cells) {
            foreach (array_values($cells) as $c => $value) {
                $coordinate = Coordinate::stringFromColumnIndex($c + 1) . ($r + 1);

                if (is_int($value)) {
                    $sheet->setCellValue($coordinate, $value); // angka sungguhan
                } else {
                    $sheet->setCellValueExplicit($coordinate, (string) $value, DataType::TYPE_STRING);
                }
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        (new Xlsx($spreadsheet))->save($path);
        $content = file_get_contents($path);
        unlink($path);

        return UploadedFile::fake()->createWithContent('mahasiswa.xlsx', $content);
    }

    private function import(UploadedFile $file)
    {
        return $this->post(route('mahasiswa.import'), ['file' => $file]);
    }

    // ------------------------------------------------------------------ search

    public function test_search_finds_by_nim_nama_program_studi_and_email(): void
    {
        $this->student(['nim' => '2210101001', 'nama' => 'Budi Santoso', 'program_studi' => 'Teknik Informatika', 'email' => 'budi@mhs.politala.ac.id']);
        $this->student(['nim' => '2210101002', 'nama' => 'Siti Aminah', 'program_studi' => 'Akuntansi', 'email' => 'siti@mhs.politala.ac.id']);

        $names = fn (string $term) => collect($this->get(route('mahasiswa.index', ['search' => $term]))
            ->viewData('data')->items())->pluck('nama')->all();

        $this->assertSame(['Budi Santoso'], $names('2210101001'));        // NIM
        $this->assertSame(['Siti Aminah'], $names('aminah'));             // nama
        $this->assertSame(['Siti Aminah'], $names('akuntansi'));          // program studi
        $this->assertSame(['Budi Santoso'], $names('budi@mhs'));          // email
        $this->assertSame(['Budi Santoso'], $names('budi informatika'));  // beberapa kata
        $this->assertSame([], $names('tidak-ada'));
    }

    public function test_search_treats_percent_and_underscore_as_plain_text(): void
    {
        $this->student(['nama' => 'Andi']);
        $this->student(['nama' => 'Budi']);

        $items = $this->get(route('mahasiswa.index', ['search' => '%']))->viewData('data')->items();

        $this->assertCount(0, $items);
    }

    // ------------------------------------------------------------------ sorting

    public function test_sorting_by_nama_ascending_and_descending(): void
    {
        $this->student(['nama' => 'Budi']);
        $this->student(['nama' => 'Citra']);
        $this->student(['nama' => 'Andi']);

        $order = fn (string $dir) => collect($this->get(route('mahasiswa.index', ['sort' => 'nama', 'direction' => $dir]))
            ->viewData('data')->items())->pluck('nama')->all();

        $this->assertSame(['Andi', 'Budi', 'Citra'], $order('asc'));
        $this->assertSame(['Citra', 'Budi', 'Andi'], $order('desc'));
    }

    public function test_sorting_by_nim_and_program_studi(): void
    {
        $this->student(['nim' => '300', 'program_studi' => 'B']);
        $this->student(['nim' => '100', 'program_studi' => 'C']);
        $this->student(['nim' => '200', 'program_studi' => 'A']);

        $col = fn (string $sort, string $dir, string $field) => collect($this->get(route('mahasiswa.index', ['sort' => $sort, 'direction' => $dir]))
            ->viewData('data')->items())->pluck($field)->all();

        $this->assertSame(['100', '200', '300'], $col('nim', 'asc', 'nim'));
        $this->assertSame(['C', 'B', 'A'], $col('program_studi', 'desc', 'program_studi'));
    }

    public function test_invalid_sort_parameters_fall_back_to_defaults(): void
    {
        $this->student(['nim' => '200']);
        $this->student(['nim' => '100']);

        $response = $this->get(route('mahasiswa.index', ['sort' => 'password; drop table', 'direction' => 'sideways']));

        $response->assertOk();
        $this->assertSame(['100', '200'], collect($response->viewData('data')->items())->pluck('nim')->all());
    }

    // ------------------------------------------------------------------ pagination

    public function test_per_page_follows_the_selected_option(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->student();
        }

        $count = fn (array $q) => count($this->get(route('mahasiswa.index', $q))->viewData('data')->items());

        $this->assertSame(10, $count([]));                    // default
        $this->assertSame(25, $count(['per_page' => 25]));
        $this->assertSame(30, $count(['per_page' => 50]));
        $this->assertSame(30, $count(['per_page' => 100]));
        $this->assertSame(10, $count(['per_page' => 7]));     // nilai tidak dikenal -> default
        $this->assertSame(5, $count(['per_page' => 25, 'page' => 2]));
    }

    public function test_page_beyond_last_page_redirects_to_last_page(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->student();
        }

        $this->get(route('mahasiswa.index', ['per_page' => 25, 'page' => 99]))
            ->assertRedirect(route('mahasiswa.index', ['per_page' => 25, 'page' => 2]));
    }

    public function test_index_shows_previous_and_next_navigation_and_keeps_filters_in_links(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->student();
        }

        $this->get(route('mahasiswa.index', ['per_page' => 10, 'sort' => 'nama']))
            ->assertOk()
            ->assertSee('Previous')
            ->assertSee('Next')
            ->assertSee('sort=nama', false)
            ->assertSee('Detail')
            ->assertSee('Edit')
            ->assertSee('Hapus');
    }

    // ------------------------------------------------------------------ tambah manual

    public function test_store_creates_student_with_valid_data(): void
    {
        $this->post(route('mahasiswa.store'), [
            'nim' => '2210101001',
            'nama' => 'Budi Santoso',
            'program_studi' => 'Teknik Informatika',
            'no_whatsapp' => '0812-3456-7890',
            'email' => 'Budi.Santoso@MHS.politala.ac.id',
        ])->assertRedirect(route('mahasiswa.index'));

        // email disimpan huruf kecil, nomor WhatsApp dibersihkan dari tanda hubung
        $this->assertDatabaseHas('mahasiswas', [
            'nim' => '2210101001',
            'email' => 'budi.santoso@mhs.politala.ac.id',
            'no_whatsapp' => '081234567890',
        ]);
    }

    public function test_store_rejects_duplicate_nim_and_non_institutional_email(): void
    {
        $this->student(['nim' => '2210101001']);

        $base = [
            'nim' => '2210101099',
            'nama' => 'Budi',
            'program_studi' => 'TI',
            'no_whatsapp' => '081234567890',
            'email' => 'budi@mhs.politala.ac.id',
        ];

        $this->post(route('mahasiswa.store'), array_replace($base, ['nim' => '2210101001']))
            ->assertSessionHasErrors('nim');

        $this->post(route('mahasiswa.store'), array_replace($base, ['email' => 'budi@gmail.com']))
            ->assertSessionHasErrors('email');

        $this->post(route('mahasiswa.store'), array_replace($base, ['email' => 'budi@politala.ac.id']))
            ->assertSessionHasErrors('email');

        $this->post(route('mahasiswa.store'), array_replace($base, ['no_whatsapp' => '12345']))
            ->assertSessionHasErrors('no_whatsapp');

        $this->post(route('mahasiswa.store'), [])
            ->assertSessionHasErrors(['nim', 'nama', 'program_studi', 'no_whatsapp', 'email']);

        $this->assertDatabaseCount('mahasiswas', 1);
    }

    public function test_update_allows_keeping_own_nim_and_email_but_not_taking_others(): void
    {
        $a = $this->student(['nim' => '111', 'email' => 'a@mhs.politala.ac.id']);
        $this->student(['nim' => '222', 'email' => 'b@mhs.politala.ac.id']);

        $payload = [
            'nim' => '111',
            'nama' => 'Nama Baru',
            'program_studi' => 'TI',
            'no_whatsapp' => '081234567890',
            'email' => 'a@mhs.politala.ac.id',
        ];

        $this->put(route('mahasiswa.update', $a), $payload)->assertSessionHasNoErrors();
        $this->assertSame('Nama Baru', $a->fresh()->nama);

        $this->put(route('mahasiswa.update', $a), array_replace($payload, ['nim' => '222']))
            ->assertSessionHasErrors('nim');
    }

    public function test_destroy_keeps_search_and_sort_state(): void
    {
        $m = $this->student();

        $this->delete(route('mahasiswa.destroy', ['mahasiswa' => $m, 'search' => 'abc', 'sort' => 'nama', 'per_page' => 25]))
            ->assertRedirect(route('mahasiswa.index', ['search' => 'abc', 'sort' => 'nama', 'per_page' => 25]));

        $this->assertDatabaseMissing('mahasiswas', ['id' => $m->id]);
    }

    // ------------------------------------------------------------------ template

    public function test_template_download_has_expected_columns_and_is_importable(): void
    {
        $response = $this->get(route('mahasiswa.template'));

        $response->assertOk()->assertDownload('template_import_mahasiswa.xlsx');

        $path = tempnam(sys_get_temp_dir(), 'tpl');
        file_put_contents($path, $response->streamedContent());
        $sheet = IOFactory::load($path)->getSheet(0);
        unlink($path);

        $this->assertSame('Data Mahasiswa', $sheet->getTitle());
        $this->assertSame(self::HEADERS, array_map(
            fn ($col) => $sheet->getCell("{$col}1")->getValue(),
            ['A', 'B', 'C', 'D', 'E']
        ));
        $this->assertNull($sheet->getCell('A2')->getValue(), 'Template tidak boleh berisi baris contoh yang ikut ter-import.');
    }

    public function test_file_created_from_the_template_can_be_imported_directly(): void
    {
        $spreadsheet = app(MahasiswaTemplateService::class)->build();
        $sheet = $spreadsheet->getSheetByName('Data Mahasiswa');

        foreach (['A2' => '2210101001', 'B2' => 'Budi Santoso', 'C2' => 'Teknik Informatika', 'D2' => '081234567890', 'E2' => 'budi@mhs.politala.ac.id'] as $cell => $value) {
            $sheet->setCellValueExplicit($cell, $value, DataType::TYPE_STRING);
        }

        $path = tempnam(sys_get_temp_dir(), 'tpl');
        (new Xlsx($spreadsheet))->save($path);
        $file = UploadedFile::fake()->createWithContent('template_terisi.xlsx', file_get_contents($path));
        unlink($path);

        $this->import($file)->assertSessionHas('success');
        $this->assertDatabaseHas('mahasiswas', ['nim' => '2210101001', 'nama' => 'Budi Santoso']);
    }

    // ------------------------------------------------------------------ import

    public function test_import_inserts_all_rows_when_everything_is_valid(): void
    {
        $rows = [
            $this->validRow(1),
            $this->validRow(2, ['nim' => 3310100002]),              // NIM tersimpan sebagai angka di Excel
            $this->validRow(3, ['email' => 'IMPORT3@MHS.POLITALA.AC.ID']), // dinormalisasi ke huruf kecil
        ];

        $this->import($this->excel($rows))
            ->assertRedirect(route('mahasiswa.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('mahasiswas', 3);
        $this->assertDatabaseHas('mahasiswas', ['nim' => '3310100002']);
        $this->assertDatabaseHas('mahasiswas', ['email' => 'import3@mhs.politala.ac.id']);
    }

    public function test_import_accepts_columns_in_any_order_and_skips_blank_rows(): void
    {
        $file = $this->excel([
            ['Budi', '3310100001', 'mhs1@mhs.politala.ac.id', 'Akuntansi', '081234567890'],
            ['', '', '', '', ''],
            ['Siti', '3310100002', 'mhs2@mhs.politala.ac.id', 'Akuntansi', '081234567891'],
        ], ['Nama', 'NIM', 'Email', 'Prodi', 'No. WhatsApp']);

        $this->import($file)->assertSessionHas('success');
        $this->assertDatabaseCount('mahasiswas', 2);
    }

    public function test_import_rejects_everything_and_reports_problem_rows_with_reasons(): void
    {
        $rows = [
            $this->validRow(1),                                        // baris 2: valid
            $this->validRow(2, ['email' => 'x@gmail.com']),            // baris 3: domain salah
            ['', '', '', '', ''],                                      // baris 4: kosong (dilewati)
            $this->validRow(3, ['nama' => '']),                        // baris 5: nama kosong
            $this->validRow(4, ['wa' => '12345']),                     // baris 6: WhatsApp salah
        ];

        $this->import($this->excel($rows))
            ->assertRedirect(route('mahasiswa.index'))
            ->assertSessionHas('import_report', function (array $report) {
                $byRow = collect($report['errors'])->keyBy('row');

                return $report['success'] === false
                    && $report['total'] === 4
                    && $report['error_count'] === 3
                    && $byRow->keys()->all() === [3, 5, 6]        // nomor baris = nomor baris di Excel
                    && str_contains(implode(' ', $byRow[3]['messages']), 'mhs.politala.ac.id')
                    && str_contains(implode(' ', $byRow[5]['messages']), 'wajib diisi')
                    && $byRow[3]['nim'] === '3310100002';
            });

        // Baris valid (baris 2) pun tidak boleh masuk: all-or-nothing.
        $this->assertDatabaseCount('mahasiswas', 0);
    }

    public function test_import_detects_duplicate_nim_and_email_in_file_and_database(): void
    {
        $this->student(['nim' => '9990000001', 'email' => 'lama@mhs.politala.ac.id']);

        $rows = [
            $this->validRow(1, ['nim' => '9990000001']),                              // baris 2: NIM sudah ada di DB
            $this->validRow(2, ['email' => 'lama@mhs.politala.ac.id']),               // baris 3: email sudah ada di DB
            $this->validRow(3, ['nim' => '4440000003', 'email' => 'c@mhs.politala.ac.id']),
            $this->validRow(4, ['nim' => '4440000003', 'email' => 'd@mhs.politala.ac.id']), // baris 5: NIM ganda di file
            $this->validRow(5, ['email' => 'c@mhs.politala.ac.id']),                  // baris 6: email ganda di file
        ];

        $this->import($this->excel($rows))
            ->assertSessionHas('import_report', function (array $report) {
                $byRow = collect($report['errors'])->keyBy('row');
                $text = fn (int $row) => implode(' ', $byRow[$row]['messages'] ?? []);

                return $byRow->keys()->all() === [2, 3, 5, 6]
                    && str_contains($text(2), 'NIM sudah terdaftar')
                    && str_contains($text(3), 'Email sudah terdaftar')
                    && str_contains($text(5), 'NIM duplikat')
                    && str_contains($text(6), 'Email duplikat');
            });

        $this->assertDatabaseCount('mahasiswas', 1); // hanya data lama
    }

    public function test_import_rejects_file_with_missing_required_column(): void
    {
        $file = $this->excel([['3310100001', 'Budi', 'Akuntansi', '081234567890']], ['NIM', 'Nama', 'Program Studi', 'No WhatsApp']);

        $this->import($file)
            ->assertRedirect(route('mahasiswa.index'))
            ->assertSessionHas('error', fn ($message) => str_contains($message, 'Email'));

        $this->assertDatabaseCount('mahasiswas', 0);
    }

    public function test_import_rejects_empty_file_and_wrong_file_type(): void
    {
        $this->import($this->excel([]))->assertSessionHas('error');

        $this->post(route('mahasiswa.import'), ['file' => UploadedFile::fake()->create('data.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('file');

        $this->post(route('mahasiswa.import'), [])->assertSessionHasErrors('file');

        // file dengan ekstensi .xlsx tetapi isinya bukan Excel
        $fake = UploadedFile::fake()->createWithContent('palsu.xlsx', 'ini bukan excel');
        $this->import($fake)->assertSessionHas('error');

        $this->assertDatabaseCount('mahasiswas', 0);
    }

    public function test_import_is_atomic_when_the_database_fails_midway(): void
    {
        // 150 baris = 2 kali INSERT (per 100 baris). INSERT kedua sengaja digagalkan.
        $rows = [];
        for ($i = 1; $i <= 150; $i++) {
            $rows[] = $this->validRow($i);
        }

        $inserts = 0;
        DB::listen(function ($query) use (&$inserts) {
            if (str_contains($query->sql, 'insert into "mahasiswas"') && ++$inserts === 2) {
                throw new QueryException($query->connectionName, $query->sql, $query->bindings, new \PDOException('simulasi gagal'));
            }
        });

        $this->import($this->excel($rows))->assertSessionHas('error');

        $this->assertSame(2, $inserts);
        $this->assertDatabaseCount('mahasiswas', 0); // 100 baris pertama ikut di-rollback
    }

    public function test_import_of_150_rows_succeeds_across_multiple_insert_chunks(): void
    {
        $rows = [];
        for ($i = 1; $i <= 150; $i++) {
            $rows[] = $this->validRow($i);
        }

        $this->import($this->excel($rows))->assertSessionHas('success');

        $this->assertDatabaseCount('mahasiswas', 150);
    }
}
