<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NonMemberTicket;
use App\Models\TicketScanLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TicketValidationController extends Controller
{
    public function validateTicket(Request $request)
    {
        // Validasi input: qrcode harus ada dan bertipe string
        $request->validate(['qrcode' => 'required|string']);

        $token = $request->qrcode;
        $now = Carbon::now(); // Waktu saat ini
        $today = $now->toDateString(); // Format: YYYY-MM-DD (tanggal hari ini)
        

        // Mencari tiket berdasarkan QR code di tabel non_member_tickets
        $ticket = NonMemberTicket::where('qrcode', $token)->first();

        // 1. Cek: Tiket tidak ditemukan di database
        if (!$ticket) {
            // Catat log scan dengan status 0 (gagal: tiket tidak ditemukan)
            TicketScanLog::create([
                'scanned_token' => $token,
                'status' => 0, // 0 untuk hasil scan gagal
                'message' => 'tiket tdk ktemu'
            ]);
            // Kembalikan respons JSON dengan status 0 dan kode HTTP 404 (Not Found)
            return response()->json([
                'Status' => 0,
                'Message' => 'Tiket tdk ketemu',
                 'Qrcode' => $token,
                 'UTC' => $now->format('d-m-Y H:i:s')

            ], 404);
        }

        // 2. Cek: Tiket sudah digunakan sebelumnya (sesuai definisi baru Anda: status 0)
        // Jika status tiket adalah 0, berarti sudah digunakan
        if ($ticket->status === 0) { // KONDISI BERUBAH: Cek jika status tiket adalah 0
            // Catat log scan dengan status 0 (gagal: tiket sudah digunakan)
            TicketScanLog::create([
                'non_member_ticket_id' => $ticket->id,
                'scanned_token' => $token,
                'status' => 0, // 0 untuk hasil scan gagal
                'message' => 'Tiket sdh digunakan , Akses Ditolak ',
                 'Qrcode' => $token,
            ]);
            // Kembalikan respons JSON dengan status 0 dan kode HTTP 409 (Conflict)
            return response()->json([
                'Status' => 0,
                'Message' => 'Tiket sdh dgnkan',
                 'Qrcode' => $token,
                 'UTC' => $now->format('d-m-Y H:i:s')
                
            ], 409);
        }

        // 3. Cek: Apakah tiket berlaku untuk hari ini?
        // Membandingkan tanggal dibuatnya tiket (created_at) dengan tanggal hari ini
        // (Logika ini tetap sama karena tidak bergantung pada nilai status tiket)
        $ticketDate = Carbon::parse($ticket->created_at)->toDateString();
        if ($ticketDate !== $today) {
            // Catat log scan dengan status 0 (gagal: tanggal tidak berlaku)
            TicketScanLog::create([
                'non_member_ticket_id' => $ticket->id,
                'scanned_token' => $token,
                'status' => 0, // 0 untuk hasil scan gagal
                'message' => 'tiket tdk berlaku',
            ]);
            // Kembalikan respons JSON dengan status 0 dan kode HTTP 410 (Gone)
            return response()->json([
                'Status' => 0,
                'message' => 'Tiket tidak berlaku hari ini , Akses Ditolak ',
                'Qrcode' => $token,
                  'UTC' => $now->format('d-m-Y H:i:s')
            ], 410);
        }

        // Jika semua validasi di atas terlewati, tiket valid dan bisa digunakan
        // SET STATUS BARU: Dari 1 (belum digunakan) menjadi 0 (sudah digunakan)
        $ticket->status = 0; // KONDISI BERUBAH: Set status tiket menjadi 0 (sudah digunakan)
        $ticket->validated_at = $now; // Catat waktu validasi
        $ticket->save(); // Simpan perubahan ke database

        // Catat log scan dengan status 1 (sukses)
        TicketScanLog::create([
            'non_member_ticket_id' => $ticket->id,
            'scanned_token' => $token,
            'status' => 1, // 1 untuk hasil scan sukses
            'message' => 'Akses Diterima'
        ]);

        // Kembalikan respons JSON sukses
        return response()->json([
            'Status' => 1,
            'Message' => 'Akses Diterima',
            'Qrcode' => $token,
            'UTC' => $now->format('d-m-Y H:i:s')
        ]);
    }
}
