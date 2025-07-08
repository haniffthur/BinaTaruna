<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(10);
        return view('user.index', compact('users'));
    }

    public function create()
    {
        return view('user.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'name' => 'required|string|max:100',
           'nik' => 'required|digits:16',
            'alamat' => 'required|string|max:500',
            'no_telp' => 'required|string|max:50',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
            'role' => 'required|in:admin,petugas',
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'username' => $request->username,
            'nik' => $request->nik,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
            'jenis_kelamin' => $request->jenis_kelamin,
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('user.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'name' => 'required|string|max:100',
            'nik' => 'required|nik|max:100',
            'alamat' => 'required|string|max:500',
            'no_telp' => 'required|string|max:50',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
            'role' => 'required|in:admin,petugas',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user->username = $request->username;
        $user->nik = $request->nik;
        $user->name = $request->name;
        $user->alamat = $request->alamat;
        $user->no_telp = $request->no_telp;
        $user->jenis_kelamin = $request->jenis_kelamin;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User berhasil diupdate');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }

    public function show($id)
{
    $user = User::findOrFail($id);
    return view('user.detail', compact('user'));
}
}
