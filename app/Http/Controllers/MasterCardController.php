<?php
namespace App\Http\Controllers;
use App\Models\MasterCard;
use Illuminate\Http\Request;

class MasterCardController extends Controller {
    public function index() {
        $cards = MasterCard::latest()->paginate(20);
        return view('master_cards.index', compact('cards'));
    }
    public function create() {
        return view('master_cards.create');
    }
    public function store(Request $request) {

        
        $request->validate([
            'card_uid' => 'required|string|unique:master_cards,card_uid',
            'card_type' => 'required|in:member,staff,coach',
        ]);
        MasterCard::create($request->all());
        return redirect()->route('master-cards.index')->with('success', 'Kartu baru berhasil ditambahkan.');
    }
    public function show(MasterCard $masterCard) {
        return view('master_cards.show', compact('masterCard'));
    }
    public function edit(MasterCard $masterCard) {
        return view('master_cards.edit', compact('masterCard'));
    }
    public function update(Request $request, MasterCard $masterCard) {
        $request->validate([
            'card_uid' => 'required|string|unique:master_cards,card_uid,' . $masterCard->id,
            'card_type' => 'required|in:member,staff,coach',
            'assignment_status' => 'required|in:available,assigned',
        ]);
        $masterCard->update($request->all());
        return redirect()->route('master-cards.index')->with('success', 'Data kartu berhasil diperbarui.');
    }
    public function destroy(MasterCard $masterCard) {
        if ($masterCard->assignment_status === 'assigned') {
            return back()->with('error', 'Tidak bisa menghapus kartu yang sedang digunakan.');
        }
        $masterCard->delete();
        return redirect()->route('master-cards.index')->with('success', 'Kartu berhasil dihapus.');
    }
}