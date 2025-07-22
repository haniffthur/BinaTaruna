<?php

namespace App\Http\Controllers;

use App\Models\MasterCard;
use Illuminate\Http\Request;

class MasterCardController extends Controller
{
     public function index(Request $request)
    {
        $cardType = $request->input('card_type');
        $assignmentStatus = $request->input('assignment_status');
        $search = $request->input('search');

        $query = MasterCard::latest();

        if ($cardType) {
            $query->where('card_type', $cardType);
        }

        if ($assignmentStatus) {
            $query->where('assignment_status', $assignmentStatus);
        }

        if ($search) {
            $query->where('cardno', 'like', '%' . $search . '%');
        }

        $cards = $query->paginate(20)->withQueryString();

        $totalCards = MasterCard::count();
        $availableCards = MasterCard::where('assignment_status', 'available')->count();
        $assignedCards = MasterCard::where('assignment_status', 'assigned')->count();

        // Cek apakah permintaan berasal dari AJAX
        if ($request->ajax()) {
            // Jika ini permintaan AJAX, kembalikan data dalam format JSON
            return response()->json([
                'data' => $cards->items(), // Data kartu untuk halaman saat ini
                'links' => (string) $cards->links('pagination::bootstrap-5'), // HTML paginasi sebagai string
                'total_cards_count' => $cards->total(), // Total kartu dari query filter (jika diperlukan)
                // Anda juga bisa mengirimkan totalCards, availableCards, assignedCards jika ingin mengupdate summary cards via AJAX
                'summary' => [
                    'total' => $totalCards,
                    'available' => $availableCards,
                    'assigned' => $assignedCards,
                ]
            ]);
        }

        // Jika bukan permintaan AJAX, kembalikan tampilan lengkap
        return view('master_cards.index', compact('cards', 'totalCards', 'availableCards', 'assignedCards', 'cardType', 'assignmentStatus', 'search'));
    }

    public function create()
    {
        return view('master_cards.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'cardno' => 'required|string|unique:master_cards,cardno',
            'card_type' => 'required|in:member,staff,coach',
        ]);

        MasterCard::create($request->all());

        return redirect()->route('master-cards.index')->with('success', 'Kartu baru berhasil ditambahkan.');
    }

    public function show(MasterCard $masterCard)
    {
        return view('master_cards.show', compact('masterCard'));
    }

    public function edit(MasterCard $masterCard)
    {
        return view('master_cards.edit', compact('masterCard'));
    }

   public function update(Request $request, MasterCard $masterCard) {
        $request->validate([
            'cardno' => 'required|string|unique:master_cards,cardno,' . $masterCard->id,
            'card_type' => 'required|in:member,staff,coach',
            'assignment_status' => 'required|in:available,assigned',
        ]);
        $masterCard->update($request->all());

        // Mengembalikan JSON untuk pesan sukses/error jika request AJAX
        if ($request->ajax()) {
            return response()->json(['success' => 'Data kartu berhasil diperbarui.']);
        }

        return redirect()->route('master-cards.index')->with('success', 'Data kartu berhasil diperbarui.');
    }

    public function destroy(MasterCard $masterCard, Request $request) { // Tambahkan Request
        if ($masterCard->assignment_status === 'assigned') {
            if ($request->ajax()) {
                return response()->json(['error' => 'Tidak bisa menghapus kartu yang sedang digunakan.'], 400); // 400 Bad Request
            }
            return back()->with('error', 'Tidak bisa menghapus kartu yang sedang digunakan.');
        }

        $masterCard->delete();

        if ($request->ajax()) {
            return response()->json(['success' => 'Kartu berhasil dihapus.']);
        }
        return redirect()->route('master-cards.index')->with('success', 'Kartu berhasil dihapus.');
    }
}