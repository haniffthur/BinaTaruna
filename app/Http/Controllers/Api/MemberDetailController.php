<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Carbon\Carbon; // Pastikan Carbon di-import

class MemberDetailController extends Controller
{
    /**
     * Mengambil dan mengembalikan detail lengkap dari seorang member sebagai JSON.
     */
    public function show(Member $member)
    {
        // Eager load semua relasi yang kita butuhkan untuk ditampilkan
        $member->load('masterCard', 'accessRule', 'schoolClass');

        // Tentukan aturan yang berlaku
        $activeRuleName = 'Default (Tanpa Batasan)';
        if ($member->rule_type == 'custom') {
            $activeRuleName = 'Aturan Custom';
        } elseif ($member->accessRule) {
            $activeRuleName = 'Template: ' . $member->accessRule->name;
        }

        // --- PERBAIKAN: Kirim lebih banyak data di dalam response JSON ---
        return response()->json([
            'id' => $member->id,
            'name' => $member->name,
            'photo_url' => $member->photo ? asset('storage/' . $member->photo) : 'https://via.placeholder.com/150',
            'class_name' => $member->schoolClass->name ?? '-',
            'card_uid' => $member->masterCard->cardno ?? 'Tidak ada kartu',
            'access_rule' => $activeRuleName,
            'phone_number' => $member->phone_number ?? '-',
            'join_date' => $member->join_date ? Carbon::parse($member->join_date)->translatedFormat('d F Y') : '-',
            'address' => $member->address ?? '-',
            
            // --- DATA BARU UNTUK MENGISI FORM ATURAN ---
            'rule_type' => $member->rule_type,
            'access_rule_id' => $member->access_rule_id,
            'max_taps_per_day' => $member->max_taps_per_day,
            'max_taps_per_month' => $member->max_taps_per_month,
            'allowed_days' => $member->allowed_days ?? [],
            'start_time' => $member->start_time ? Carbon::parse($member->start_time)->format('H:i') : '',
            'end_time' => $member->end_time ? Carbon::parse($member->end_time)->format('H:i') : '',
            // ---------------------------------------------
        ]);
    }
}
