<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MasterCard;
use App\Models\AccessRule;
use App\Models\SchoolClass;
use App\Models\TapLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel; // Pastikan ini di-import
use Maatwebsite\Excel\Validators\ValidationException; // Pastikan ini di-import
use App\Imports\MembersImport; // Pastikan ini di-import
use PhpOffice\PhpSpreadsheet\Spreadsheet; // Pastikan ini di-import untuk download template
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; // Pastikan ini di-import untuk download template
use Illuminate\Support\Facades\Response; // Pastikan ini di-import untuk download template
use PhpOffice\PhpSpreadsheet\Style\NumberFormat; // <-- Tambahkan ini
use App\Exports\MembersExport; // <-- Tambahkan ini untuk export laporan

class MemberController extends Controller
{
    /**
     * Menampilkan daftar semua member dengan filter.
     */
    public function index(Request $request)
    {
        // Eager load relasi untuk efisiensi query
        $members = Member::with(['masterCard', 'accessRule', 'schoolClass']);

        // --- Logika Filter ---

        // Filter berdasarkan Nama
        if ($request->filled('name')) {
            $members->where('name', 'like', '%' . $request->input('name') . '%');
        }

        // Filter berdasarkan Kelas
        if ($request->filled('school_class_id')) {
            $members->where('school_class_id', $request->input('school_class_id'));
        }

        // Filter berdasarkan Tanggal Bergabung
        if ($request->filled('join_date')) {
            $members->whereDate('join_date', $request->input('join_date'));
        }

        // --- Akhir Logika Filter ---

        // Urutkan dan paginasi hasilnya
        $members = $members->latest()->paginate(15);

        // Ambil semua kelas untuk dropdown filter
        $schoolClasses = SchoolClass::orderBy('name')->get();

        return view('members.index', compact('members', 'schoolClasses'));
    }

    /**
     * Menampilkan form untuk membuat member baru.
     */
    public function create()
    {
        $availableCards = MasterCard::where('card_type', 'member')->where('assignment_status', 'available')->get();
        $accessRules = AccessRule::orderBy('name')->get();
        $schoolClasses = SchoolClass::orderBy('name')->get();

        return view('members.create', compact('availableCards', 'accessRules', 'schoolClasses'));
    }

    /**
     * Menyimpan member baru ke database.
     */
    public function store(Request $request)
    {
        // Membersihkan input jam yang kosong sebelum validasi
        

        
        if (empty($request->input('start_time'))) {
            $request->merge(['start_time' => null]);
        }
        if (empty($request->input('end_time'))) {
            $request->merge(['end_time' => null]);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'nis' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('members', 'nis')->whereNull('deleted_at'),
            ],
            'nisnas' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('members', 'nisnas')->whereNull('deleted_at'),
            ],
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
                    // Pastikan allowed_days disimpan sebagai JSON jika ada
                    if (isset($dataToCreate['allowed_days']) && is_array($dataToCreate['allowed_days'])) {
                        $dataToCreate['allowed_days'] = json_encode($dataToCreate['allowed_days']);
                    } else {
                        $dataToCreate['allowed_days'] = null; // Set to null if no days are selected for custom
                    }
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
            'max_daily' => 'N/A',
            'used_daily' => 0,
            'remaining_daily' => 'N/A',
            'max_monthly' => 'N/A',
            'used_monthly' => 0,
            'remaining_monthly' => 'N/A',
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

                $dailyQuery = TapLog::where('master_card_id', $cardId)
                    ->whereDate('tapped_at', $now->toDateString())
                    ->where('status', 1);

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

