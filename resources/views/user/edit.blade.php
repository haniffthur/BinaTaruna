@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit User</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group mb-3">
                <label for="username">Username</label>
                <input id="username" type="text" name="username" class="form-control"
                    value="{{ old('username', $user->username) }}" required>
            </div>
            <div class="form-group mb-3">
                <label for="nik">Nik</label>
                <input id="nik" type="text" name="nik" class="form-control"
                    value="{{ old('nik', $user->nik) }}" required>
            </div>
            <div class="form-group mb-3">
                <label for="password">Password <small class="text-muted">(kosongkan jika tidak ingin diubah)</small></label>
                <input id="password" type="password" name="password" class="form-control" autocomplete="new-password"
                    placeholder="Isi password baru jika ingin mengganti">
            </div>  

            <div class="form-group mb-3">
                <label for="name">Nama</label>
                <input id="name" type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}"
                    required>
            </div>

            <div class="form-group mb-3">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" class="form-control" rows="3"
                    required>{{ old('alamat', $user->alamat) }}</textarea>
            </div>

            <div class="form-group mb-3">
                <label for="no_telp">No. Telepon</label>
                <input id="no_telp" type="text" name="no_telp" class="form-control"
                    value="{{ old('no_telp', $user->no_telp) }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="jenis_kelamin">Jenis Kelamin</label>
                <select id="jenis_kelamin" name="jenis_kelamin" class="form-control" required>
                    <option value="laki-laki" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="perempuan" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="role">Role</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="petugas" {{ old('role', $user->role) == 'petugas' ? 'selected' : '' }}>Petugas</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary ms-2">Batal</a>
        </form>
    </div>
@endsection