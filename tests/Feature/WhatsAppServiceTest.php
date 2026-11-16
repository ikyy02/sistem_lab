<?php

namespace Tests\Feature;

use App\Services\WhatsAppService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppServiceTest extends TestCase
{
    private WhatsAppService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set([
            'notifikasi.wa.enabled' => true,
            'notifikasi.wa.gateway_url' => 'http://127.0.0.1:3001',
            'notifikasi.wa.gateway_token' => 'test-token',
        ]);

        $this->service = app(WhatsAppService::class);
    }

    public function test_kirim_sukses(): void
    {
        Http::fake([
            'http://127.0.0.1:3001/send' => Http::response(['success' => true], 200),
        ]);

        $hasil = $this->service->kirim('08123456789', 'Test pesan');

        $this->assertTrue($hasil);
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://127.0.0.1:3001/send'
                && $request['to'] === '628123456789'
                && $request['message'] === 'Test pesan';
        });
        Http::assertSentCount(1);
    }

    public function test_gateway_mati_tidak_melempar_exception(): void
    {
        Http::fake(fn () => throw new ConnectionException('Connection refused'));

        $hasil = $this->service->kirim('08123456789', 'Test pesan');

        $this->assertFalse($hasil);
    }

    public function test_gateway_merespons_gagal_mengembalikan_false(): void
    {
        Http::fake([
            'http://127.0.0.1:3001/send' => Http::response(['success' => false], 503),
        ]);

        $this->assertFalse($this->service->kirim('08123456789', 'Test pesan'));
    }

    public function test_nomor_kosong_dilewati_tanpa_request(): void
    {
        Http::fake();

        $hasil = $this->service->kirim('', 'Test pesan');

        $this->assertFalse($hasil);
        Http::assertNothingSent();
    }

    public function test_nomor_invalid_dilewati_tanpa_request(): void
    {
        Http::fake();

        $this->assertFalse($this->service->kirim('abc', 'Test pesan'));
        Http::assertNothingSent();
    }

    public function test_normalisasi_nomor(): void
    {
        $this->assertSame('628123456789', $this->service->normalisasiNomor('08123456789'));
        $this->assertSame('628123456789', $this->service->normalisasiNomor('+628123456789'));
        $this->assertSame('628123456789', $this->service->normalisasiNomor('628123456789'));
        $this->assertSame('628123456789', $this->service->normalisasiNomor('08-1234-56789'));
        $this->assertNull($this->service->normalisasiNomor('123'));
    }

    public function test_anti_spam_cooldown_mencegah_pengiriman_berulang_ke_nomor_yang_sama(): void
    {
        Http::fake([
            'http://127.0.0.1:3001/send' => Http::response(['success' => true], 200),
        ]);

        $this->service->bersihkanCooldown('628129999999');

        // Pengiriman pertama sukses
        $pertama = $this->service->kirim('08129999999', 'Pesan pertama');
        $this->assertTrue($pertama);
        Http::assertSentCount(1);

        // Pengiriman kedua ke nomor yang sama ditahan oleh cooldown anti-spam
        $kedua = $this->service->kirim('08129999999', 'Pesan kedua');
        $this->assertFalse($kedua);
        Http::assertSentCount(1); // Tidak ada request baru ke gateway

        // Pengiriman jika diizinkan mengabaikan cooldown
        $ketiga = $this->service->kirim('08129999999', 'Pesan darurat', abaikanCooldown: true);
        $this->assertTrue($ketiga);
        Http::assertSentCount(2);

        $this->service->bersihkanCooldown('628129999999');
    }

    public function test_anti_spam_nomor_blacklist_dilewati_tanpa_request(): void
    {
        Http::fake();
        config()->set('notifikasi.wa.blacklist', ['6281233334444']);

        $hasil = $this->service->kirim('081233334444', 'Test pesan');

        $this->assertFalse($hasil);
        Http::assertNothingSent();
    }

    public function test_anti_spam_nomor_pola_dummy_berulang_ditolak(): void
    {
        Http::fake();

        // Pola angka berulang (dummy: 08111111111)
        $hasil = $this->service->kirim('08111111111', 'Test pesan');

        $this->assertFalse($hasil);
        Http::assertNothingSent();
    }
}