                $monthlyQuery = TapLog::where('master_card_id', $cardId)
                    ->whereMonth('tapped_at', $now->month)
                    ->whereYear('tapped_at', $now->year)
                    ->where('status', 1);

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
        if (empty($request->input('start_time'))) $request->merge(['start_time' => null]);
        if (empty($request->input('end_time'))) $request->merge(['end_time' => null]);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'school_class_id' => 'nullable|integer|exists:classes,id',
            'master_card_id' => ['nullable', 'integer', 'exists:master_cards,id', Rule::unique('members')->ignore($member->id)],
            'join_date' => 'required|date',
            'rule_type' => 'required_with:master_card_id|in:template,custom',
            'access_rule_id' => 'required_if:rule_type,template|nullable|exists:access_rules,id',
            'max_taps_per_day' => 'nullable|integer|min:0',
            'max_taps_per_month' => 'nullable|integer|min:0',
            'allowed_days' => 'nullable|array',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
            // Tambahkan validasi untuk data diri lainnya jika ada
        ]);
        
        $dataToUpdate = $validatedData;
        $resetMessages = [];

        // ====================================================================
        // === PERBAIKAN FINAL: Logika Reset Tap yang Benar-Benar Cerdas    ===
        // ====================================================================
        
        // Hanya jalankan logika reset jika kartu terpasang
        if ($member->masterCard) {
            // 1. Tentukan batas tap LAMA dari member
            $oldRule = $member->rule_type == 'custom' ? $member : $member->accessRule;
            $oldDailyLimit = $oldRule ? $oldRule->max_taps_per_day : null;
            $oldMonthlyLimit = $oldRule ? $oldRule->max_taps_per_month : null;

            // 2. Tentukan batas tap BARU berdasarkan pilihan user
            $newDailyLimit = null;
            $newMonthlyLimit = null;
            if ($request->rule_type == 'custom') {
                $newDailyLimit = $request->input('max_taps_per_day');
                $newMonthlyLimit = $request->input('max_taps_per_month');
            } else { // Jika tipe adalah 'template'
                if ($request->filled('access_rule_id')) {
                    // Ambil data dari template yang dipilih
                    $selectedRule = AccessRule::find($request->access_rule_id);
                    if ($selectedRule) {
                        $newDailyLimit = $selectedRule->max_taps_per_day;
                        $newMonthlyLimit = $selectedRule->max_taps_per_month;
                    }
                }
            }

            // 3. Bandingkan nilai LAMA dengan nilai BARU
            if ($oldDailyLimit != $newDailyLimit) {
                $dataToUpdate['daily_tap_reset_at'] = now();
                $resetMessages[] = 'Hitungan tap harian telah di-reset.';
            }
            if ($oldMonthlyLimit != $newMonthlyLimit) {
                $dataToUpdate['monthly_tap_reset_at'] = now();
                $resetMessages[] = 'Hitungan tap bulanan telah di-reset.';
            }
        }
        
        // 4. Atur data final yang akan disimpan berdasarkan tipe aturan
        if ($request->rule_type == 'template') {
            $dataToUpdate['max_taps_per_day'] = null;
            $dataToUpdate['max_taps_per_month'] = null;
            $dataToUpdate['allowed_days'] = null;
            $dataToUpdate['start_time'] = null;
            $dataToUpdate['end_time'] = null;
        } else { // 'custom'
            $dataToUpdate['access_rule_id'] = null;
        }
        // --- AKHIR DARI PERBAIKAN ---

        if ($request->hasFile('photo')) {
            if ($member->photo) Storage::disk('public')->delete($member->photo);
            $dataToUpdate['photo'] = $request->file('photo')->store('member_photos', 'public');
        }

        try {
            DB::transaction(function () use ($member, $request, $dataToUpdate) {
                $oldCardId = $member->master_card_id;
                $newCardId = $dataToUpdate['master_card_id'] ?? null;

                $member->update($dataToUpdate);

                if ($oldCardId != $newCardId) {
                    if ($oldCardId) MasterCard::find($oldCardId)->update(['assignment_status' => 'available']);
                    if ($newCardId) MasterCard::find($newCardId)->update(['assignment_status' => 'assigned']);
                }
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui member: ' . $e->getMessage());
        }

        $successMessage = 'Data member berhasil diperbarui.';
        if (!empty($resetMessages)) $successMessage .= ' ' . implode(' ', $resetMessages);

        return redirect()->route('members.index')->with('success', $successMessage);
    }

    /**
     * Menghapus data member.
     */
    public function destroy(Member $member)
    {
        try {
            DB::transaction(function () use ($member) {
                if ($member->photo) {
                    Storage::disk('public')->delete($member->photo);
                }
                if ($member->masterCard) {
                    $member->masterCard->update(['assignment_status' => 'available']);
                }
                $member->forceDelete(); // Menggunakan Soft Delete
            });
        } catch (\Exception $e) {
            return redirect()->route('members.index')->with('error', 'Gagal menghapus member: ' . $e->getMessage());
        }
        return redirect()->route('members.index')->with('success', 'Data member berhasil dihapus.');
    }

    /**
     * API endpoint untuk mendapatkan data member.
     */
    public function getMemberApiData(Member $member)
    {
        $member->load(['masterCard', 'schoolClass', 'accessRule']);
        return response()->json([
            'id' => $member->id,
            'name' => $member->name,
            'nickname' => $member->nickname,
            'nis' => $member->nis,
            'nisnas' => $member->nisnas,
            'photo_url' => $member->photo ? asset('storage/' . $member->photo) : 'https://via.placeholder.com/150',
            'class_name' => $member->schoolClass->name ?? 'Tidak ada kelas',
            'card_uid' => $member->masterCard->cardno ?? 'Tidak ada kartu',
            'master_card_id' => $member->masterCard->id ?? null,
            'rule_type' => $member->rule_type,
            'access_rule_id' => $member->access_rule_id,
            'access_rule' => $member->rule_type == 'custom' ? 'Custom' : ($member->accessRule->name ?? 'Default'),
            'max_taps_per_day' => $member->max_taps_per_day,
            'max_taps_per_month' => $member->max_taps_per_month,
            'allowed_days' => $member->allowed_days,
            'start_time' => $member->start_time ? $member->start_time->format('H:i') : null,
            'end_time' => $member->end_time ? $member->end_time->format('H:i') : null,
        ]);
    }

    /**
     * Metode untuk mengunduh template Excel.
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Definisikan header kolom yang dibutuhkan
        $headers = [
            'nama_lengkap', 'nama_panggilan', 'nis', 'nisnas', 'alamat',
            'nomor_telepon', 'tanggal_lahir', 'nama_orang_tua', 'tanggal_bergabung',
            'kartu_rfid_uid', 'nama_kelas', 'nama_aturan_akses'
        ];

        // Tulis header ke baris pertama
        $sheet->fromArray([$headers], NULL, 'A1');

        // --- Tambahan: Atur Format Kolom ---

        // Kolom yang harus diformat sebagai Teks
        // Berdasarkan template: nis (C), nisnas (D), nomor_telepon (F), tanggal_lahir (G), tanggal_bergabung (I), kartu_rfid_uid (J)
        // Kita paksa tanggal juga sebagai teks karena cara ini yang berhasil saat import
        $textColumns = ['C', 'D', 'F', 'G', 'I', 'J']; // Kolom C, D, F, G, I, J

        foreach ($textColumns as $col) {
            // Atur seluruh kolom untuk diformat sebagai Teks
            $sheet->getStyle($col . '1:' . $col . $sheet->getHighestRow())
                  ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        }

        // --- Akhir Tambahan ---

        // Opsional: Atur lebar kolom agar lebih rapi
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Siapkan writer untuk file XLSX
        $writer = new Xlsx($spreadsheet);

        // Siapkan nama file
        $fileName = 'template_member_' . date('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);

        // Simpan file ke lokasi sementara
        try {
            $writer->save($tempFile);
        } catch (\Exception $e) {
            \Log::error("Gagal menyimpan file template Excel sementara: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat template Excel. Error: ' . $e->getMessage());
        }

        // Unduh file
        return Response::download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Memproses import data member dari file Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:5120', // Max 5MB
        ], [
            'excel_file.required' => 'File Excel harus diunggah.',
            'excel_file.file' => 'Yang diunggah harus berupa file.',
            'excel_file.mimes' => 'Format file harus .xlsx atau .xls.',
            'excel_file.max' => 'Ukuran file tidak boleh lebih dari 5MB.',
        ]);

        $import = new MembersImport();
        $allErrorMessages = []; // Array untuk mengumpulkan semua pesan error

        try {
            Excel::import($import, $request->file('excel_file'));

            // Dapatkan error kustom yang dikumpulkan di MembersImport (dari model() method)
            $customFailures = $import->getErrors();

            if (!empty($customFailures)) {
                foreach ($customFailures as $customErrorString) {
                    $allErrorMessages[] = $customErrorString; // Langsung tambahkan string error
                }
            }

            if (!empty($allErrorMessages)) { // Jika ada error kustom atau validasi
                return redirect()->back()
                                 ->with('warning', 'Beberapa data berhasil diimpor, namun ada kegagalan pada baris tertentu:')
                                 ->withErrors($allErrorMessages); // Kirim semua pesan error
            }

            return redirect()->back()->with('success', 'Data member berhasil diimpor sepenuhnya!');

        } catch (ValidationException $e) {
            // Dapatkan error validasi dari Maatwebsite/Excel
            $excelFailures = $e->failures();
            foreach ($excelFailures as $failure) {
                $allErrorMessages[] = 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }

            // Jika ada error kustom yang juga terkumpul sebelum ValidationException
            $customFailures = $import->getErrors();
            if (!empty($customFailures)) {
                foreach ($customFailures as $customErrorString) {
                    $allErrorMessages[] = $customErrorString;
                }
            }

            return redirect()->back()
                             ->with('error', 'Gagal mengimpor data karena validasi. Periksa kesalahan berikut:')
                             ->withErrors($allErrorMessages); // Kirim semua pesan error
        } catch (\Exception $e) {
            // Tangani error umum lainnya
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }
    public function exportReport(Request $request)
    {
        // Ambil semua parameter filter dari request
        $filters = $request->only(['name', 'school_class_id', 'join_date']);

        // Pastikan Anda membersihkan cache sebelum export untuk data terbaru
        // php artisan optimize:clear; // Ini biasanya tidak diperlukan di sini

        // Gunakan MembersExport class untuk mengunduh data
        try {
            return Excel::download(new MembersExport($filters), 'laporan_member_' . date('Ymd_His') . '.xlsx');
        } catch (\Exception $e) {
            \Log::error("Gagal mengekspor laporan member: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat laporan Excel: ' . $e->getMessage());
        }
    }
}