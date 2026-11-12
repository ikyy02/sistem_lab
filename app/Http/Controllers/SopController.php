<?php

namespace App\Http\Controllers;

use App\Models\Sop;

/** SOP & Panduan: halaman statis berisi tata tertib dan prosedur laboratorium (data dari tabel sops). */
class SopController extends Controller
{
    public function index()
    {
        $sops = Sop::query()->orderBy('urutan')->orderBy('kode')->get();

        return view('sop', ['sops' => $sops]);
    }
}
