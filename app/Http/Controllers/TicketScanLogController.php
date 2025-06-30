<?php

namespace App\Http\Controllers;

use App\Models\TicketScanLog;
use Illuminate\Http\Request;

class TicketScanLogController extends Controller
{
    /**
     * Menampilkan halaman riwayat scan tiket QR Code.
     */
     public function index()
    {
        // Ambil data log dengan relasi terkait, urutkan berdasarkan waktu scan terbaru
        $logs = TicketScanLog::with('nonMemberTicket.ticketProduct', 'nonMemberTicket.transaction')
                      ->latest('scanned_at')
                      ->paginate(25); // Tampilkan 10 log per halaman

        return view('ticket_scan_logs.index', compact('logs'));
    }
}