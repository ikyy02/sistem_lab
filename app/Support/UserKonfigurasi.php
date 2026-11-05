<?php

namespace App\Support;

use App\Http\Requests\MahasiswaRequest;

/** Konfigurasi Kelola Data User per kategori + aturan nomor WhatsApp; dipakai controller dan import. */
class UserKonfigurasi
{
    public const WA_REGEX = '/^(?:\+62|62|0)8[1-9][0-9]{7,11}$/';

    /**
     * Konfigurasi tiap kategori.
     * columns: kolom tabel [key => [label, sortable]]; search: kolom yang dicari; fields: input form.
     */
    // Satu menu untuk 4 role. Tambah kolom/field form per kategori di sini (match menurut role).
    public static function config(string $kategori): array
    {
        $wa = ['name' => 'no_whatsapp', 'label' => 'WhatsApp', 'type' => 'text', 'placeholder' => '08xxxxxxxxxx', 'required' => false];
        $email = fn ($ph = 'nama@domain.ac.id') => ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'placeholder' => $ph, 'required' => true];
        $nama = ['name' => 'nama', 'label' => 'Nama', 'type' => 'text', 'placeholder' => 'Nama lengkap', 'required' => true];
        $idPegawai = ['name' => 'id_pegawai', 'label' => 'ID Pegawai/NIP', 'type' => 'text', 'placeholder' => 'ID Pegawai/NIP', 'required' => true];
        $prodi = ['name' => 'id_prodi', 'label' => 'Prodi', 'type' => 'select', 'options' => Options::prodi(), 'placeholder' => 'Pilih Prodi', 'required' => true];

        // Tiap role punya kolom tabel, kolom pencarian, urutan default, dan field form sendiri.
        return match ($kategori) {
            Role::MAHASISWA => [
                'columns' => ['nim' => ['NIM', true], 'nama' => ['Nama', true], 'nama_prodi' => ['Prodi', true], 'email' => ['Email', true], 'no_whatsapp' => ['WhatsApp', false]],
                'search' => ['mahasiswas.nim', 'mahasiswas.nama', 'prodis.nama_prodi', 'mahasiswas.email', 'mahasiswas.no_whatsapp'],
                'default_sort' => 'nim',
                'fields' => [
                    ['name' => 'nim', 'label' => 'NIM', 'type' => 'text', 'placeholder' => 'Contoh: 2210101001', 'required' => true],
                    $nama,
                    $prodi,
                    $email('nim@' . MahasiswaRequest::EMAIL_DOMAIN),
                    $wa,
                ],
            ],
            Role::DOSEN => [
                'columns' => ['nuptk_nidn' => ['NUPTK/NIDN', true], 'nama' => ['Nama', true], 'nama_prodi' => ['Prodi', true], 'email' => ['Email', true], 'no_whatsapp' => ['WhatsApp', false], 'status' => ['Status', false]],
                'search' => ['dosens.nuptk_nidn', 'dosens.nama', 'prodis.nama_prodi', 'dosens.email', 'dosens.no_whatsapp'],
                'default_sort' => 'nama',
                'fields' => [
                    ['name' => 'nuptk_nidn', 'label' => 'NUPTK/NIDN', 'type' => 'text', 'placeholder' => 'NUPTK atau NIDN', 'required' => true],
                    $nama, $prodi, $email(), $wa,
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'], 'placeholder' => 'Pilih Status', 'required' => true],
                ],
            ],
            Role::STAFF => [
                'columns' => ['id_pegawai' => ['ID Pegawai/NIP', true], 'nama' => ['Nama', true], 'nama_prodi' => ['Prodi', true], 'email' => ['Email', true], 'no_whatsapp' => ['WhatsApp', false]],
                'search' => ['staff_prodis.id_pegawai', 'staff_prodis.nama', 'prodis.nama_prodi', 'staff_prodis.email', 'staff_prodis.no_whatsapp'],
                'default_sort' => 'nama',
                'fields' => [$idPegawai, $nama, $prodi, $email(), $wa],
            ],
            default => [
                'columns' => ['id_pegawai' => ['ID Pegawai/NIP', true], 'nama' => ['Nama', true], 'email' => ['Email', true], 'no_whatsapp' => ['WhatsApp', false]],
                'search' => ['laborans.id_pegawai', 'laborans.nama', 'laborans.email', 'laborans.no_whatsapp'],
                'default_sort' => 'nama',
                'fields' => [$idPegawai, $nama, $email(), $wa],
            ],
        };
    }
}
