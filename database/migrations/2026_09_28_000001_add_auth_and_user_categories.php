<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tahap 1: autentikasi + pemisahan kategori pengguna.
 * - mahasiswas: tambah kelas & password
 * - dosens: tambah password
 * - staff_prodis, laborans: tabel baru
 * Password disimpan apa adanya (belum di-hash) sesuai ketentuan tahap ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->string('kelas', 50)->nullable()->after('nama');
            $table->string('password')->nullable();
        });

        Schema::table('dosens', function (Blueprint $table) {
            $table->string('password')->nullable();
        });

        Schema::create('staff_prodis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('program_studi');
            $table->string('email')->unique();
            $table->string('no_whatsapp', 20);
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('laborans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('no_whatsapp', 20);
            $table->string('password');
            $table->timestamps();
        });

        // Akun lama yang belum punya password: default = NIM / NIDN agar tetap bisa login.
        DB::table('mahasiswas')->whereNull('password')->update(['password' => DB::raw('nim')]);
        DB::table('dosens')->whereNull('password')->update(['password' => DB::raw('nidn')]);

        // Akun awal Laboran/Admin supaya sistem dapat dimasuki (ganti passwordnya setelah login).
        if (! DB::table('laborans')->exists()) {
            DB::table('laborans')->insert([
                'nama' => 'Administrator',
                'email' => 'admin@silab.test',
                'no_whatsapp' => '081200000000',
                'password' => 'admin123',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('laborans');
        Schema::dropIfExists('staff_prodis');
        Schema::table('dosens', fn (Blueprint $t) => $t->dropColumn('password'));
        Schema::table('mahasiswas', fn (Blueprint $t) => $t->dropColumn(['kelas', 'password']));
    }
};
