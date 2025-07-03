{{-- resources/views/members/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Daftar Member')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
       <div class="d-flex">
            
            {{-- === TOMBOL BARU DITAMBAHKAN DI SINI === --}}
            <a href="{{ route('members.create') }}" class="btn btn-primary btn-sm shadow-sm mr-2">
                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Member Baru
            </a>
            {{-- ======================================= --}}

            {{-- Tombol Generate Laporan --}}
            <a href="{{ route('members.export.report', request()->query()) }}" class="btn btn-info btn-sm mr-2">
                <i class="fas fa-file-alt fa-sm"></i> Generate Laporan
            </a>

            {{-- Tombol Buat Template --}}
            <a href="{{ route('members.download.template') }}" class="btn btn-success btn-sm mr-2">
                <i class="fas fa-file-excel fa-sm"></i> Buat Template Excel
            </a>

            {{-- Tombol Import (Membuka Modal) --}}
            <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#importExcelModal">
                <i class="fas fa-upload fa-sm"></i> Import Data Excel
            </button>
        </div>
    </div>

    {{-- Modal untuk Import Excel --}}
    <div class="modal fade" id="importExcelModal" tabindex="-1" role="dialog" aria-labelledby="importExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importExcelModalLabel">Import Data Member dari Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('members.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="excel_file">Pilih File Excel (.xlsx, .xls)</label>
                            <input type="file" name="excel_file" id="excel_file"
                                   class="form-control-file @error('excel_file') is-invalid @enderror"
                                   accept=".xlsx, .xls" required>
                            @error('excel_file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Ukuran maksimal file: 5MB. Pastikan header kolom sesuai template.</small>
                        </div>
                        <p class="small text-muted mt-3">
                            Jika Anda belum memiliki template, silakan klik tombol "Buat Template Excel" di halaman utama.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload mr-1"></i> Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Pesan Feedback (Sukses, Warning, Error dari proses import) --}}
    {{-- Ini dipindahkan ke sini agar tetap terlihat setelah modal tertutup --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Terdapat beberapa masalah dengan file Excel Anda:<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Filter Data Member</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                    <div class="dropdown-header">Aksi:</div>
                    <a class="dropdown-item" href="{{ route('members.index') }}">Reset Filter</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('members.index') }}" method="GET" class="form-row align-items-end">
                <div class="form-group col-md-5">
                    <label for="name">Nama</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Cari Nama..." value="{{ request('name') }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="school_class_id">Kelas</label>
                    <select name="school_class_id" id="school_class_id" class="form-control">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($schoolClasses as $class)
                            <option value="{{ $class->id }}" {{ request('school_class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="join_date">Tgl Bergabung</label>
                    <input type="date" class="form-control" id="join_date" name="join_date" value="{{ request('join_date') }}">
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-info mr-2 w-100">
                        <i class="fas fa-filter fa-sm"></i> Filter
                    </button>
                    @if(request('name') || request('school_class_id') || request('join_date'))
                        <a href="{{ route('members.index') }}" class="btn btn-warning ml-2 w-100">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Daftar Member Anda --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Member</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Lengkap</th>
                         
                            <th>Kelas</th>
                            <th>Kartu RFID</th>
                            <th>Aturan Akses</th>
                            <th>Tgl Bergabung</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $index => $member)
                            <tr>
                                <td>{{ $index + $members->firstItem() }}</td>
                                <td>
                                    @if($member->photo)
                                        <a href="#" data-toggle="modal" data-target="#photoModal{{ $member->id }}">
                                            <img src="{{ asset('storage/' . $member->photo) }}" alt="Foto"
                                                class="img-thumbnail rounded-circle mr-2" width="40" height="40">
                                        </a>
                                        <div class="modal fade" id="photoModal{{ $member->id }}" tabindex="-1" role="dialog"
                                            aria-labelledby="photoModalLabel{{ $member->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-body text-center p-2">
                                                        <img src="{{ asset('storage/' . $member->photo) }}"
                                                            alt="Foto {{ $member->name }}" class="img-fluid rounded"
                                                            style="max-width: 100%; max-height: 600px;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    {{ $member->name }}
                                </td>
                                
                                <td>{{ $member->schoolClass->name ?? '-' }}</td>
                                <td><span class="badge badge-info">{{ $member->masterCard->cardno ?? 'Belum ada' }}</span></td>
                                <td>{{ $member->rule_type == 'custom' ? 'Custom' : ($member->accessRule->name ?? 'Default') }}</td>
                                <td>{{ \Carbon\Carbon::parse($member->join_date)->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('members.show', $member->id) }}" class="btn btn-info btn-sm" title="Detail"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('members.edit', $member->id) }}" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('members.destroy', $member->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus member ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" title="Hapus"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada data member</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $members->appends(request()->except('page'))->links() }}</div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Script untuk menampilkan modal jika ada error validasi setelah submit --}}
    @if ($errors->any() && old('_token')) {{-- Cek jika ada error dan form sudah pernah disubmit --}}
        <script type="text/javascript">
            $(window).on('load', function() {
                $('#importExcelModal').modal('show');
            });
        </script>
    @endif
@endpush
