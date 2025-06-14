<?php

namespace App\Http\Controllers;

use App\Models\TapLog;
use Illuminate\Http\Request;

class TaplogsController extends Controller
{
    /**
     * Menampilkan halaman riwayat tap kartu.
     */
    public function index()
    {
        // Ambil data log, sertakan relasi ke master card dan pemiliknya (member, coach, staff)
        // Urutkan dari yang paling baru, dan gunakan paginasi
        $logs = TapLog::with('masterCard.member', 'masterCard.coach', 'masterCard.staff')
                    ->latest('tapped_at')
                    ->paginate(25); // Tampilkan 25 log per halaman

        return view('taplogs.index', compact('logs'));
    }
}