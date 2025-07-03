<?php

namespace App\Http\Controllers;

use App\Models\TapLog;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\TapLogsExport; // <-- TAMBAHKAN INI
use Maatwebsite\Excel\Facades\Excel; // <-- TAMBAHKAN INI

class TaplogsController extends Controller
{
    /**
     * Menampilkan halaman riwayat tap kartu dengan filter.
     */
    public function index(Request $request)
    {
        $schoolClasses = SchoolClass::orderBy('name')->get();
        $query = $this->buildQuery($request);
        $logs = $query->latest('tapped_at')->paginate(25)->withQueryString(); 
        return view('taplogs.index', compact('logs', 'schoolClasses'));
    }
    public function exportExcel(Request $request)
    {
        $fileName = 'Laporan_Log_Tap_' . now()->format('Y-m-d_H-i') . '.xlsx';

        // Panggil class Export yang sudah kita buat, sambil mengirimkan seluruh request
        // agar filter bisa digunakan di dalam class Export.
        return Excel::download(new TapLogsExport($request), $fileName);
    }

    /**
     * Mengambil log terbaru untuk pembaruan real-time (AJAX) dengan filter.
     */
       public function fetchLatest(Request $request)
    {
        $request->validate(['since_id' => 'nullable|integer']);
        $sinceId = $request->input('since_id', 0);
        
        $query = $this->buildQuery($request);

        $newLogs = $query->where('tap_logs.id', '>', $sinceId)->get();

        $formattedLogs = $newLogs->map(function ($log) {
            
            $ownerName = $log->member_name ?? $log->coach_name ?? $log->staff_name ?? 'Kartu Tidak Terhubung';
            if(!$log->master_card_id) {
                $ownerName = 'Kartu Telah Dihapus';
            }

            $ownerType = 'Tidak Diketahui';
            $cardDetail = '-';
            if($log->member_name) { $ownerType = 'Member'; $cardDetail = $log->class_name ?? 'Tanpa Kelas'; }
            elseif($log->coach_name) { $ownerType = 'Pelatih'; $cardDetail = $log->coach_specialization ?? '-'; }
            elseif($log->staff_name) { $ownerType = 'Staff'; $cardDetail = $log->staff_position ?? '-'; }

            return [
                'id' => $log->id,
                'tapped_at' => Carbon::parse($log->tapped_at)->format('d M Y, H:i:s'),
                
                // --- PERBAIKAN UTAMA: Gunakan logika fallback yang sama seperti di Blade ---
                'card_uid' => $log->cardno ?? $log->card_uid,
                
                'owner_name' => $ownerName,
                'owner_type' => $ownerType,
                'owner_detail' => $cardDetail,
                'status' => $log->status,
                'message' => $log->message,
            ];
        });
        return response()->json($formattedLogs);
    }

    /**
     * Method private untuk membangun query yang kompleks agar tidak duplikasi kode.
     */
   private function buildQuery(Request $request)
{
    $query = TapLog::query()
        ->select(
            'tap_logs.*',
            'members.name as member_name',
            'coaches.name as coach_name',
            'staffs.name as staff_name',
            'classes.name as class_name',
            'coaches.specialization as coach_specialization',
            'staffs.position as staff_position',
            'master_cards.cardno'
        )
        ->leftJoin('master_cards', 'tap_logs.master_card_id', '=', 'master_cards.id')

        // Balik join: dari tabel yang punya master_card_id
        ->leftJoin('members', 'members.master_card_id', '=', 'master_cards.id')
        ->leftJoin('coaches', 'coaches.master_card_id', '=', 'master_cards.id')
        ->leftJoin('staffs', 'staffs.master_card_id', '=', 'master_cards.id')

        ->leftJoin('classes', 'members.school_class_id', '=', 'classes.id');

    // Filter nama
    if ($request->filled('name')) {
        $name = $request->name;
        $query->where(function ($q) use ($name) {
            $q->where('members.name', 'like', "%{$name}%")
              ->orWhere('coaches.name', 'like', "%{$name}%")
              ->orWhere('staffs.name', 'like', "%{$name}%");
        });
    }

    // Filter kelas (khusus member)
    if ($request->filled('class_id')) {
        $query->where('members.school_class_id', $request->class_id);
    }

    // Filter tipe pemilik
    if ($request->filled('owner_type')) {
        switch ($request->owner_type) {
            case 'member': $query->whereNotNull('members.id'); break;
            case 'coach': $query->whereNotNull('coaches.id'); break;
            case 'staff': $query->whereNotNull('staffs.id'); break;
        }
    }

    // Filter periode
    $period = $request->input('period', 'all_time');
    $start = null; $end = null;
    switch ($period) {
        case 'today':
            $start = now()->startOfDay(); $end = now()->endOfDay(); break;
        case 'this_week':
            $start = now()->startOfWeek(); $end = now()->endOfWeek(); break;
        case 'this_month':
            $start = now()->startOfMonth(); $end = now()->endOfMonth(); break;
        case 'custom':
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $start = Carbon::parse($request->start_date)->startOfDay();
                $end = Carbon::parse($request->end_date)->endOfDay();
            }
            break;
    }
    if ($start && $end) {
        $query->whereBetween('tap_logs.tapped_at', [$start, $end]);
    }

    return $query;
}
}
