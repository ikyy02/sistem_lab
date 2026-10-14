<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase; // landing page membaca pengaturan operasional dari database

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        // Pengguna belum login melihat Landing Page SIARKA beserta formulir login.
        $response->assertOk()->assertSee('SIARKA')->assertSee('Masuk');
    }
}
