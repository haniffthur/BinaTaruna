<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\MasterCard;
use App\Models\AccessRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\TapLog;

class StaffController extends Controller
{
    /**
     * Menampilkan daftar semua staff.
     */
    public function index()
    {
        $staffs = Staff::with(['masterCard', 'accessRule'])->latest()->paginate(15);
        return view('staffs.index', compact('staffs'));
    }

    /**
     * Menampilkan form untuk membuat staff baru.
     */
    public function create()
    {
        $availableCards = MasterCard::where('card_type', 'staff')->where('assignment_status', 'available')->get();
        $accessRules = AccessRule::orderBy('name')->get();
        return view('staffs.create', compact('availableCards', 'accessRules'));
    }

    /**
     * Menyimpan staff baru ke database.
     */
    public function store(Request $request)
    {
        if (empty($request->input('start_time'))) $request->merge(['start_time' => null]);
        if (empty($request->input('end_time'))) $request->merge(['end_time' => null]);
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'master_card_id' => ['nullable', 'integer', 'exists:master_cards,id', Rule::unique('staffs')->whereNull('deleted_at')],
            'join_date' => 'required|date',
            'phone_number' => 'nullable|string|max:20',
            'rule_type' => 'required|in:template,custom',
            'access_rule_id' => 'required_if:rule_type,template|nullable|exists:access_rules,id',
            'max_taps_per_day' => 'nullable|integer|min:0',
            'max_taps_per_month' => 'nullable|integer|min:0',
            'allowed_days' => 'nullable|array',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
        ]);

        try {
            DB::transaction(function () use ($request, $validatedData) {
                $dataToCreate = $validatedData;
                
                if ($request->rule_type == 'template') {
                    $dataToCreate['max_taps_per_day'] = null;
                    $dataToCreate['max_taps_per_month'] = null;
                    $dataToCreate['allowed_days'] = null;
                    $dataToCreate['start_time'] = null;
                    $dataToCreate['end_time'] = null;
                } else {
                    $dataToCreate['access_rule_id'] = null;
                }

                $staff = Staff::create($dataToCreate);
                if($staff->master_card_id) {
                    MasterCard::find($staff->master_card_id)->update(['assignment_status' => 'assigned']);
                }
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan staff: ' . $e->getMessage());
        }

        return redirect()->route('staffs.index')->with('success', 'Staff baru berhasil didaftarkan.');
    }

    /**
     * Menampilkan form untuk mengedit staff.
     */
    public function edit(Staff $staff)
    {
        $currentCard = $staff->masterCard;
        $otherCards = MasterCard::where('card_type', 'staff')->where('assignment_status', 'available')->get();
        // Gabungkan kartu saat ini dengan kartu lain yang tersedia, jika kartu saat ini ada
        if($currentCard) {
            $availableCards = $otherCards->push($currentCard)->unique('id')->sortBy('id');
        } else {
            $availableCards = $otherCards->sortBy('id');
        }
        
        $accessRules = AccessRule::orderBy('name')->get();
        return view('staffs.edit', compact('staff', 'availableCards', 'accessRules'));
    }

    /**
     * Mengupdate data staff di database.
     */
    public function update(Request $request, Staff $staff)
    {
        if (empty($request->input('start_time'))) $request->merge(['start_time' => null]);
        if (empty($request->input('end_time'))) $request->merge(['end_time' => null]);
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'master_card_id' => ['nullable', 'integer', 'exists:master_cards,id', Rule::unique('staffs')->ignore($staff->id)],
            'join_date' => 'required|date',
            'phone_number' => 'nullable|string|max:20',
            'rule_type' => 'required|in:template,custom',
            'access_rule_id' => 'required_if:rule_type,template|nullable|exists:access_rules,id',
            'max_taps_per_day' => 'nullable|integer|min:0',
            'max_taps_per_month' => 'nullable|integer|min:0',
            'allowed_days' => 'nullable|array',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
        ]);

        try {
            DB::transaction(function () use ($request, $validatedData, $staff) {
                $dataToUpdate = $validatedData;
                
                if ($request->rule_type == 'template') {
                    $dataToUpdate['max_taps_per_day'] = null;
                    $dataToUpdate['max_taps_per_month'] = null;
                    $dataToUpdate['allowed_days'] = null;
                    $dataToUpdate['start_time'] = null;
                    $dataToUpdate['end_time'] = null;
                } else {
                    $dataToUpdate['access_rule_id'] = null;
                }
                
                $oldCardId = $staff->master_card_id;
                $newCardId = $dataToUpdate['master_card_id'] ?? null;

                $staff->update($dataToUpdate);

                if ($oldCardId != $newCardId) {
                    if ($oldCardId) {
                        MasterCard::find($oldCardId)->update(['assignment_status' => 'available']);
                    }
                    if ($newCardId) {
                        MasterCard::find($newCardId)->update(['assignment_status' => 'assigned']);
                    }
                }
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui staff: ' . $e->getMessage());
        }

        return redirect()->route('staffs.index')->with('success', 'Data staff berhasil diperbarui.');
    }

    /**
     * Menghapus data staff.
     */
    public function destroy(Staff $staff)
    {
        try {
            DB::transaction(function () use ($staff) {
                if ($staff->masterCard) {
                    $staff->masterCard->update(['assignment_status' => 'available']);
                }
                $staff->delete(); // Menggunakan Soft Delete jika modelnya pakai
            });
        } catch (\Exception $e) {
            return redirect()->route('staffs.index')->with('error', 'Gagal menghapus staff: ' . $e->getMessage());
        }
        
        return redirect()->route('staffs.index')->with('success', 'Data staff berhasil dihapus.');
    }
    public function show(Staff $staff)
    {
        $staff->load('masterCard', 'accessRule');
        $rule = null;
        $tapsData = [
            'max_daily' => 'N/A', 'used_daily' => 0, 'remaining_daily' => 'N/A',
            'max_monthly' => 'N/A', 'used_monthly' => 0, 'remaining_monthly' => 'N/A',
        ];

        if ($staff->rule_type == 'custom') {
            $rule = $staff;
        } elseif ($staff->accessRule) {
            $rule = $staff->accessRule;
        }

        if ($rule && $staff->masterCard) {
            $now = Carbon::now();
            $cardId = $staff->masterCard->id;

            if ($rule->max_taps_per_day !== null) {
                $tapsData['max_daily'] = $rule->max_taps_per_day;
                $dailyQuery = TapLog::where('master_card_id', $cardId)->whereDate('tapped_at', $now->toDateString())->where('status', 'granted');
                if ($staff->daily_tap_reset_at) $dailyQuery->where('tapped_at', '>=', $staff->daily_tap_reset_at);
                $tapsData['used_daily'] = $dailyQuery->count();
                $tapsData['remaining_daily'] = max(0, $tapsData['max_daily'] - $tapsData['used_daily']);
            } else {
                $tapsData['max_daily'] = 'Tak Terbatas';
                $tapsData['remaining_daily'] = 'Tak Terbatas';
            }

            if ($rule->max_taps_per_month !== null) {
                $tapsData['max_monthly'] = $rule->max_taps_per_month;
                $monthlyQuery = TapLog::where('master_card_id', $cardId)->whereMonth('tapped_at', $now->month)->whereYear('tapped_at', $now->year)->where('status', 'granted');
                if ($staff->monthly_tap_reset_at) $monthlyQuery->where('tapped_at', '>=', $staff->monthly_tap_reset_at);
                $tapsData['used_monthly'] = $monthlyQuery->count();
                $tapsData['remaining_monthly'] = max(0, $tapsData['max_monthly'] - $tapsData['used_monthly']);
            } else {
                $tapsData['max_monthly'] = 'Tak Terbatas';
                $tapsData['remaining_monthly'] = 'Tak Terbatas';
            }
        }

        return view('staffs.show', compact('staff', 'tapsData'));
    }
}
