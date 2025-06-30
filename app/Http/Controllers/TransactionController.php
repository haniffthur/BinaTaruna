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
use App\Models\NonMemberTicket;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    /**
     * Menampilkan riwayat gabungan dari semua jenis transaksi.
     *

     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function showNonMemberDetail($id)
    {
        // Cukup ambil transaksi dengan relasi tiket dan produk tiket
        $transaction = NonMemberTransaction::with(['purchasedTickets.ticketProduct'])->find($id); 

        if (!$transaction) {
            abort(404, 'Transaksi tidak ditemukan.');
        }

        // Tidak perlu membuat array $qrcodes di sini karena akan dibuat di Blade
        return view('transactions.non_member_detail', compact('transaction'));
    }

    /**
     * Menampilkan struk transaksi non-member (format cetak).
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function showNonMemberReceipt($id)
    {
        $transaction = NonMemberTransaction::with(['purchasedTickets.ticketProduct'])->find($id);

        if (!$transaction) {
            abort(404, 'Transaksi tidak ditemukan.');
        }

        $qrcodes = [];
        foreach ($transaction->purchasedTickets as $purchasedTicket) {
            $qrcodes[] = QrCode::size(120)->generate($purchasedTicket->qrcode);
        }

        return view('receipts.non_member', [
            'transaction' => $transaction,
            'tickets' => $transaction->purchasedTickets,
            'qrcodes' => $qrcodes,
        ]);
    }
   public function index(Request $request)
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

        $allTransactionsUnion = $memberTransactions->unionAll($nonMemberTransactions);

        $type = $request->input('type', 'all');

        $finalQuery = DB::query()->fromSub($allTransactionsUnion, 'transactions');

        if ($type === 'member') {
            $finalQuery->where('transaction_type', 'Member');
        } elseif ($type === 'non-member') {
            $finalQuery->where('transaction_type', 'Non-Member');
        }

        $transactions = $finalQuery
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);

        return view('transactions.index', compact('transactions'));
    }

    /**
     * Menampilkan detail transaksi non-member.
     * @param  int  $id
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory // Bisa juga dihilangkan sepenuhnya
     */
    

    /**
     * Menampilkan form transaksi untuk member.
     */
    public function createMemberTransaction()
    {
        $members = Member::orderBy('name')->get();
        $schoolClasses = SchoolClass::orderBy('name')->get(); 
        $availableCards = MasterCard::where('card_type', 'member')->where('assignment_status', 'available')->get();
        $accessRules = AccessRule::all();
        
        return view('transactions.member.create', compact('members', 'schoolClasses', 'availableCards', 'accessRules'));
    }

 public function storeMemberTransaction(Request $request)
    {
        try {
            $baseRules = [
                'transaction_type' => 'required|in:lama,baru',
                'class_id' => 'required|exists:classes,id',
                'amount_paid' => 'required|numeric|min:0',
            ];

            if ($request->input('transaction_type') == 'baru') {
                $newMemberRules = [
                    'name' => 'required|string|max:255',
                    'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            } else { // 'lama'
                $existingMemberRules = [
                    'member_id' => 'required|exists:members,id',
                    'update_rules' => 'nullable|boolean',
                    'update_rule_type' => 'required_if:update_rules,1|in:template,custom',
                    'update_access_rule_id' => 'required_if:update_rule_type,template|nullable|exists:access_rules,id',
                    'update_max_taps_per_day' => 'nullable|integer|min:0',
                    'update_max_taps_per_month' => 'nullable|integer|min:0',
                    'update_allowed_days' => 'nullable|array',
                    'update_start_time' => 'nullable|date_format:H:i',
                    'update_end_time' => 'nullable|date_format:H:i|after_or_equal:update_start_time',
                ];
                $rules = array_merge($baseRules, $existingMemberRules);
            }

            // --- DEBUG POINT A: Cek seluruh request data ---
            // dd($request->all()); 

            $validatedData = $request->validate($rules);

            // --- DEBUG POINT B: Cek data setelah validasi berhasil ---
            // dd('Validasi berhasil!', $validatedData); 

            $class = SchoolClass::find($request->class_id);
            if ($request->amount_paid < $class->price) {
                return back()->withInput()->with('error', 'Jumlah bayar kurang dari harga kelas.');
            }

            $resetMessages = [];
            DB::transaction(function () use ($request, $validatedData, $class, &$resetMessages) {
                $memberIdToUse = null;

                if ($request->transaction_type == 'baru') {
                    $dataMemberBaru = $validatedData;
                    if ($request->hasFile('photo')) {
                        $dataMemberBaru['photo'] = $request->file('photo')->store('member_photos', 'public');
                    }

                    $dataMemberBaru['school_class_id'] = $class->id; 

                    $dataMemberBaru['rule_type'] = null;
                    $dataMemberBaru['access_rule_id'] = null;
                    $dataMemberBaru['max_taps_per_day'] = null;
                    $dataMemberBaru['max_taps_per_month'] = null;
                    $dataMemberBaru['allowed_days'] = null;
                    $dataMemberBaru['start_time'] = null;
                    $dataMemberBaru['end_time'] = null;

                    if ($request->filled('master_card_id')) {
                        $dataMemberBaru['rule_type'] = $validatedData['rule_type'];
                        if ($validatedData['rule_type'] == 'template') {
                            $dataMemberBaru['access_rule_id'] = $validatedData['access_rule_id'];
                        } else { // 'custom'
                            $dataMemberBaru['max_taps_per_day'] = $validatedData['max_taps_per_day'] ?? null;
                            $dataMemberBaru['max_taps_per_month'] = $validatedData['max_taps_per_month'] ?? null;
                            $dataMemberBaru['allowed_days'] = $validatedData['allowed_days'] ?? null;
                            $dataMemberBaru['start_time'] = $validatedData['start_time'] ?? null;
                            $dataMemberBaru['end_time'] = $validatedData['end_time'] ?? null;
                        }
                    }
                    
                    $newMember = Member::create($dataMemberBaru);
                    if ($newMember->master_card_id) MasterCard::find($newMember->master_card_id)->update(['assignment_status' => 'assigned']);
                    $memberIdToUse = $newMember->id;

                } else { // 'lama'
                    $memberIdToUse = $validatedData['member_id'];
                    $member = Member::find($memberIdToUse);

                    // --- DEBUG POINT C: Cek apakah member lama ditemukan ---
                    // dd('Member lama ditemukan:', $member); 

                    if ($member) {
                        $updateData = ['school_class_id' => $class->id]; 
                        if ($request->has('update_rules') && $request->update_rules == 1) {
                            $updateData['rule_type'] = $validatedData['update_rule_type'];
                            
                            if ($request->update_rule_type == 'template') {
                                $updateData['access_rule_id'] = $validatedData['update_access_rule_id'];
                                $updateData['max_taps_per_day'] = null;
                                $updateData['max_taps_per_month'] = null;
                                $updateData['allowed_days'] = null;
                                $updateData['start_time'] = null;
                                $updateData['end_time'] = null;
                            } else {
                                $updateData['access_rule_id'] = null;
                                $updateData['max_taps_per_day'] = $validatedData['update_max_taps_per_day'] ?? null;
                                $updateData['max_taps_per_month'] = $validatedData['update_max_taps_per_month'] ?? null;
                                // --- PERBAIKAN: Gunakan 'update_allowed_days' untuk member lama ---
                                $updateData['allowed_days'] = $validatedData['update_allowed_days'] ?? null;
                                $updateData['start_time'] = $validatedData['update_start_time'] ?? null;
                                $updateData['end_time'] = $validatedData['update_end_time'] ?? null;
                            }
                            if ($member->max_taps_per_day != ($updateData['max_taps_per_day'] ?? null)) {
                                $updateData['daily_tap_reset_at'] = now();
                                $resetMessages[] = 'Hitungan tap harian telah di-reset.';
                            }
                            if ($member->max_taps_per_month != ($updateData['max_taps_per_month'] ?? null)) {
                                $updateData['monthly_tap_reset_at'] = now();
                                $resetMessages[] = 'Hitungan tap bulanan telah di-reset.';
                            }
                        }
                        // --- DEBUG POINT D: Data update member lama ---
                        // dd('Data update member lama:', $updateData); 
                        $member->update($updateData); // Member record is updated here
                    } else {
                        // --- DEBUG POINT E: Member tidak ditemukan (harusnya tidak terjadi jika validasi exists:members,id berhasil) ---
                        // dd('Error: Member lama tidak ditemukan dengan ID:', $memberIdToUse); 
                    }
                }
                MemberTransaction::create([
                    'member_id' => $memberIdToUse, 
                    'total_amount' => $class->price, 
                    'amount_paid' => $request->amount_paid, 
                    'change' => $request->amount_paid - $class->price, 
                    'transaction_date' => now(),
                ])->details()->create([
                    'purchasable_id' => $class->id, 
                    'purchasable_type' => SchoolClass::class, 
                    'quantity' => 1, 
                    'price' => $class->price,
                ]);
                Enrollment::updateOrCreate(
                    ['member_id' => $memberIdToUse, 'class_id' => $class->id], 
                    ['enrollment_date' => now(), 'status' => 'active']
                );

                // --- DEBUG POINT F: Transaksi dan pendaftaran berhasil dalam DB Transaction ---
                // dd('Transaksi dan Pendaftaran Berhasil dalam DB Transaction untuk member ID:', $memberIdToUse); 
            });

            $successMessage = 'Transaksi member berhasil diproses.';
            if (!empty($resetMessages)) {
                $successMessage .= ' ' . implode(' ', $resetMessages);
            }
            return redirect()->route('transactions.index')->with('success', $successMessage);

        } catch (ValidationException $e) {
            $members = Member::orderBy('name')->get();
            $schoolClasses = SchoolClass::orderBy('name')->get();
            $availableCards = MasterCard::where('card_type', 'member')->where('assignment_status', 'available')->get();
            $accessRules = AccessRule::all();

            return redirect()->back()->withErrors($e->validator)->withInput()->with(compact('members', 'schoolClasses', 'availableCards', 'accessRules'));
        } catch (\Exception $e) {
            // --- DEBUG POINT G: Terjadi Exception lain (di luar ValidationException) ---
            // Aktifkan baris di bawah ini saja, komentari yang lain.
            dd('Terjadi Exception:', $e->getMessage(), 'Trace:', $e->getTraceAsString()); 
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan form transaksi untuk non-member.
     */
    public function createNonMemberTransaction()
    {
        $tickets = Ticket::orderBy('name')->get();
        return view('transactions.non_member.create', compact('tickets'));
    }

    /**
     * Menyimpan transaksi non-member dengan logika kuantitas.
     */
    public function storeNonMemberTransaction(Request $request)
    {
        $validatedData = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'quantity' => 'required|integer|min:1',
            'customer_name' => 'nullable|string|max:255',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $ticket = Ticket::find($validatedData['ticket_id']);
        $quantity = (int)$validatedData['quantity'];
        $totalAmount = $ticket->price * $quantity;

        if ($validatedData['amount_paid'] < $totalAmount) {
            return back()->withInput()->with('error', 'Jumlah bayar kurang dari total harga.');
        }

        $transactionResult = DB::transaction(function () use ($request, $ticket, $quantity, $totalAmount) {
            
            $now = now(); // now() akan menggunakan timezone Asia/Jakarta
            $today = $now->format('Y-m-d');
            $datePrefix = $now->format('dmY'); // contoh: 22062025

            // Dapatkan tiket terakhir HARI INI untuk menentukan nomor urut berikutnya
            $lastTicketToday = NonMemberTicket::whereDate('created_at', $today)->orderBy('id', 'desc')->first();
            
            $nextSequence = 1;
            if ($lastTicketToday) {
                // PERBAIKAN: Gunakan 'qrcode' sesuai dengan nama kolom di database
                $lastToken = $lastTicketToday->qrcode;
                if ($lastToken) {
                    // Ambil 5 digit terakhir dari token sebagai nomor urut
                    $lastSequence = (int) substr($lastToken, -5); 
                    $nextSequence = $lastSequence + 1;
                }
            }

            // Buat satu transaksi induk
            $parentTransaction = NonMemberTransaction::create([
                'customer_name' => $request->customer_name,
                'total_amount' => $totalAmount,
                'amount_paid' => $request->amount_paid,
                'change' => $request->amount_paid - $totalAmount,
                'transaction_date' => $now,
            ]);

            $purchasedTickets = [];
            
            // Buat tiket sejumlah kuantitas dengan nomor urut yang berlanjut
            for ($i = 0; $i < $quantity; $i++) {
                $sequenceNumber = str_pad($nextSequence + $i, 5, '0', STR_PAD_LEFT);
                $newToken = $datePrefix . $sequenceNumber;

                $purchasedTickets[] = NonMemberTicket::create([
                    'non_member_transaction_id' => $parentTransaction->id,
                    'ticket_id' => $ticket->id,
                    // PERBAIKAN: Gunakan 'qrcode' untuk menyimpan token
                    'qrcode' => $newToken,
                ]);
            }

            return ['transaction' => $parentTransaction, 'tickets' => $purchasedTickets];
        });

        $qrcodes = [];
        foreach ($transactionResult['tickets'] as $purchasedTicket) {
            // PERBAIKAN: Ambil token dari 'qrcode'
            $qrcodes[] = QrCode::size(120)->generate($purchasedTicket->qrcode);
        }

        return view('receipts.non_member', [
            'transaction' => $transactionResult['transaction'],
            'tickets' => $transactionResult['tickets'],
            'qrcodes' => $qrcodes,
        ]);
    }
}
