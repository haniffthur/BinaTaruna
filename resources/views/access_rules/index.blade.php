@extends('layouts.app')

@section('title', 'Manajemen Aturan Akses')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('access-rules.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Aturan Baru
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Aturan</th>
                            <th>Limit Tap (Harian/Bulanan)</th>
                            <th>Jadwal Akses</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rules as $index => $rule)
                            <tr>
                                <td>{{ $index + $rules->firstItem() }}</td>
                                <td>
                                    <span class="font-weight-bold">{{ $rule->name }}</span><br>
                                    <small class="text-muted">{{ $rule->description ?? 'Tidak ada deskripsi' }}</small>
                                </td>
                                <td>
                                    Harian: <span class="badge badge-info">{{ $rule->max_taps_per_day ?? '∞' }}</span><br>
                                    Bulanan: <span class="badge badge-primary">{{ $rule->max_taps_per_month ?? '∞' }}</span>
                                </td>
                                <td>
                                    @if($rule->allowed_days)
                                        @foreach($rule->allowed_days as $day)
                                            <span class="badge badge-secondary text-capitalize">{{ substr($day, 0, 3) }}</span>
                                        @endforeach
                                    @else
                                        <span class="badge badge-success">Setiap Hari</span>
                                    @endif
                                    <br>
                                    <small class="text-muted">
                                        {{ $rule->start_time ? date('H:i', strtotime($rule->start_time)) : '00:00' }} - 
                                        {{ $rule->end_time ? date('H:i', strtotime($rule->end_time)) : '23:59' }}
                                    </small>
                                </td>
                                <td>
                                    <a href="{{ route('access-rules.edit', $rule->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('access-rules.destroy', $rule->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin ingin hapus aturan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Tidak ada aturan akses yang dibuat</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $rules->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection