<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('name');
            $table->string('role')->default('mahasiswa')->after('password');
            $table->string('nim', 20)->nullable()->after('role');
            $table->string('nidn', 20)->nullable()->after('nim');
            $table->string('nip', 20)->nullable()->after('nidn');
            $table->string('no_hp', 20)->nullable()->after('nip');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('no_hp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'role', 'nim', 'nidn', 'nip', 'no_hp', 'status']);
        });
    }
};
