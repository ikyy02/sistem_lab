<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman_ruangans', function (Blueprint $table) {
            $table->dateTime('tanggal_selesai_aktual')->nullable()->after('tanggal_selesai');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman_ruangans', function (Blueprint $table) {
            $table->dropColumn('tanggal_selesai_aktual');
        });
    }
};
