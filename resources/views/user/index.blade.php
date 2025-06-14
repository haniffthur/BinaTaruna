@extends('layouts.app')

@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Data User</h1>
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah User
        </a>
    </div>

    <!-- User Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Nik</th>
                            <th>No. Telepon</th>
                            <th>Jenis Kelamin</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                                            <tr>
                                                <td>{{ $index + $users->firstItem() }}</td>

                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->nik }}</td>
                                                <td>{{ $user->no_telp }}</td>
                                                <td>{{ ucfirst($user->jenis_kelamin) }}</td>
                                                <td>{{ ucfirst($user->role) }}</td>
                                                <td>
                                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">
                                                        Edit
                                                    </a>
                                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline"
                                                        onsubmit="return confirm('Yakin ingin hapus user ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-danger btn-sm">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                    <a href="{{ route('users.show', $user->id) }}"
                                        class="btn btn-info btn-sm">Detail</a>



                                                    







                                                </td>



                                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted fst-italic py-3">Tidak ada data user</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>

    </div>

@endsection