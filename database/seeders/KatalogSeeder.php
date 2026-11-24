<?php

namespace Database\Seeders;

use App\Models\AlatBahan;
use App\Models\Ruangan;
use App\Models\Satuan;
use Illuminate\Database\Seeder;

class KatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (Ruangan::count() === 0) {
            $this->call(RuanganSeeder::class);
        }

        $unit = Satuan::firstOrCreate(['nama_satuan' => 'Unit']);
        $buah = Satuan::firstOrCreate(['nama_satuan' => 'Buah']);
        $pcs = Satuan::firstOrCreate(['nama_satuan' => 'Pcs']);
        $meter = Satuan::firstOrCreate(['nama_satuan' => 'Meter']);
        $botol = Satuan::firstOrCreate(['nama_satuan' => 'Botol']);

        $labLinus = Ruangan::where('nama_ruangan', 'like', '%Linus%')->first()
            ?? Ruangan::first();
        $labSteve = Ruangan::where('nama_ruangan', 'like', '%Steve Jobs%')->first()
            ?? Ruangan::first();
        $labGates = Ruangan::where('nama_ruangan', 'like', '%Bill Gates%')->first()
            ?? Ruangan::first();

        $items = [
            // ALAT
            [
                'nama' => 'Switch Cisco Catalyst 24 Port',
                'jenis' => 'alat',
                'id_satuan' => $unit->id_satuan,
                'stok' => 5,
                'harga' => 3200000,
                'id_ruangan' => $labLinus->id_ruangan,
                'gambar' => 'switch-cisco-catalyst.jpg',
                'keterangan' => 'Switch manageable untuk praktikum routing & switching.',
            ],
            [
                'nama' => 'Router Mikrotik RB750Gr3',
                'jenis' => 'alat',
                'id_satuan' => $unit->id_satuan,
                'stok' => 8,
                'harga' => 850000,
                'id_ruangan' => $labLinus->id_ruangan,
                'gambar' => 'router-mikrotik-rb750.jpg',
                'keterangan' => 'Router board 5-port Gigabit Ethernet untuk praktikum jaringan komputer.',
            ],
            [
                'nama' => 'Crimping Tool RJ45 Pro',
                'jenis' => 'alat',
                'id_satuan' => $buah->id_satuan,
                'stok' => 12,
                'harga' => 125000,
                'id_ruangan' => $labLinus->id_ruangan,
                'gambar' => 'crimping-tool-rj45.jpg',
                'keterangan' => 'Tang crimping kabel UTP Cat5e dan Cat6.',
            ],
            [
                'nama' => 'LAN Cable Tester Digital',
                'jenis' => 'alat',
                'id_satuan' => $unit->id_satuan,
                'stok' => 8,
                'harga' => 95000,
                'id_ruangan' => $labLinus->id_ruangan,
                'gambar' => 'lan-cable-tester.jpg',
                'keterangan' => 'Tester pin kabel jaringan RJ45 dan RJ11.',
            ],
            [
                'nama' => 'Multimeter Digital Sanwa',
                'jenis' => 'alat',
                'id_satuan' => $unit->id_satuan,
                'stok' => 6,
                'harga' => 350000,
                'id_ruangan' => $labLinus->id_ruangan,
                'gambar' => 'multimeter-digital.jpg',
                'keterangan' => 'Pengukur tegangan, arus, dan hambatan digital.',
            ],
            [
                'nama' => 'Proyektor Epson EB-X500',
                'jenis' => 'alat',
                'id_satuan' => $unit->id_satuan,
                'stok' => 3,
                'harga' => 5400000,
                'id_ruangan' => $labSteve->id_ruangan,
                'gambar' => 'proyektor-epson.jpg',
                'keterangan' => 'Proyektor HDMI/VGA untuk presentasi praktikum multimedia.',
            ],
            [
                'nama' => 'Drawing Tablet Huion Inspiroy',
                'jenis' => 'alat',
                'id_satuan' => $unit->id_satuan,
                'stok' => 10,
                'harga' => 650000,
                'id_ruangan' => $labSteve->id_ruangan,
                'gambar' => 'drawing-tablet.jpg',
                'keterangan' => 'Pen tablet grafis untuk desain UI/UX dan multimedia interaktif.',
            ],
            [
                'nama' => 'Barcode Scanner USB',
                'jenis' => 'alat',
                'id_satuan' => $unit->id_satuan,
                'stok' => 5,
                'harga' => 280000,
                'id_ruangan' => $labGates->id_ruangan,
                'gambar' => 'barcode-scanner-usb.jpg',
                'keterangan' => 'Pemindai barcode 1D/2D untuk praktikum sistem informasi & kasir.',
            ],

            // BAHAN
            [
                'nama' => 'Kabel UTP Cat6 Belden',
                'jenis' => 'bahan',
                'id_satuan' => $meter->id_satuan,
                'stok' => 300,
                'harga' => 8500,
                'id_ruangan' => $labLinus->id_ruangan,
                'gambar' => 'kabel-utp-cat6.jpg',
                'keterangan' => 'Kabel jaringan Cat6 unshielded twisted pair.',
            ],
            [
                'nama' => 'Konektor RJ45 Cat6',
                'jenis' => 'bahan',
                'id_satuan' => $pcs->id_satuan,
                'stok' => 200,
                'harga' => 2500,
                'id_ruangan' => $labLinus->id_ruangan,
                'gambar' => 'konektor-rj45.jpg',
                'keterangan' => 'Modular plug konektor RJ45 Cat6 berlapis emas.',
            ],
            [
                'nama' => 'Larutan Pembersih Kontak (Contact Cleaner)',
                'jenis' => 'bahan',
                'id_satuan' => $botol->id_satuan,
                'stok' => 15,
                'harga' => 45000,
                'id_ruangan' => $labLinus->id_ruangan,
                'gambar' => 'contact-cleaner.jpg',
                'keterangan' => 'Cairan semprot pembersih komponen elektronik dan soket RAM/Motherboard.',
            ],
            [
                'nama' => 'Thermal Paste Pasta Pendingin CPU',
                'jenis' => 'bahan',
                'id_satuan' => $pcs->id_satuan,
                'stok' => 20,
                'harga' => 35000,
                'id_ruangan' => $labLinus->id_ruangan,
                'gambar' => 'thermal-paste.jpg',
                'keterangan' => 'Pasta pendingin konduktivitas tinggi untuk heatsink prosesor.',
            ],
        ];

        foreach ($items as $item) {
            AlatBahan::updateOrCreate(
                ['nama' => $item['nama']],
                $item
            );
        }
    }
}
