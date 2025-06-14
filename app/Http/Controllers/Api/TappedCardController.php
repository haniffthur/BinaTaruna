<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session; // Digunakan untuk menyimpan di sesi
use Illuminate\Support\Facades\Auth;   // Digunakan untuk mengidentifikasi user yang login
use Illuminate\Support\Facades\Cache;  // Opsi lain yang lebih persistent

class TappedCardController extends Controller
{
    /**
     * Menerima UID kartu dari ESP8266 dan menyimpannya sementara.
     * Diasumsikan ESP8266 mengirimkan 'card_uid'.
     */
    public function storeTappedUid(Request $request)
    {
        $request->validate(['card_uid' => 'required|string|max:255']);
        $cardUid = $request->input('card_uid');
        // Simpan UID ke cache atau database sementara
        // Menggunakan Cache lebih baik karena bisa diatur TTL (Time To Live)
        // Kunci cache unik untuk setiap user yang login agar tidak bercampur
        $userId = Auth::id() ?? 'guest'; // Jika user tidak login, pakai 'guest' atau IP
        Cache::put('tapped_card_uid_for_user_' . $userId, $cardUid, now()->addMinutes(2)); // Simpan selama 5 menit

        return response()->json([
            'status' => 'success',
            'message' => 'UID ' . $cardUid . ' berhasil diterima dan disimpan sementara.'
        ], 200);
    }

    /**
     * Mengambil UID kartu yang disimpan sementara untuk user yang sedang login.
     * Dipanggil oleh JavaScript dari halaman form pendaftaran kartu.
     */
    public function getTappedUid(Request $request)
    {
        $userId = Auth::id() ?? 'guest'; // Harus sama dengan kunci yang digunakan di storeTappedUid
        $tappedUid = Cache::get('tapped_card_uid_for_user_' . $userId);

        // Hapus UID dari cache setelah dibaca agar tidak diambil lagi
        if ($tappedUid) {
            Cache::forget('tapped_card_uid_for_user_' . $userId);
        }

        return response()->json([
            'card_uid' => $tappedUid
        ]);
    }
}