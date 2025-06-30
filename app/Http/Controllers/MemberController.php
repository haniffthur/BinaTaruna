<?php



namespace App\Http\Controllers;



use App\Models\Member;

use App\Models\MasterCard;

use App\Models\AccessRule;

use App\Models\SchoolClass; // Pastikan ini di-import

use App\Models\TapLog;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Validation\Rule;

use Carbon\Carbon;

use Illuminate\Support\Facades\Storage; // Pastikan ini di-import



class MemberController extends Controller
{

    /**

    * Menampilkan daftar semua member.

    */

    public function index()
    {

        // Eager load relasi untuk efisiensi query

        $members = Member::with(['masterCard', 'accessRule', 'schoolClass'])->latest()->paginate(15);

        return view('members.index', compact('members'));

    }



    /**

    * Menampilkan form untuk membuat member baru.

    */

    public function create()
    {

        $availableCards = MasterCard::where('card_type', 'member')->where('assignment_status', 'available')->get();

        $accessRules = AccessRule::orderBy('name')->get();

        $schoolClasses = SchoolClass::orderBy('name')->get(); // Ambil data kelas



        return view('members.create', compact('availableCards', 'accessRules', 'schoolClasses'));

    }



    /**

    * Menyimpan member baru ke database.

    */

    public function store(Request $request)
    {

        // Membersihkan input jam yang kosong sebelum validasi

        if (empty($request->input('start_time')))
            $request->merge(['start_time' => null]);

        if (empty($request->input('end_time')))
            $request->merge(['end_time' => null]);


        $validatedData = $request->validate([

            'name' => 'required|string|max:255',

            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            'school_class_id' => 'nullable|integer|exists:classes,id',

            'master_card_id' => ['required', 'integer', 'exists:master_cards,id', Rule::unique('members')->whereNull('deleted_at')],

            'join_date' => 'required|date',

            'rule_type' => 'required|in:template,custom',

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

        ]);



        try {

            DB::transaction(function () use ($request, $validatedData) {

                $dataToCreate = $validatedData;



                // Logika upload foto

                if ($request->hasFile('photo')) {

                    $path = $request->file('photo')->store('member_photos', 'public');

                    $dataToCreate['photo'] = $path;

                }



                // Logika aturan akses

                if ($request->rule_type == 'template') {

                    $dataToCreate['max_taps_per_day'] = null;

                    $dataToCreate['max_taps_per_month'] = null;

                    $dataToCreate['allowed_days'] = null;

                    $dataToCreate['start_time'] = null;

                    $dataToCreate['end_time'] = null;

                } else { // 'custom'

                    $dataToCreate['access_rule_id'] = null;

                }



                Member::create($dataToCreate);

                MasterCard::find($request->master_card_id)->update(['assignment_status' => 'assigned']);

            });

        } catch (\Exception $e) {

            return back()->withInput()->with('error', 'Gagal menyimpan member: ' . $e->getMessage());

        }



        return redirect()->route('members.index')->with('success', 'Member baru berhasil didaftarkan.');

    }



    /**

    * Menampilkan detail spesifik member.

    */

   public function show(Member $member)
    {
        $member->load('masterCard', 'accessRule', 'schoolClass');
        $rule = null;

        $tapsData = [
            'max_daily' => 'N/A', 'used_daily' => 0, 'remaining_daily' => 'N/A',
            'max_monthly' => 'N/A', 'used_monthly' => 0, 'remaining_monthly' => 'N/A',
        ];

        if ($member->rule_type == 'custom') {
            $rule = $member;
        } elseif ($member->accessRule) {
            $rule = $member->accessRule;
        }

        if ($rule && $member->masterCard) {
            $now = Carbon::now();
            $cardId = $member->masterCard->id;

            // Penghitungan Tap Harian
            if ($rule->max_taps_per_day !== null && $rule->max_taps_per_day >= 0) {
                $tapsData['max_daily'] = (int) $rule->max_taps_per_day;
                
                // PERUBAHAN: Query untuk status = 1
                $dailyQuery = TapLog::where('master_card_id', $cardId)
                    ->whereDate('tapped_at', $now->toDateString())
                    ->where('status', 1); // <-- DIUBAH

                if ($member->daily_tap_reset_at) {
                    $dailyQuery->where('tapped_at', '>=', $member->daily_tap_reset_at);
                }

                $tapsData['used_daily'] = $dailyQuery->count();
                $tapsData['remaining_daily'] = max(0, $tapsData['max_daily'] - $tapsData['used_daily']);
            } else {
                $tapsData['max_daily'] = 'Tak Terbatas';
                $tapsData['remaining_daily'] = 'Tak Terbatas';
            }

            // Penghitungan Tap Bulanan
            if ($rule->max_taps_per_month !== null && $rule->max_taps_per_month >= 0) {
                $tapsData['max_monthly'] = (int) $rule->max_taps_per_month;

                // PERUBAHAN: Query untuk status = 1
                $monthlyQuery = TapLog::where('master_card_id', $cardId)
                    ->whereMonth('tapped_at', $now->month)
                    ->whereYear('tapped_at', $now->year)
                    ->where('status', 1); // <-- DIUBAH

                if ($member->monthly_tap_reset_at) {
                    $monthlyQuery->where('tapped_at', '>=', $member->monthly_tap_reset_at);
                }

                $tapsData['used_monthly'] = $monthlyQuery->count();
                $tapsData['remaining_monthly'] = max(0, $tapsData['max_monthly'] - $tapsData['used_monthly']);
            } else {
                $tapsData['max_monthly'] = 'Tak Terbatas';
                $tapsData['remaining_monthly'] = 'Tak Terbatas';
            }
        }

        return view('members.show', compact('member', 'tapsData'));
    }
    /**

    * Menampilkan form untuk mengedit member.

    */

