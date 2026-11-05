<?php

namespace App\Support;

use Carbon\CarbonInterface;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

final class PesanPengingatWa
{
    /**
     * @param  array<int, array{nama: string, jumlah: int}>  $barang
     */
    public static function buat(
        string $nama,
        array $barang,
        CarbonInterface $batas,
        int $keKe,
        CarbonInterface $sekarang,
        string $timezone,
    ): string {
        return implode("\n", [
            '*Pengingat Pengembalian – SIARKA*',
            '',
            'Halo, '.$nama.'!',
            '',
            self::pembuka($keKe),
            '',
            'Barang: '.self::ringkasanBarang($barang),
            'Batas kembali: '.self::formatBatas($batas, $timezone),
            'Terlambat: '.self::formatTerlambat($batas, $sekarang),
        ]);
    }

    public static function pembuka(int $keKe): string
    {
        return match ($keKe) {
            1 => 'Ini pengingat pertama untuk peminjaman yang telah melewati batas waktu. Mohon segera kembalikan barang ke laboratorium.',
            2 => 'Ini pengingat kedua untuk peminjaman yang masih belum dikembalikan. Mohon segera kembalikan barang ke laboratorium.',
            default => 'Ini pengingat terakhir untuk peminjaman yang masih belum dikembalikan. Mohon segera kembalikan barang atau hubungi laboran.',
        };
    }

    /**
     * @param  array<int, array{nama: string, jumlah: int}>  $barang
     */
    public static function ringkasanBarang(array $barang): string
    {
        if ($barang === []) {
            return '-';
        }

        $tampil = array_map(
            fn (array $item) => $item['nama'].' × '.$item['jumlah'],
            array_slice($barang, 0, 3),
        );

        $sisa = count($barang) - 3;
        if ($sisa > 0) {
            $tampil[] = '+'.$sisa.' barang lainnya';
        }

        return implode(', ', $tampil);
    }

    public static function formatBatas(CarbonInterface $batas, string $timezone): string
    {
        $waktu = $batas->copy()->setTimezone($timezone)->locale('id');
        $teks = $waktu->translatedFormat('l, j F Y').' pukul '.$waktu->format('H.i');
        $zona = self::labelZona($timezone, $waktu);

        return $zona === '' ? $teks : $teks.' '.$zona;
    }

    public static function formatTerlambat(CarbonInterface $batas, CarbonInterface $sekarang): string
    {
        if (abs($sekarang->getTimestamp() - $batas->getTimestamp()) < 60) {
            return 'baru saja';
        }

        return $batas->copy()->locale('id')->diffForHumans($sekarang, [
            'syntax' => CarbonInterface::DIFF_ABSOLUTE,
            'parts' => 2,
        ]);
    }

    public static function labelZona(string $timezone, ?CarbonInterface $waktu = null): string
    {
        return match ($timezone) {
            'Asia/Jakarta', 'Asia/Pontianak' => 'WIB',
            'Asia/Makassar', 'Asia/Ujung_Pandang' => 'WITA',
            'Asia/Jayapura' => 'WIT',
            default => self::labelDariOffset($timezone, $waktu),
        };
    }

    private static function labelDariOffset(string $timezone, ?CarbonInterface $waktu): string
    {
        try {
            $offset = (new DateTimeZone($timezone))->getOffset(
                $waktu?->toDateTimeImmutable() ?? new DateTimeImmutable,
            );
        } catch (Throwable) {
            return '';
        }

        return match (intdiv($offset, 3600)) {
            7 => 'WIB',
            8 => 'WITA',
            9 => 'WIT',
            default => '',
        };
    }
}
