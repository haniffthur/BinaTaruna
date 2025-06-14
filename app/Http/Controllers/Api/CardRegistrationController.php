<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterCard;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CardRegistrationController extends Controller
{
    /**
     * Menerima dan menyimpan data kartu baru dari perangkat keras.
     */
    public function register(Request $request)
    {
        // 1. Validasi data yang masuk dari ESP8266
        $validatedData = $request->validate([
            'card_uid' => 'required|string|unique:master_cards,card_uid',
            'card_type' => ['required', 'string', Rule::in(['member', 'staff', 'coach'])],
        ]);

        try {
            // 2. Jika validasi lolos, buat record baru di tabel master_cards
            $card = MasterCard::create([
                'card_uid' => $validatedData['card_uid'],
                'card_type' => $validatedData['card_type'],
                'assignment_status' => 'available', // Status awal selalu tersedia
            ]);

            // 3. Kirim respon sukses dalam format JSON
            return response()->json([
                'status' => 'success',
                'message' => 'Kartu ' . $card->card_type . ' dengan UID ' . $card->card_uid . ' berhasil didaftarkan!',
                'data' => $card
            ], 201); // 201 Created

        } catch (\Exception $e) {
            // Kirim respon error jika gagal menyimpan ke database
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan ke database: ' . $e->getMessage()
            ], 500);
        }
    }
}