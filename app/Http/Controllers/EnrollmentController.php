<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Member;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Menampilkan daftar semua pendaftaran dengan filter.
     */
    public function index(Request $request)
    {
        // Mulai query dengan eager loading untuk efisiensi
        $query = Enrollment::with('member', 'schoolClass')->latest();

        // Terapkan filter jika ada input dari request
        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $enrollments = $query->paginate(20)->withQueryString();

        // Ambil data untuk dropdown filter di view
        $members = Member::orderBy('name')->get();
        $classes = SchoolClass::orderBy('name')->get();

        return view('enrollments.index', compact('enrollments', 'members', 'classes'));
    }

    /**
     * Mengupdate status pendaftaran (misal: dari active ke cancelled).
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'status' => 'required|in:active,completed,cancelled',
        ]);

        $enrollment->update(['status' => $request->status]);

        return back()->with('success', 'Status pendaftaran berhasil diperbarui.');
    }

    /**
     * Menghapus atau membatalkan pendaftaran member dari kelas.
     */
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();

        return back()->with('success', 'Pendaftaran berhasil dihapus.');
    }
}