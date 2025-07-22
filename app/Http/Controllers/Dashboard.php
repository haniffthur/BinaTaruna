<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Staff;
use App\Models\Coach;
use App\Models\SchoolClass;
use App\Models\MemberTransaction;
use App\Models\NonMemberTransaction;
use App\Models\TapLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use PDF;

class Dashboard extends Controller
{
    /**
     * Menampilkan halaman dashboard utama (hanya kerangka dan data non-grafik).
     */
    public function index(Request $request)
    {
        // Ambil data kelas untuk dropdown filter
        $schoolClasses = SchoolClass::orderBy('name')->get();
        
        // --- AMBIL TOTAL SEMUA MEMBER DI SINI (TIDAK TERPENGARUH FILTER) ---
        // Jika Anda ingin MENGHITUNG member yang di-soft-delete juga, gunakan Member::withTrashed()->count();
        $totalAllMembers = Member::count(); 

        // --- PERBAIKAN: Eager load relasi yang lebih dalam untuk detail spesifik ---
        $recentTapLogs = TapLog::with([
            'masterCard.member.schoolClass', // Ambil data kelas member
            'masterCard.coach',              // Ambil data coach
            'masterCard.staff'               // Ambil data staff
        ])->latest('tapped_at')->limit(5)->get();

        // Pass totalAllMembers ke view
        return view('dashboard.index', compact('schoolClasses', 'recentTapLogs', 'totalAllMembers'));
    }

