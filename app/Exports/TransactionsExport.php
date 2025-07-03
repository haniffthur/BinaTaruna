<?php

namespace App\Exports;

use App\Models\MemberTransaction;
use App\Models\NonMemberTransaction;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class TransactionsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Mendefinisikan query untuk mengambil data dari database sesuai filter.
     */
    public function query()
    {
        $filters = $this->filters;

        // Logika penentuan tanggal (sama seperti di controller)
        $start = null;
        $end = null;
        if (isset($filters['period'])) {
            switch ($filters['period']) {
                case 'today': $start = now()->startOfDay(); $end = now()->endOfDay(); break;
                case 'this_week': $start = now()->startOfWeek(); $end = now()->endOfWeek(); break;
                case 'this_month': $start = now()->startOfMonth(); $end = now()->endOfMonth(); break;
                case 'custom':
                    if (isset($filters['start_date']) && isset($filters['end_date'])) {
                        $start = Carbon::parse($filters['start_date'])->startOfDay();
                        $end = Carbon::parse($filters['end_date'])->endOfDay();
                    }
                    break;
            }
        }
        
        // Query dasar (sama seperti di controller)
        $memberTransactionsQuery = DB::table('member_transactions')
            ->join('members', 'member_transactions.member_id', '=', 'members.id')
            ->leftJoin('transaction_details', 'member_transactions.id', '=', 'transaction_details.detailable_id')
            ->leftJoin('classes', 'transaction_details.purchasable_id', '=', 'classes.id')
            ->where('transaction_details.detailable_type', 'App\\Models\\MemberTransaction')
            ->select('member_transactions.id', 'members.name as customer_name', 'member_transactions.total_amount', 'member_transactions.transaction_date', DB::raw("'Member' as transaction_type"), 'classes.name as item_name', 'classes.id as class_id');

        $nonMemberTransactionsQuery = DB::table('non_member_transactions')
            ->select('id', 'customer_name', 'total_amount', 'transaction_date', DB::raw("'Non-Member' as transaction_type"), DB::raw("'(Transaksi Tiket)' as item_name"), DB::raw("NULL as class_id"));

        // Terapkan filter
        if (!empty($filters['name'])) {
            $memberTransactionsQuery->where('members.name', 'like', '%' . $filters['name'] . '%');
            $nonMemberTransactionsQuery->where('non_member_transactions.customer_name', 'like', '%' . $filters['name'] . '%');
        }
        if ($start && $end) {
            $memberTransactionsQuery->whereBetween('member_transactions.transaction_date', [$start, $end]);
            $nonMemberTransactionsQuery->whereBetween('non_member_transactions.transaction_date', [$start, $end]);
        }
        if (!empty($filters['class_id'])) {
            $memberTransactionsQuery->where('classes.id', $filters['class_id']);
            $nonMemberTransactionsQuery->whereRaw('1 = 0');
        }
        
        $allTransactionsUnion = $memberTransactionsQuery->unionAll($nonMemberTransactionsQuery);
        $finalQuery = DB::query()->fromSub($allTransactionsUnion, 'transactions');

        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $finalQuery->where('transaction_type', ucfirst($filters['type']));
        }

        return $finalQuery->orderBy('transaction_date', 'desc');
    }

    /**
     * Mendefinisikan header untuk setiap kolom di file Excel.
     */
    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Nama Pelanggan',
            'Tipe Transaksi',
            'Item Dibeli',
            'Total Bayar',
            'Tanggal Transaksi',
        ];
    }

    /**
     * Memetakan setiap baris data ke dalam format yang diinginkan.
     */
    public function map($transaction): array
    {
        return [
            '#' . $transaction->id,
            $transaction->customer_name ?? 'Tamu',
            $transaction->transaction_type,
            $transaction->item_name ?? '-',
            $transaction->total_amount,
            Carbon::parse($transaction->transaction_date)->format('Y-m-d H:i:s'),
        ];
    }
}
