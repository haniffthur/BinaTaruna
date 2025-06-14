<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\NonMemberTransaction;
use Illuminate\Http\Request;

class TicketValidationController extends Controller {
    public function validateTicket(Request $request) {
        $request->validate(['qr_code_token' => 'required|string|uuid']);
        $token = $request->qr_code_token;
        $transaction = NonMemberTransaction::where('qr_code_token', $token)->first();
        if (!$transaction) {
            return response()->json(['status' => 'error', 'message' => 'Tiket Tidak Valid'], 404);
        }
        if ($transaction->qr_code_status === 'used') {
            return response()->json([
                'status' => 'error', 
                'message' => 'Tiket Sudah Digunakan pada ' . $transaction->validated_at
            ], 409);
        }
        if ($transaction->qr_code_status === 'expired') {
            return response()->json(['status' => 'error', 'message' => 'Tiket Sudah Kadaluarsa'], 410);
        }
        $transaction->qr_code_status = 'used';
        $transaction->validated_at = now();
        $transaction->save();
        return response()->json([
            'status' => 'success', 
            'message' => 'Akses Diberikan',
            'customer' => $transaction->customer_name,
            'validated_at' => $transaction->validated_at
        ]);
    }
}