    /**
     * METHOD BARU: Mengambil dan mengolah data untuk grafik, lalu mengembalikannya sebagai JSON.
     */
    public function getChartData(Request $request)
    {
        $request->validate([
            'filter' => 'sometimes|in:today,this_week,this_month,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'class_id' => 'nullable|integer|exists:classes,id',
            'status_filter' => 'nullable|in:granted,denied', // NEW: Validation for status filter
        ]);

        $filterType = $request->input('filter', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $classId = $request->input('class_id');
        $statusFilter = $request->input('status_filter'); // NEW: Get status filter

        switch ($filterType) {
            case 'today': $start = now()->startOfDay(); $end = now()->endOfDay(); $periodLabel = 'Hari Ini'; break;
            case 'this_week': $start = now()->startOfWeek(); $end = now()->endOfWeek(); $periodLabel = 'Minggu Ini'; break;
            case 'custom': $start = Carbon::parse($startDate)->startOfDay(); $end = Carbon::parse($endDate)->endOfDay(); $periodLabel = $start->format('j M Y') . ' - ' . $end->format('j M Y'); break;
            default: $start = now()->startOfMonth(); $end = now()->endOfMonth(); $periodLabel = 'Bulan Ini'; break;
        }

        $baseMemberTransactionQuery = MemberTransaction::query()->whereBetween('transaction_date', [$start, $end]);
        // For 'Total Tap Masuk' card, if status filter is applied, only count those.
        $baseGrantedTapsQuery = TapLog::where('status', 1)->whereBetween('tapped_at', [$start, $end]);
        // For chart data and detailed logs, we need to consider both granted/denied.
        $baseTapLogsForChartQuery = TapLog::query()->whereBetween('tap_logs.tapped_at', [$start, $end]);


        // Apply class filter if selected
        if ($classId) {
            $baseMemberTransactionQuery->whereHas('member', fn($q) => $q->where('school_class_id', $classId));
            $baseGrantedTapsQuery->whereHas('masterCard.member', fn($q) => $q->where('school_class_id', $classId));
            $baseTapLogsForChartQuery->whereHas('masterCard.member', fn($q) => $q->where('school_class_id', $classId));
        }

        // NEW: Apply status filter to tap logs for chart and card
        if ($statusFilter) {
            $statusValue = ($statusFilter === 'granted') ? 1 : 0;
            $baseGrantedTapsQuery->where('status', $statusValue); // Affects the "Total Tap Masuk" card
            $baseTapLogsForChartQuery->where('status', $statusValue); // Affects the chart data
        }

        // --- Hitung Data untuk Kartu Ringkasan dari Query Dasar ---
        $memberRevenue = $baseMemberTransactionQuery->sum('total_amount');
        $memberTransactionsCount = $baseMemberTransactionQuery->count();
        $grantedTapsInRange = $baseGrantedTapsQuery->count();

        $nonMemberRevenue = !$classId ? NonMemberTransaction::whereBetween('transaction_date', [$start, $end])->sum('total_amount') : 0;
        $nonMemberTransactionsCount = !$classId ? NonMemberTransaction::whereBetween('transaction_date', [$start, $end])->count() : 0;
        
        $revenueInRange = $memberRevenue + $nonMemberRevenue;
        $totalTransactionsInRange = $memberTransactionsCount + $nonMemberTransactionsCount;
        
        // --- Olah Data untuk Grafik dari Query Dasar ---
        $diffInDays = $end->diffInDays($start);
        $groupByFormat = ($diffInDays > 365) ? DB::raw("DATE_FORMAT(tapped_at, '%Y-%m') as date") : DB::raw('DATE(tapped_at) as date');
        $period = ($diffInDays > 365) ? CarbonPeriod::create($start, '1 month', $end) : CarbonPeriod::create($start, '1 day', $end);
        $labelFormat = ($diffInDays > 365) ? 'M Y' : 'j M';
        $dateKeyFormat = ($diffInDays > 365) ? 'Y-m' : 'Y-m-d';

        // Only select status and count if no specific status filter is applied,
        // otherwise, we only show data for the selected status.
        $tapLogsData = $baseTapLogsForChartQuery
            ->select($groupByFormat, 'status', DB::raw('count(*) as count'))
            ->groupBy('date', 'status')
            ->orderBy('date', 'ASC')
            ->get();
        
        $chartLabels = [];
        $chartDataGranted = [];
        $chartDataDenied = [];

        foreach ($period as $date) {
            $formattedDate = $date->format($dateKeyFormat);
            $chartLabels[] = $date->format($labelFormat);

            // If a status filter is active, only show that data.
            // Otherwise, show both granted and denied.
            if ($statusFilter === 'granted') {
                $chartDataGranted[] = $tapLogsData->where('status', 1)->where('date', $formattedDate)->sum('count');
                $chartDataDenied[] = 0; // No denied data if filter is granted
            } elseif ($statusFilter === 'denied') {
                $chartDataGranted[] = 0; // No granted data if filter is denied
                $chartDataDenied[] = $tapLogsData->where('status', 0)->where('date', $formattedDate)->sum('count');
            } else { // No status filter, show both
                $chartDataGranted[] = $tapLogsData->where('status', 1)->where('date', $formattedDate)->sum('count');
                $chartDataDenied[] = $tapLogsData->where('status', 0)->where('date', $formattedDate)->sum('count');
            }
        }

        // Kembalikan semua data dalam format JSON
        return response()->json([
            'cards' => [
                'revenue' => 'Rp ' . number_format($revenueInRange, 0, ',', '.'),
                'transactions' => number_format($totalTransactionsInRange),
                'taps' => number_format($grantedTapsInRange), // This now reflects the status filter
            ],
            'chart' => [
                'period_label' => $periodLabel,
                'labels' => $chartLabels,
                'granted_data' => $chartDataGranted,
                'denied_data' => $chartDataDenied,
            ]
        ]);
    }

    public function generateReport(Request $request)
    {
        // 1. Validasi dan tentukan rentang tanggal (logika yang sama)
        $request->validate([
            'filter' => 'sometimes|in:today,this_week,this_month,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'class_id' => 'nullable|integer|exists:classes,id',
            'status_filter' => 'nullable|in:granted,denied', // NEW: Validation for status filter
        ]);

        $filterType = $request->input('filter', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $classId = $request->input('class_id');
        $statusFilter = $request->input('status_filter'); // NEW: Get status filter

        switch ($filterType) {
            case 'today': $start = now()->startOfDay(); $end = now()->endOfDay(); $periodLabel = 'Hari Ini'; break;
            case 'this_week': $start = now()->startOfWeek(); $end = now()->endOfWeek(); $periodLabel = 'Minggu Ini'; break;
            case 'custom': $start = Carbon::parse($startDate)->startOfDay(); $end = Carbon::parse($endDate)->endOfDay(); $periodLabel = $start->format('j M Y') . ' - ' . $end->format('j M Y'); break;
            default: $start = now()->startOfMonth(); $end = now()->endOfMonth(); $periodLabel = 'Bulan Ini'; break;
        }

        // Base queries
        $baseMemberTransactionQuery = MemberTransaction::query()->whereBetween('transaction_date', [$start, $end]);
        $baseGrantedTapsQuery = TapLog::where('status', 1)->whereBetween('tapped_at', [$start, $end]);
        $detailedTapLogsQuery = TapLog::with(['masterCard.member.schoolClass', 'masterCard.coach', 'masterCard.staff'])->whereBetween('tapped_at', [$start, $end]);

        // Apply class filter
        if ($classId) {
            $baseMemberTransactionQuery->whereHas('member', fn($q) => $q->where('school_class_id', $classId));
            $baseGrantedTapsQuery->whereHas('masterCard.member', fn($q) => $q->where('school_class_id', $classId));
            $detailedTapLogsQuery->whereHas('masterCard.member', fn($q) => $q->where('school_class_id', $classId));
        }

        // NEW: Apply status filter to detailed tap logs and total taps
        if ($statusFilter) {
            $statusValue = ($statusFilter === 'granted') ? 1 : 0;
            $baseGrantedTapsQuery->where('status', $statusValue); // Affects "Total Tap Masuk" card
            $detailedTapLogsQuery->where('status', $statusValue); // Affects the detailed logs table
        }
        
        $memberRevenue = $baseMemberTransactionQuery->sum('total_amount');
        $memberTransactionsCount = $baseMemberTransactionQuery->count();
        $nonMemberRevenue = !$classId ? NonMemberTransaction::whereBetween('transaction_date', [$start, $end])->sum('total_amount') : 0;
        $nonMemberTransactionsCount = !$classId ? NonMemberTransaction::whereBetween('transaction_date', [$start, $end])->count() : 0;
        
        $totalAllMembersForReport = Member::count();

        $summary = [
            'revenue' => $memberRevenue + $nonMemberRevenue,
            'new_members' => $totalAllMembersForReport,
            'transactions' => $memberTransactionsCount + $nonMemberTransactionsCount,
            'taps' => $baseGrantedTapsQuery->count(), // This now reflects the status filter
        ];

        $detailedTapLogs = $detailedTapLogsQuery->latest('tapped_at')->get();
        $filteredClass = $classId ? SchoolClass::find($classId) : null;

        // 3. --- PERBAIKAN UTAMA: Buat PDF dari view laporan ---
        $data = compact('summary', 'periodLabel', 'detailedTapLogs', 'filteredClass');
        
        // Muat view 'reports.dashboard' dengan data di atas
        $pdf = PDF::loadView('reports.dashboard', $data);
        
        // Atur nama file yang akan diunduh
        $fileName = 'Laporan-Dashboard-' . now()->format('Y-m-d') . '.pdf';

        // Kembalikan sebagai file PDF yang akan diunduh oleh browser
        return $pdf->download($fileName);
    }
}