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
        
        // --- PERBAIKAN: Eager load relasi yang lebih dalam untuk detail spesifik ---
        $recentTapLogs = TapLog::with([
            'masterCard.member.schoolClass', // Ambil data kelas member
            'masterCard.coach',              // Ambil data coach
            'masterCard.staff'               // Ambil data staff
        ])->latest('tapped_at')->limit(5)->get();

        return view('dashboard.index', compact('schoolClasses', 'recentTapLogs'));
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
        ]);

        $filterType = $request->input('filter', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $classId = $request->input('class_id');

        switch ($filterType) {
            case 'today': $start = now()->startOfDay(); $end = now()->endOfDay(); $periodLabel = 'Hari Ini'; break;
            case 'this_week': $start = now()->startOfWeek(); $end = now()->endOfWeek(); $periodLabel = 'Minggu Ini'; break;
            case 'custom': $start = Carbon::parse($startDate)->startOfDay(); $end = Carbon::parse($endDate)->endOfDay(); $periodLabel = $start->format('j M Y') . ' - ' . $end->format('j M Y'); break;
            default: $start = now()->startOfMonth(); $end = now()->endOfMonth(); $periodLabel = 'Bulan Ini'; break;
        }

        // === PERBAIKAN UTAMA: Buat Query Dasar yang Bisa Difilter ===
        $baseMemberQuery = Member::query()->whereBetween('join_date', [$start, $end]);
        $baseMemberTransactionQuery = MemberTransaction::query()->whereBetween('transaction_date', [$start, $end]);
        $baseGrantedTapsQuery = TapLog::where('status', 1)->whereBetween('tapped_at', [$start, $end]);
        $baseTapLogsForChartQuery = TapLog::query()->join('master_cards', 'tap_logs.master_card_id', '=', 'master_cards.id')->leftJoin('members', 'master_cards.id', '=', 'members.master_card_id')->whereBetween('tap_logs.tapped_at', [$start, $end]);

        // Terapkan filter kelas jika dipilih
        if ($classId) {
            $baseMemberQuery->where('school_class_id', $classId);
            $baseMemberTransactionQuery->whereHas('member', fn($q) => $q->where('school_class_id', $classId));
            $baseGrantedTapsQuery->whereHas('masterCard.member', fn($q) => $q->where('school_class_id', $classId));
            $baseTapLogsForChartQuery->where('members.school_class_id', $classId);
        }

        // --- Hitung Data untuk Kartu Ringkasan dari Query Dasar ---
        $totalMembersInRange = $baseMemberQuery->count();
        $memberRevenue = $baseMemberTransactionQuery->sum('total_amount');
        $memberTransactionsCount = $baseMemberTransactionQuery->count();
        $grantedTapsInRange = $baseGrantedTapsQuery->count();

        $nonMemberRevenue = !$classId ? NonMemberTransaction::whereBetween('transaction_date', [$start, $end])->sum('total_amount') : 0;
        $nonMemberTransactionsCount = !$classId ? NonMemberTransaction::whereBetween('transaction_date', [$start, $end])->count() : 0;
        
        $revenueInRange = $memberRevenue + $nonMemberRevenue;
        $totalTransactionsInRange = $memberTransactionsCount + $nonMemberTransactionsCount;
        
        // --- Olah Data untuk Grafik dari Query Dasar ---
        $diffInDays = $end->diffInDays($start);
        $groupByFormat = ($diffInDays > 365) ? DB::raw("DATE_FORMAT(tap_logs.tapped_at, '%Y-%m') as date") : DB::raw('DATE(tap_logs.tapped_at) as date');
        $period = ($diffInDays > 365) ? CarbonPeriod::create($start, '1 month', $end) : CarbonPeriod::create($start, '1 day', $end);
        $labelFormat = ($diffInDays > 365) ? 'M Y' : 'j M';
        $dateKeyFormat = ($diffInDays > 365) ? 'Y-m' : 'Y-m-d';

        $tapLogsInRange = $baseTapLogsForChartQuery->select($groupByFormat, 'tap_logs.status', DB::raw('count(*) as count'))->groupBy('date', 'tap_logs.status')->orderBy('date', 'ASC')->get();
        
        $grantedTaps = $tapLogsInRange->where('status', 1)->keyBy('date');
        $deniedTaps = $tapLogsInRange->where('status', 0)->keyBy('date');

        $chartLabels = []; $chartDataGranted = []; $chartDataDenied = [];
        foreach ($period as $date) {
            $formattedDate = $date->format($dateKeyFormat);
            $chartLabels[] = $date->format($labelFormat);
            $chartDataGranted[] = $grantedTaps->get($formattedDate, (object)['count' => 0])->count;
            $chartDataDenied[] = $deniedTaps->get($formattedDate, (object)['count' => 0])->count;
        }

        // Kembalikan semua data dalam format JSON
        return response()->json([
            'cards' => [
                'revenue' => 'Rp ' . number_format($revenueInRange, 0, ',', '.'),
                'new_members' => number_format($totalMembersInRange),
                'transactions' => number_format($totalTransactionsInRange),
                'taps' => number_format($grantedTapsInRange),
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
        ]);

        $filterType = $request->input('filter', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $classId = $request->input('class_id');

        switch ($filterType) {
            case 'today': $start = now()->startOfDay(); $end = now()->endOfDay(); $periodLabel = 'Hari Ini'; break;
            case 'this_week': $start = now()->startOfWeek(); $end = now()->endOfWeek(); $periodLabel = 'Minggu Ini'; break;
            case 'custom': $start = Carbon::parse($startDate)->startOfDay(); $end = Carbon::parse($endDate)->endOfDay(); $periodLabel = $start->format('j M Y') . ' - ' . $end->format('j M Y'); break;
            default: $start = now()->startOfMonth(); $end = now()->endOfMonth(); $periodLabel = 'Bulan Ini'; break;
        }

        // 2. Ambil semua data yang diperlukan (logika yang sama)
        $baseMemberQuery = Member::query()->whereBetween('join_date', [$start, $end]);
        $baseMemberTransactionQuery = MemberTransaction::query()->whereBetween('transaction_date', [$start, $end]);
        $baseGrantedTapsQuery = TapLog::where('status', 1)->whereBetween('tapped_at', [$start, $end]);
        $detailedTapLogsQuery = TapLog::with(['masterCard.member.schoolClass', 'masterCard.coach', 'masterCard.staff'])->whereBetween('tapped_at', [$start, $end]);

        if ($classId) {
            $baseMemberQuery->where('school_class_id', $classId);
            $baseMemberTransactionQuery->whereHas('member', fn($q) => $q->where('school_class_id', $classId));
            $baseGrantedTapsQuery->whereHas('masterCard.member', fn($q) => $q->where('school_class_id', $classId));
            $detailedTapLogsQuery->whereHas('masterCard.member', fn($q) => $q->where('school_class_id', $classId));
        }

        $memberRevenue = $baseMemberTransactionQuery->sum('total_amount');
        $memberTransactionsCount = $baseMemberTransactionQuery->count();
        $nonMemberRevenue = !$classId ? NonMemberTransaction::whereBetween('transaction_date', [$start, $end])->sum('total_amount') : 0;
        $nonMemberTransactionsCount = !$classId ? NonMemberTransaction::whereBetween('transaction_date', [$start, $end])->count() : 0;
        $summary = [
            'revenue' => $memberRevenue + $nonMemberRevenue,
            'new_members' => $baseMemberQuery->count(),
            'transactions' => $memberTransactionsCount + $nonMemberTransactionsCount,
            'taps' => $baseGrantedTapsQuery->count(),
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
