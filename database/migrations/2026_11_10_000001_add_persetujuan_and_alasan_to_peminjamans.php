<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pelengkap proses persetujuan peminjaman:
 * - tanggal_persetujuan : kapan pengajuan disetujui/ditolak (dicatat sistem saat diproses).
 * - alasan_ditolak      : alasan penolakan yang diisi Dosen/Laboran (wajib saat menolak).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('peminjamans')) {
            return; // struktur final belum dibuat (mis. migrasi dijalankan terpisah)
        }
        Schema::table('peminjamans', function (Blueprint $t) {
            if (! Schema::hasColumn('peminjamans', 'alasan_ditolak')) {
                $t->text('alasan_ditolak')->nullable()->after('keterangan');
            }
            if (! Schema::hasColumn('peminjamans', 'tanggal_persetujuan')) {
                $t->dateTime('tanggal_persetujuan')->nullable()->after('email_pemroses');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('peminjamans')) {
            return;
        }
        Schema::table('peminjamans', function (Blueprint $t) {
            if (Schema::hasColumn('peminjamans', 'alasan_ditolak')) {
                $t->dropColumn('alasan_ditolak');
            }
            if (Schema::hasColumn('peminjamans', 'tanggal_persetujuan')) {
                $t->dropColumn('tanggal_persetujuan');
            }
        });
    }
};
