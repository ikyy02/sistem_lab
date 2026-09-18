<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard sesuai role user yang sedang login.
     */
    public function index()
    {
        $user = Auth::user();

        $stats = [];

        if ($user->isLaboran()) {
            $stats = [
                'total_user'      => User::count(),
                'total_mahasiswa' => User::where('role', 'mahasiswa')->count(),
                'total_dosen'     => User::where('role', 'dosen')->count(),
                'total_staf'      => User::where('role', 'staf_prodi')->count(),
                'user_aktif'      => User::where('status', 'aktif')->count(),
                'user_nonaktif'   => User::where('status', 'nonaktif')->count(),
            ];
        }

        return view('dashboard', compact('user', 'stats'));
    }
}
