@extends('layouts.app')

@section('title', 'Manajemen Kartu RFID')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('master-cards.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Kartu Baru
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
             @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
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
                            <th>UID Kartu</th>
                            <th>Tipe Kartu</th>
                            <th>Status</th>
                            <th>Dibuat Pada</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cards as $index => $card)
                            <tr>
                                <td>{{ $index + $cards->firstItem() }}</td>
                                <td><span class="font-weight-bold">{{ $card->card_uid }}</span></td>
                                <td>
                                    @if($card->card_type == 'member')
                                        <span class="badge badge-primary">Member</span>
                                    @elseif($card->card_type == 'coach')
                                        <span class="badge badge-info">Pelatih</span>
                                    @else
                                        <span class="badge badge-dark">Staff</span>
                                    @endif
                                </td>
                                <td>
                                    @if($card->assignment_status == 'available')
                                        <span class="badge badge-success">Tersedia</span>
                                    @else
                                        <span class="badge badge-warning">Digunakan</span>
                                    @endif
                                </td>
                                <td>{{ $card->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('master-cards.edit', $card->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('master-cards.destroy', $card->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin ingin hapus kartu ini? Jika kartu sedang digunakan, pemiliknya akan kehilangan akses kartu.')">
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
                                <td colspan="6" class="text-center text-muted py-3">Tidak ada data kartu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $cards->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection