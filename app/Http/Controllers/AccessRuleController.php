<?php
namespace App\Http\Controllers;
use App\Models\AccessRule;
use Illuminate\Http\Request;

class AccessRuleController extends Controller {
    public function index() {
        $rules = AccessRule::latest()->paginate(15);
        return view('access_rules.index', compact('rules'));
    }
    public function create() {
        return view('access_rules.create');
    }
    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_taps_per_day' => 'nullable|integer|min:0',
            'max_taps_per_month' => 'nullable|integer|min:0',
            'allowed_days' => 'nullable|array',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
        ]);
        AccessRule::create($validated);
        return redirect()->route('access-rules.index')->with('success', 'Aturan akses berhasil dibuat.');
    }
    public function show(AccessRule $accessRule) {
        return view('access_rules.show', compact('accessRule'));
    }
    public function edit(AccessRule $accessRule) {
        return view('access_rules.edit', compact('accessRule'));
    }
    public function update(Request $request, AccessRule $accessRule) {
         $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_taps_per_day' => 'nullable|integer|min:0',
            'max_taps_per_month' => 'nullable|integer|min:0',
            'allowed_days' => 'nullable|array',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
        ]);
        $accessRule->update($validated);
        return redirect()->route('access-rules.index')->with('success', 'Aturan akses berhasil diperbarui.');
    }
    public function destroy(AccessRule $accessRule) {
        $accessRule->delete();
        return redirect()->route('access-rules.index')->with('success', 'Aturan akses berhasil dihapus.');
    }
}