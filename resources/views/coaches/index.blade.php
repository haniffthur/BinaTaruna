@extends('layouts.app')
@section('title', 'Manajemen Pelatih')
@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('coaches.create') }}" class="btn btn-primary btn-sm shadow-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Tambah Pelatih Baru</a>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th><th>Nama</th><th>Spesialisasi</th><th>Kartu RFID</th><th>Aturan Akses</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coaches as $index => $coach)
                            <tr>
                                <td>{{ $index + $coaches->firstItem() }}</td>
                                <td>{{ $coach->name }}</td>
                                <td>{{ $coach->specialization ?? '-' }}</td>
                                <td><span class="badge badge-info">{{ $coach->masterCard->cardno ?? 'Belum ada' }}</span></td>
                                <td>{{ $coach->accessRule->name ?? 'Default' }}</td>
                                <td>
                                    <a href="{{ route('coaches.show', $coach->id) }}" class="btn btn-info btn-sm" title="Detail"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('coaches.edit', $coach->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('coaches.destroy', $coach->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus pelatih ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">Tidak ada data pelatih</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $coaches->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
@endsection