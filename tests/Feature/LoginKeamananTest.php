<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class LoginKeamananTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    public function test_login_dengan_password_benar_berhasil(): void
    {
        $m = $this->mahasiswa();

        $this->post('login', ['email' => $m->email, 'password' => 'rahasia'])->assertRedirect(route('dashboard'));
    }

    public function test_password_salah_ditolak_lalu_percobaan_dibatasi(): void
    {
        $m = $this->mahasiswa();

        for ($i = 0; $i < 5; $i++) {
            $this->post('login', ['email' => $m->email, 'password' => 'salah'])->assertSessionHasErrors('email');
        }
        // Setelah 5 kali gagal, password yang benar pun ditolak sementara.
        $this->post('login', ['email' => $m->email, 'password' => 'rahasia'])->assertSessionHasErrors('email');
    }
}
