<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\SchoolClass;
use App\Models\Ticket;
use App\Models\MasterCard;
use App\Models\AccessRule;
use App\Models\Enrollment;
use App\Models\MemberTransaction;
use App\Models\NonMemberTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TransactionController extends Controller
{
    /**
     * Menampilkan riwayat gabungan dari semua jenis transaksi.
     */
    public function index()
    {
        $memberTransactions = DB::table('member_transactions')
            ->join('members', 'member_transactions.member_id', '=', 'members.id')
            ->select(
                'member_transactions.id', 'members.name as customer_name',
                'member_transactions.total_amount', 'member_transactions.transaction_date',
                DB::raw("'Member' as transaction_type")
            );

        $nonMemberTransactions = DB::table('non_member_transactions')
            ->select(
                'non_member_transactions.id', 'non_member_transactions.customer_name',
                'non_member_transactions.total_amount', 'non_member_transactions.transaction_date',
                DB::raw("'Non-Member' as transaction_type")
            );

        $allTransactionsQuery = $memberTransactions->unionAll($nonMemberTransactions);

        $transactions = DB::query()
            ->fromSub($allTransactionsQuery, 'transactions')
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);

        return view('transactions.index', compact('transactions'));
    }

    /**
     * Menampilkan form transaksi untuk member.
     */
    public function createMemberTransaction()
    {
        $members = Member::orderBy('name')->get();
        $classes = SchoolClass::all();
        $availableCards = MasterCard::where('card_type', 'member')->where('assignment_status', 'available')->get();
        $accessRules = AccessRule::all();
        
        return view('transactions.member.create', compact('members', 'classes', 'availableCards', 'accessRules'));
    }

    /**
     * Menyimpan transaksi member (baik member lama maupun baru).
     */
     public function storeMemberTransaction(Request $request)
    {
        $baseRules = [
            'transaction_type' => 'required|in:lama,baru',
            'class_id' => 'required|exists:classes,id',
            'amount_paid' => 'required|numeric|min:0',
        ];

        // Validasi berdasarkan tipe transaksi
        if ($request->input('transaction_type') == 'baru') {
            // Validasi ini sekarang sama persis dengan di MemberController
            $newMemberRules = [
                'name' => 'required|string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'school_class_id' => 'nullable|integer|exists:classes,id',
                'master_card_id' => ['nullable', 'integer', 'exists:master_cards,id', Rule::unique('members')->whereNull('deleted_at')],
                'join_date' => 'required|date',
                'rule_type' => 'required_with:master_card_id|in:template,custom',
                'access_rule_id' => 'required_if:rule_type,template|nullable|exists:access_rules,id',
                'max_taps_per_day' => 'nullable|integer|min:0',
                'max_taps_per_month' => 'nullable|integer|min:0',
                'allowed_days' => 'nullable|array',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
                'address' => 'nullable|string',
                'phone_number' => 'nullable|string|max:20',
                'date_of_birth' => 'nullable|date',
                'parent_name' => 'nullable|string|max:255',
            ];
            $rules = array_merge($baseRules, $newMemberRules);
        } else {
            $existingMemberRules = ['member_id' => 'required|exists:members,id'];
            $rules = array_merge($baseRules, $existingMemberRules);
        }
        $validatedData = $request->validate($rules);

        $class = SchoolClass::find($request->class_id);
        if ($request->amount_paid < $class->price) {
            return back()->withInput()->with('error', 'Jumlah bayar kurang dari harga kelas.');
        }

        try {
            DB::transaction(function () use ($request, $validatedData, $class) {
                $memberIdToUse = null;

                if ($request->transaction_type == 'baru') {
                    // Membuat member baru dengan SEMUA data dari form
                    $dataMemberBaru = $validatedData;

                    if ($request->hasFile('photo')) {
                        $dataMemberBaru['photo'] = $request->file('photo')->store('member_photos', 'public');
                    }
                    
                    if ($request->rule_type == 'template' || !$request->filled('master_card_id')) {
                        $dataMemberBaru['max_taps_per_day'] = null;
                        // ... null-kan semua kolom custom ...
                    } else {
                        $dataMemberBaru['access_rule_id'] = null;
                        // ... isi semua kolom custom ...
                    }

                    $newMember = Member::create($dataMemberBaru);
                    
                    if ($newMember->master_card_id) {
                        MasterCard::find($newMember->master_card_id)->update(['assignment_status' => 'assigned']);
                    }
                    $memberIdToUse = $newMember->id;

                } else { // 'lama'
                    $memberIdToUse = $validatedData['member_id'];
                }

                // Proses Inti Transaksi & Enrollment (tidak berubah)
                MemberTransaction::create([
                    'member_id' => $memberIdToUse,
                    'total_amount' => $class->price,
                    'amount_paid' => $request->amount_paid,
                    'change' => $request->amount_paid - $class->price,
                    'transaction_date' => now(),
                ])->details()->create([
                    'purchasable_id' => $class->id, 'purchasable_type' => SchoolClass::class,
                    'quantity' => 1, 'price' => $class->price,
                ]);

                Enrollment::updateOrCreate(
                    ['member_id' => $memberIdToUse, 'class_id' => $class->id],
                    ['enrollment_date' => now(), 'status' => 'active']
                );
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->route('transactions.index')->with('success', 'Transaksi member berhasil diproses.');
    }

    /**
     * Menampilkan form transaksi untuk non-member.
     */
    public function createNonMemberTransaction()
    {
        $tickets = Ticket::all();
        return view('transactions.non_member.create', compact('tickets'));
    }

    /**
     * Menyimpan transaksi non-member dan mengarahkan ke struk.
     */
 public function storeNonMemberTransaction(Request $request)
    {
        $validatedData = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'quantity' => 'required|integer|min:1', // Tetap validasi quantity
            'customer_name' => 'nullable|string|max:255',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $ticket = Ticket::find($validatedData['ticket_id']);
        $quantity = (int)$validatedData['quantity'];
        $totalAmount = $ticket->price * $quantity;

        if ($validatedData['amount_paid'] < $totalAmount) {
            return back()->withInput()->with('error', 'Jumlah bayar kurang dari total harga.');
        }

        // --- KEMBALI KE LOGIKA LAMA (Satu Transaksi, Satu QR Code) ---
        $transaction = DB::transaction(function () use ($request, $ticket, $quantity, $totalAmount) {
            
            // Buat satu transaksi induk dengan satu QR Code
            $newTransaction = NonMemberTransaction::create([
                'customer_name' => $request->customer_name,
                'qr_code_token' => (string) Str::uuid(), // Generate satu token
                'qr_code_status' => 'valid',
                'total_amount' => $totalAmount,
                'amount_paid' => $request->amount_paid,
                'change' => $request->amount_paid - $totalAmount,
                'transaction_date' => now(),
            ]);

            // Jika Anda memiliki model TransactionDetail, bagian ini bisa diaktifkan
            // untuk mencatat item apa yang dibeli.
            if (method_exists($newTransaction, 'details')) {
                $newTransaction->details()->create([
                    'purchasable_id' => $ticket->id,
                    'purchasable_type' => get_class($ticket),
                    'quantity' => $quantity,
                    'price' => $ticket->price,
                ]);
            }

            return $newTransaction;
        });
        // --- AKHIR DARI LOGIKA LAMA ---

        // Generate satu QR code dari token transaksi
        $qr = QrCode::size(150)->generate($transaction->qr_code_token);

        // Arahkan ke view struk
        return view('receipts.non_member', [
            'transaction' => $transaction,
            'qr' => $qr,
        ]);
    }
}