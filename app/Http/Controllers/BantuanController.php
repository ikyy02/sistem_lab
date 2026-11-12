<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\PengaturanOperasional;

/** Bantuan: FAQ aplikasi + kontak layanan laboratorium. */
class BantuanController extends Controller
{
    public function index()
    {
        $faqs = Faq::query()
            ->where('aktif', true)
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->groupBy('kategori');

        return view('bantuan', [
            'faqs' => $faqs,
            'setting' => PengaturanOperasional::ambil(),
        ]);
    }
}
