<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\MasterCard;
use App\Models\AccessRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\TapLog;
use Carbon\Carbon;
class CoachController extends Controller
{
    /**
     * Menampilkan daftar semua pelatih.
     */
    public function index()
    {
        $coaches = Coach::with('masterCard', 'accessRule')->latest()->paginate(15);
        return view('coaches.index', compact('coaches'));
    }

    /**
     * Menampilkan form untuk membuat pelatih baru.
     */
    public function create()
    {
        $availableCards = MasterCard::where('card_type', 'coach')->where('assignment_status', 'available')->get();
        $accessRules = AccessRule::orderBy('name')->get();
        return view('coaches.create', compact('availableCards', 'accessRules'));
    }

    /**
     * Menyimpan pelatih baru ke database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'master_card_id' => ['required', 'integer', 'exists:master_cards,id', Rule::unique('coaches')->whereNull('deleted_at')],
            'join_date' => 'required|date',
            'specialization' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
            'rule_type' => 'required|in:template,custom',
            'access_rule_id' => 'required_if:rule_type,template|nullable|exists:access_rules,id',
            'max_taps_per_day' => 'nullable|integer|min:0',
            'max_taps_per_month' => 'nullable|integer|min:0',
            'allowed_days' => 'nullable|array',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
        ]);

        DB::transaction(function () use ($request, $validatedData) {
            $dataToCreate = $validatedData;
            
            if ($request->rule_type == 'template') {
                $dataToCreate['max_taps_per_day'] = null;
                $dataToCreate['max_taps_per_month'] = null;
                $dataToCreate['allowed_days'] = null;
                $dataToCreate['start_time'] = null;
                $dataToCreate['end_time'] = null;
            } else { // 'custom'
                $dataToCreate['access_rule_id'] = null;
            }

            Coach::create($dataToCreate);
            MasterCard::find($request->master_card_id)->update(['assignment_status' => 'assigned']);
        });

        return redirect()->route('coaches.index')->with('success', 'Pelatih baru berhasil didaftarkan.');
    }

    /**
     * Menampilkan detail spesifik pelatih.
     */
    public function show(Coach $coach)
    {
        $coach->load('masterCard', 'accessRule');
        $rule = null;
        $tapsData = [
            'max_daily' => 'N/A', 'used_daily' => 0, 'remaining_daily' => 'N/A',
            'max_monthly' => 'N/A', 'used_monthly' => 0, 'remaining_monthly' => 'N/A',
        ];

        if ($coach->rule_type == 'custom') {
            $rule = $coach;
        } elseif ($coach->accessRule) {
            $rule = $coach->accessRule;
        }

        if ($rule && $coach->masterCard) {
            $now = Carbon::now();
            $cardId = $coach->masterCard->id;

            if ($rule->max_taps_per_day !== null) {
                $tapsData['max_daily'] = $rule->max_taps_per_day;
                $dailyQuery = TapLog::where('master_card_id', $cardId)->whereDate('tapped_at', $now->toDateString())->where('status', 'granted');
                if ($coach->daily_tap_reset_at) $dailyQuery->where('tapped_at', '>=', $coach->daily_tap_reset_at);
                $tapsData['used_daily'] = $dailyQuery->count();
                $tapsData['remaining_daily'] = max(0, $tapsData['max_daily'] - $tapsData['used_daily']);
            } else {
                $tapsData['max_daily'] = 'Tak Terbatas';
                $tapsData['remaining_daily'] = 'Tak Terbatas';
            }

            if ($rule->max_taps_per_month !== null) {
                $tapsData['max_monthly'] = $rule->max_taps_per_month;
                $monthlyQuery = TapLog::where('master_card_id', $cardId)->whereMonth('tapped_at', $now->month)->whereYear('tapped_at', $now->year)->where('status', 'granted');
                if ($coach->monthly_tap_reset_at) $monthlyQuery->where('tapped_at', '>=', $coach->monthly_tap_reset_at);
                $tapsData['used_monthly'] = $monthlyQuery->count();
                $tapsData['remaining_monthly'] = max(0, $tapsData['max_monthly'] - $tapsData['used_monthly']);
            } else {
                $tapsData['max_monthly'] = 'Tak Terbatas';
                $tapsData['remaining_monthly'] = 'Tak Terbatas';
            }
        }

        return view('coaches.show', compact('coach', 'tapsData'));
    }

    /**
     * Menampilkan form untuk mengedit pelatih.
     */
    public function edit(Coach $coach)
    {
        $currentCard = $coach->masterCard;
        $otherCards = MasterCard::where('card_type', 'coach')->where('assignment_status', 'available')->get();
        $availableCards = $otherCards->push($currentCard)->filter()->sortBy('id');
        $accessRules = AccessRule::all();
        return view('coaches.edit', compact('coach', 'availableCards', 'accessRules'));
    }

    /**
     * Mengupdate data pelatih di database.
     */
    public function update(Request $request, Coach $coach)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'master_card_id' => ['required', 'integer', 'exists:master_cards,id', Rule::unique('coaches')->ignore($coach->id)],
            'join_date' => 'required|date',
            'specialization' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
            'rule_type' => 'required|in:template,custom',
            'access_rule_id' => 'required_if:rule_type,template|nullable|exists:access_rules,id',
            'max_taps_per_day' => 'nullable|integer|min:0',
            'max_taps_per_month' => 'nullable|integer|min:0',
            'allowed_days' => 'nullable|array',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
        ]);

        DB::transaction(function () use ($request, $validatedData, $coach) {
            $dataToUpdate = $validatedData;
            
            if ($request->rule_type == 'template') {
                $dataToUpdate['max_taps_per_day'] = null;
                $dataToUpdate['max_taps_per_month'] = null;
                $dataToUpdate['allowed_days'] = null;
                $dataToUpdate['start_time'] = null;
                $dataToUpdate['end_time'] = null;
            } else { // 'custom'
                $dataToUpdate['access_rule_id'] = null;
            }
            
            $oldCardId = $coach->master_card_id;
            $newCardId = $dataToUpdate['master_card_id'];

            $coach->update($dataToUpdate);

            if ($oldCardId != $newCardId) {
                if ($oldCardId) {
                    MasterCard::find($oldCardId)->update(['assignment_status' => 'available']);
                }
                MasterCard::find($newCardId)->update(['assignment_status' => 'assigned']);
            }
        });

        return redirect()->route('coaches.index')->with('success', 'Data pelatih berhasil diperbarui.');
    }

    /**
     * Menghapus data pelatih dari database.
     */
    public function destroy(Coach $coach)
    {
        DB::transaction(function () use ($coach) {
            if ($coach->masterCard) {
                $coach->masterCard->update(['assignment_status' => 'available']);
            }
            $coach->delete();
        });

        return redirect()->route('coaches.index')->with('success', 'Data pelatih berhasil dihapus.');
    }
    
}