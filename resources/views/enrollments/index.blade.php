@extends('layouts.app')
@section('title', 'Manajemen Pendaftaran Kelas')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
    </div>

    {{-- Notifikasi Sukses atau Error --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    {{-- Card untuk Filter --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-2"></i>Filter Pendaftaran</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('enrollments.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="member_id">Filter berdasarkan Member</label>
                            <select name="member_id" id="member_id" class="form-control">
                                <option value="">Semua Member</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" {{ request('member_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="class_id">Filter berdasarkan Kelas</label>
                            <select name="class_id" id="class_id" class="form-control">
                                <option value="">Semua Kelas</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Filter berdasarkan Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <a href="{{ route('enrollments.index') }}" class="btn btn-secondary">Reset Filter</a>
            </form>
        </div>
    </div>

    {{-- Card untuk Tabel Data --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Pendaftaran</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Member</th>
                            <th>Kelas yang Diikuti</th>
                            <th>Tanggal Pendaftaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($enrollments as $index => $enrollment)
                            <tr>
                                <td>{{ $index + $enrollments->firstItem() }}</td>
                                <td>{{ $enrollment->member->name ?? 'Member Dihapus' }}</td>
                                <td>{{ $enrollment->schoolClass->name ?? 'Kelas Dihapus' }}</td>
                                <td>{{ $enrollment->enrollment_date->format('d M Y') }}</td>
                                <td>
                                    @if($enrollment->status == 'active')
                                        <span class="badge badge-success">Aktif</span>
                                    @elseif($enrollment->status == 'completed')
                                        <span class="badge badge-secondary">Selesai</span>
                                    @else
                                        <span class="badge badge-danger">Dibatalkan</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-info dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Ubah Status
                                        </button>
                                        <div class="dropdown-menu">
                                            <form action="{{ route('enrollments.update', $enrollment->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="dropdown-item">Jadikan Aktif</button>
                                            </form>
                                            <form action="{{ route('enrollments.update', $enrollment->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="dropdown-item">Jadikan Selesai</button>
                                            </form>
                                            <form action="{{ route('enrollments.update', $enrollment->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="dropdown-item">Jadikan Batal</button>
                                            </form>
                                        </div>
                                    </div>
                                    <form action="{{ route('enrollments.destroy', $enrollment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus pendaftaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" title="Hapus"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">Tidak ada data pendaftaran yang ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{-- Tampilkan link paginasi --}}
                {{ $enrollments->links() }}
            </div>
        </div>
    </div>
@endsection