    public function edit(Member $member)
    {

        $currentCard = $member->masterCard;

        $otherCards = MasterCard::where('card_type', 'member')->where('assignment_status', 'available')->get();

        $availableCards = $otherCards->push($currentCard)->filter()->sortBy('id');

        $accessRules = AccessRule::all();

        $schoolClasses = SchoolClass::orderBy('name')->get();



        return view('members.edit', compact('member', 'availableCards', 'accessRules', 'schoolClasses'));

    }



    /**

    * Mengupdate data member di database.

    */

    public function update(Request $request, Member $member)
    {

        if (empty($request->input('start_time')))
            $request->merge(['start_time' => null]);

        if (empty($request->input('end_time')))
            $request->merge(['end_time' => null]);



        $validatedData = $request->validate([

            'name' => 'required|string|max:255',

            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            'school_class_id' => 'nullable|integer|exists:classes,id',

            'master_card_id' => ['required', 'integer', 'exists:master_cards,id', Rule::unique('members')->ignore($member->id)],

            'join_date' => 'required|date',

            'rule_type' => 'required|in:template,custom',

            'access_rule_id' => 'required_if:rule_type,template|nullable|exists:access_rules,id',

            'max_taps_per_day' => 'nullable|integer|min:0',

            'max_taps_per_month' => 'nullable|integer|min:0',

            'allowed_days' => 'nullable|array',

            'start_time' => 'nullable|date_format:H:i',

            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',

        ]);



        $resetMessages = [];

        $dataToUpdate = $validatedData;



        if ($request->rule_type == 'custom') {

            $newDaily = (int) $request->input('max_taps_per_day');

            $newMonthly = (int) $request->input('max_taps_per_month');

            if ($member->max_taps_per_day != $newDaily) {

                $dataToUpdate['daily_tap_reset_at'] = now();

                $resetMessages[] = 'Hitungan tap harian telah di-reset.';

            }

            if ($member->max_taps_per_month != $newMonthly) {

                $dataToUpdate['monthly_tap_reset_at'] = now();

                $resetMessages[] = 'Hitungan tap bulanan telah di-reset.';

            }

        }



        if ($request->rule_type == 'template') {

            $dataToUpdate['max_taps_per_day'] = null;

            $dataToUpdate['max_taps_per_month'] = null;

            $dataToUpdate['allowed_days'] = null;

            $dataToUpdate['start_time'] = null;

            $dataToUpdate['end_time'] = null;

        } else {

            $dataToUpdate['access_rule_id'] = null;

        }



        if ($request->hasFile('photo')) {

            if ($member->photo)
                Storage::disk('public')->delete($member->photo);

            $dataToUpdate['photo'] = $request->file('photo')->store('member_photos', 'public');

        }



        try {

            DB::transaction(function () use ($member, $dataToUpdate) {

                $member->update($dataToUpdate);

            });

        } catch (\Exception $e) {

            return back()->withInput()->with('error', 'Gagal memperbarui member: ' . $e->getMessage());

        }



        $successMessage = 'Data member berhasil diperbarui.';

        if (!empty($resetMessages))
            $successMessage .= ' ' . implode(' ', $resetMessages);



        return redirect()->route('members.index')->with('success', $successMessage);

    }



    /**

    * Menghapus data member.

    */

    public function destroy(Member $member)
    {

        try {

            DB::transaction(function () use ($member) {

                if ($member->photo)
                    Storage::disk('public')->delete($member->photo);

                if ($member->masterCard)
                    $member->masterCard->update(['assignment_status' => 'available']);

                $member->delete(); // Menggunakan Soft Delete

            });

        } catch (\Exception $e) {

            return redirect()->route('members.index')->with('error', 'Gagal menghapus member: ' . $e->getMessage());

        }

        return redirect()->route('members.index')->with('success', 'Data member berhasil dihapus.');

    }

}