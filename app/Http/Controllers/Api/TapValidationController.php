<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterCard;
use App\Models\TapLog;
use Carbon\Carbon;

class TapValidationController extends Controller
{
    public function validateTap(Request $request)
    {
        $request->validate(['card_uid' => 'required|string']);

        $card = MasterCard::with('member.accessRule', 'coach.accessRule', 'staff.accessRule')
            ->where('card_uid', $request->card_uid)->first();
            
        // Pengecekan awal kartu
        if (!$card || $card->assignment_status == 'available') {
            return response()->json(['status' => 'denied', 'message' => 'Kartu tidak terdaftar atau belum di-assign.'], 404);
        }

        // Dapatkan pemilik kartu
        $owner = $card->member ?? $card->coach ?? $card->staff;
        
        if (!$owner) {
             return response()->json(['status' => 'denied', 'message' => 'Pemilik kartu tidak ditemukan.'], 404);
        }

        // Logika untuk memilih aturan yang benar (sudah benar)
        $rule = null;
        if (isset($owner->rule_type) && $owner->rule_type == 'custom') {
            $rule = $owner;
        } else if ($owner->accessRule) {
            $rule = $owner->accessRule;
        }

        if (!$rule) {
            TapLog::create(['master_card_id' => $card->id, 'status' => 'granted', 'message' => 'Akses diberikan (tanpa aturan).']);
            return response()->json(['status' => 'granted', 'message' => 'Akses Diberikan', 'owner' => $owner->name ?? 'N/A']);
        }

        // ================================================================
        // === PERBAIKAN LOGIKA VALIDASI WAKTU ===
        // ================================================================
        $now = Carbon::now();
        $today = strtolower($now->format('l'));
        $currentTimeString = $now->format('H:i:s'); // Format waktu saat ini sebagai string

        // Validasi Hari (sudah benar)
        if ($rule->allowed_days && !in_array($today, $rule->allowed_days)) {
            $message = 'Akses ditolak: Bukan hari yang diizinkan.';
            TapLog::create(['master_card_id' => $card->id, 'status' => 'denied', 'message' => $message]);
            return response()->json(['status' => 'denied', 'message' => $message, 'owner' => $owner->name ?? 'N/A'], 403);
        }
        
        // Perbaikan Validasi Jam: Ubah semua menjadi string dengan format yang sama
        $startTimeString = $rule->start_time ? Carbon::parse($rule->start_time)->format('H:i:s') : null;
        $endTimeString = $rule->end_time ? Carbon::parse($rule->end_time)->format('H:i:s') : null;
        
        if (($startTimeString && $currentTimeString < $startTimeString) || ($endTimeString && $currentTimeString > $endTimeString)) {
             $message = 'Akses ditolak: Di luar jam operasional.';
             TapLog::create(['master_card_id' => $card->id, 'status' => 'denied', 'message' => $message]);
             return response()->json(['status' => 'denied', 'message' => $message, 'owner' => $owner->name ?? 'N/A'], 403);
        }
        // ================================================================
        // === AKHIR DARI PERBAIKAN ===
        // ================================================================
        
        // Validasi Limit Tap Harian (sudah benar)
        if ($rule->max_taps_per_day !== null && $rule->max_taps_per_day >= 0) {
            $dailyQuery = TapLog::where('master_card_id', $card->id)->whereDate('tapped_at', $now->toDateString())->where('status', 'granted');
            if (isset($owner->daily_tap_reset_at) && $owner->daily_tap_reset_at) {
                $dailyQuery->where('tapped_at', '>=', $owner->daily_tap_reset_at);
            }
            $tapsToday = $dailyQuery->count();
            
            if ($tapsToday >= $rule->max_taps_per_day) {
                $message = 'Akses ditolak: Limit harian tercapai.';
                TapLog::create(['master_card_id' => $card->id, 'status' => 'denied', 'message' => $message]);
                return response()->json(['status' => 'denied', 'message' => $message, 'owner' => $owner->name ?? 'N/A'], 429);
            }
        }
        
        // Validasi Limit Tap Bulanan (sudah benar)
        if ($rule->max_taps_per_month !== null && $rule->max_taps_per_month >= 0) {
             $monthlyQuery = TapLog::where('master_card_id', $card->id)->whereMonth('tapped_at', $now->month)->whereYear('tapped_at', $now->year)->where('status', 'granted');
             if (isset($owner->monthly_tap_reset_at) && $owner->monthly_tap_reset_at) {
                $monthlyQuery->where('tapped_at', '>=', $owner->monthly_tap_reset_at);
            }
            $tapsThisMonth = $monthlyQuery->count();

            if ($tapsThisMonth >= $rule->max_taps_per_month) {
                $message = 'Akses ditolak: Limit bulanan tercapai.';
                TapLog::create(['master_card_id' => $card->id, 'status' => 'denied', 'message' => $message]);
                return response()->json(['status' => 'denied', 'message' => 'Akses ditolak: Limit bulanan tercapai.', 'owner' => $owner->name ?? 'N/A'], 429);
            }
        }
        
        // Jika semua lolos, berikan akses
        TapLog::create(['master_card_id' => $card->id, 'status' => 'granted', 'message' => 'Akses diberikan.']);
        return response()->json(['status' => 'granted', 'message' => 'Akses Diberikan', 'owner' => $owner->name ?? 'N/A']);
    }
}
