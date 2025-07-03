<?php

namespace App\Http\Controllers;

use App\Models\TicketScanLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TicketScanLogController extends Controller
{
    /**
     * Menampilkan halaman utama riwayat scan tiket (dengan data awal).
     */
    public function index()
    {
        // Ambil semua log, urutkan dari yang paling baru, untuk tampilan awal.
        $logs = TicketScanLog::with(['nonMemberTicket.ticketProduct', 'nonMemberTicket.transaction'])
                      ->latest('scanned_at')
                      ->paginate(25); 

        return view('ticket_scan_logs.index', compact('logs'));
    }

    /**
     * METHOD BARU: Mengambil log scan terbaru untuk pembaruan real-time (AJAX).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchLatest(Request $request)
    {
        $request->validate(['since_id' => 'nullable|integer']);

        $sinceId = $request->input('since_id', 0);

        // Ambil semua log yang ID-nya lebih besar dari ID terakhir yang dilihat klien
        $newLogs = TicketScanLog::with(['nonMemberTicket.ticketProduct', 'nonMemberTicket.transaction'])
                         ->where('id', '>', $sinceId)
                         ->latest('scanned_at')
                         ->get();

        // Ubah data menjadi format yang mudah digunakan oleh JavaScript
        $formattedLogs = $newLogs->map(function ($log) {
            $ticket = $log->nonMemberTicket;
            $ticketName = '-';
            $customerName = 'Tamu';

            if ($ticket) {
                $ticketName = $ticket->ticketProduct->name ?? 'Tiket Dihapus';
                $customerName = $ticket->transaction->customer_name ?? 'Tamu';
            } else {
                $ticketName = 'Tiket Telah Dihapus';
                $customerName = 'Data tidak tersedia';
            }

            return [
                'id' => $log->id,
                'scanned_at' => Carbon::parse($log->scanned_at)->format('d M Y, H:i:s'),
                'scanned_token' => $log->scanned_token,
                'ticket_name' => $ticketName,
                'customer_name' => $customerName,
                'status' => $log->status,
                'message' => $log->message,
            ];
        });

        return response()->json($formattedLogs);
    }
}
