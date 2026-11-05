<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class NotifikasiBellTest extends TestCase
{
    use MembuatData, RefreshDatabase;

    private function sebagai(string $role, $profil): self
    {
        return $this->withSession(['silab_user' => ['role' => $role, 'key' => (string) $profil->getKey(), 'nama' => $profil->nama, 'email' => $profil->email]]);
    }

    /** Sisipkan notifikasi database langsung agar created_at dapat dipastikan (pengurutan 10 terbaru). */
    private function beriNotifikasi(string $email, string $judul, ?Carbon $waktu = null, bool $dibaca = false): string
    {
        $waktu ??= now();
        $id = (string) Str::uuid();
        DB::table('notifications')->insert([
            'id' => $id,
            'type' => 'uji',
            'notifiable_type' => 'akun',
            'notifiable_id' => $email,
            'data' => json_encode(['judul' => $judul, 'pesan' => 'Pesan untuk ' . $judul]),
            'read_at' => $dibaca ? $waktu : null,
            'created_at' => $waktu,
            'updated_at' => $waktu,
        ]);

        return $id;
    }

    private function readAt(string $id): ?string
    {
        return DB::table('notifications')->where('id', $id)->value('read_at');
    }

    public function test_lonceng_menampilkan_badge_unread_dan_10_notifikasi_terbaru(): void
    {
        $m = $this->mahasiswa();
        $base = now()->startOfMinute();

        $this->beriNotifikasi($m->email, 'Notif lama', $base->copy()->subDay(), true);
        for ($i = 1; $i <= 12; $i++) {
            $this->beriNotifikasi($m->email, sprintf('Notif %02d', $i), $base->copy()->addSeconds($i));
        }

        $this->sebagai('mahasiswa', $m)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('<span class="notif-badge">12</span>', false)
            ->assertSee('Tandai semua dibaca')
            ->assertSee('Lihat semua notifikasi')
            ->assertSee('Notif 12')
            ->assertSee('Notif 03')
            ->assertDontSee('Notif 02')
            ->assertDontSee('Notif 01')
            ->assertDontSee('Notif lama');
    }

    public function test_halaman_notifikasi_hanya_menampilkan_milik_akun_sendiri(): void
    {
        $a = $this->mahasiswa();
        $b = $this->mahasiswa();
        $this->beriNotifikasi($a->email, 'Notif untuk A');
        $this->beriNotifikasi($b->email, 'Notif untuk B');

        $this->sebagai('mahasiswa', $a)->get(route('notifikasi.index'))
            ->assertOk()
            ->assertSee('Notif untuk A')
            ->assertDontSee('Notif untuk B')
            ->assertSee('1 notifikasi belum dibaca');
    }

    public function test_tandai_dibaca_hanya_untuk_notifikasi_milik_sendiri(): void
    {
        $a = $this->mahasiswa();
        $b = $this->mahasiswa();
        $idA = $this->beriNotifikasi($a->email, 'Notif A');
        $idB = $this->beriNotifikasi($b->email, 'Notif B');

        $this->sebagai('mahasiswa', $a)->post(route('notifikasi.baca', $idA))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertNotNull($this->readAt($idA));

        $this->sebagai('mahasiswa', $a)->post(route('notifikasi.baca', $idB))->assertNotFound();
        $this->assertNull($this->readAt($idB));
    }

    public function test_tandai_semua_dibaca_tidak_menyentuh_notifikasi_akun_lain(): void
    {
        $a = $this->mahasiswa();
        $b = $this->mahasiswa();
        $idA1 = $this->beriNotifikasi($a->email, 'A satu');
        $idA2 = $this->beriNotifikasi($a->email, 'A dua');
        $idB = $this->beriNotifikasi($b->email, 'B satu');

        $this->sebagai('mahasiswa', $a)->post(route('notifikasi.baca-semua'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotNull($this->readAt($idA1));
        $this->assertNotNull($this->readAt($idA2));
        $this->assertNull($this->readAt($idB));

        // Tanpa sisa unread, badge lonceng tidak dirender lagi.
        $this->sebagai('mahasiswa', $a)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('<span class="notif-badge">', false);
    }

    public function test_tamu_dialihkan_ke_halaman_login(): void
    {
        $id = (string) Str::uuid();

        $this->get(route('notifikasi.index'))->assertRedirect(route('login'));
        $this->post(route('notifikasi.baca', $id))->assertRedirect(route('login'));
        $this->post(route('notifikasi.baca-semua'))->assertRedirect(route('login'));
    }
}
