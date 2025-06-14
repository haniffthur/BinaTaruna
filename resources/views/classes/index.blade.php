@extends('layouts.app')
@section('title', 'Manajemen Kelas')
@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('classes.create') }}" class="btn btn-primary btn-sm shadow-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Tambah Kelas Baru</a>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            {{-- ... notifikasi success/error ... --}}
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr><th>#</th><th>Nama Kelas</th><th>Harga</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse($classes as $index => $class)
                            <tr>
                                <td>{{ $index + $classes->firstItem() }}</td>
                                <td>{{ $class->name }}</td>
                                <td>Rp {{ number_format($class->price, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('classes.edit', $class->id) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    {{-- ... form delete ... --}}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">Tidak ada data kelas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $classes->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
@endsection