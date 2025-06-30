@extends('layouts.app')
@section('title', 'Manajemen Staff')
@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('staffs.create') }}" class="btn btn-primary btn-sm shadow-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Tambah Staff Baru</a>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th><th>Nama</th><th>Posisi</th></th><th>Kartu RFID</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffs as $index => $staff)
                            <tr>
                                <td>{{ $index + $staffs->firstItem() }}</td>
                                <td>{{ $staff->name }}</td>
                                <td>{{ $staff->position }}</td>
                                <td><span class="badge badge-info">{{ $staff->masterCard->cardno ?? 'Belum ada' }}</span></td>
                                <td>
                                    <a href="{{ route('staffs.edit', $staff->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('staffs.destroy', $staff->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus staff ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">Tidak ada data staff</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $staffs->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
@endsection