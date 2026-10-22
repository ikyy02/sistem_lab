<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Status baru "dibatalkan" untuk peminjaman ber-ruangan yang dibatalkan peminjam sampai H-1. */
return new class extends Migration
{
    private const SEMUA = ['menunggu', 'disetujui', 'ditolak', 'selesai', 'dibatalkan'];
    private const LAMA = ['menunggu', 'disetujui', 'ditolak', 'selesai'];

    public function up(): void
    {
        $this->ubah(self::SEMUA);
    }

    public function down(): void
    {
        DB::table('peminjamans')->where('status', 'dibatalkan')->update(['status' => 'ditolak']);
        $this->ubah(self::LAMA);
    }

    // MySQL memakai ALTER ... MODIFY ENUM; database lain memakai ->change().
    private function ubah(array $nilai): void
    {
        if (! Schema::hasTable('peminjamans')) {
            return;
        }
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $daftar = "'" . implode("','", $nilai) . "'";
            DB::statement("ALTER TABLE peminjamans MODIFY status ENUM({$daftar}) NOT NULL");

            return;
        }
        Schema::table('peminjamans', function (Blueprint $t) use ($nilai) {
            $t->enum('status', $nilai)->change();
        });
    }
};
