<?php
namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::latest()->paginate(15);
        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        return view('classes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);
        SchoolClass::create($request->all());
        return redirect()->route('classes.index')->with('success', 'Kelas baru berhasil ditambahkan.');
    }

    public function show(SchoolClass $class)
    {
        // Di Laravel, Anda bisa type-hint model dan Laravel akan menemukannya otomatis
        // Variabel $class harus cocok dengan nama parameter di route, misal: Route::get('/classes/{class}', ...)
        return view('classes.show', compact('class'));
    }

    public function edit(SchoolClass $class)
    {
        return view('classes.edit', compact('class'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);
        $class->update($request->all());
        return redirect()->route('classes.index')->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class)
    {
        // Anda mungkin ingin menambahkan pengecekan jika kelas ini memiliki pendaftar aktif
        // sebelum menghapusnya. Untuk saat ini, kita langsung hapus.
        $class->delete();
        return redirect()->route('classes.index')->with('success', 'Kelas berhasil dihapus.');
    }
}