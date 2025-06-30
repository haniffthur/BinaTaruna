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

class Dashboard extends Controller
{
    /**
     * Menampilkan halaman dashboard utama dengan data yang sudah difilter.
     */
    public function index(Request $request)
    {
        // 1. Tentukan Rentang Tanggal berdasarkan Filter dari Request
        $filterType = $request->input('filter', 'this_month'); // Default ke 'Bulan Ini'
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        switch ($filterType) {
            case 'today':
                $start = now()->startOfDay();
                $end = now()->endOfDay();
                $periodLabel = 'Hari Ini';
                break;
            case 'this_week':
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
                $periodLabel = 'Minggu Ini';
                break;
            case 'custom':
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
                $periodLabel = $start->format('j M Y') . ' - ' . $end->format('j M Y');
                break;
            case 'this_month':
            default:
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
                $periodLabel = 'Bulan Ini';
                break;
        }

        // 2. Ambil Data untuk Kartu Ringkasan (Info Cards) sesuai rentang
        $totalMembersInRange = Member::whereBetween('join_date', [$start, $end])->count();
        
        $revenueInRange = MemberTransaction::whereBetween('transaction_date', [$start, $end])->sum('total_amount')
                           + NonMemberTransaction::whereBetween('transaction_date', [$start, $end])->sum('total_amount');
        
        $totalTransactionsInRange = MemberTransaction::whereBetween('transaction_date', [$start, $end])->count()
                                    + NonMemberTransaction::whereBetween('transaction_date', [$start, $end])->count();

        $grantedTapsInRange = TapLog::where('status', 1)->whereBetween('tapped_at', [$start, $end])->count();


        // 3. Siapkan Data untuk Grafik Transaksi sesuai rentang
        $memberTransactionsQuery = DB::table('member_transactions')->select(DB::raw('DATE(transaction_date) as date'));
        $nonMemberTransactionsQuery = DB::table('non_member_transactions')->select(DB::raw('DATE(transaction_date) as date'));
        $transactionUnion = $memberTransactionsQuery->unionAll($nonMemberTransactionsQuery);

        $transactionsInRange = DB::query()->fromSub($transactionUnion, 'transactions')
            ->select(DB::raw('date'), DB::raw('count(*) as count'))
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get()->keyBy('date');

        $chartLabels = [];
        $chartData = [];
        // Buat periode tanggal dari awal hingga akhir filter
        $period = CarbonPeriod::create($start, $end);
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $chartLabels[] = $date->format('j M'); // Label untuk sumbu X
            $chartData[] = $transactionsInRange->get($formattedDate, (object)['count' => 0])->count;
        }

        // 4. Data untuk Daftar Aktivitas Terbaru (tidak terpengaruh filter)
        $recentTapLogs = TapLog::with(['masterCard.member', 'masterCard.coach', 'masterCard.staff'])
                            ->latest('tapped_at')->limit(7)->get();

        // 5. Kirim semua data yang sudah diolah ke view
        return view('dashboard.index', [
            'totalMembersInRange' => $totalMembersInRange,
            'revenueInRange' => $revenueInRange,
            'totalTransactionsInRange' => $totalTransactionsInRange,
            'grantedTapsInRange' => $grantedTapsInRange,
            'periodLabel' => $periodLabel,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'recentTapLogs' => $recentTapLogs,
        ]);
    }
}

