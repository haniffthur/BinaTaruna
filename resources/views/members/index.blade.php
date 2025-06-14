@extends('layouts.app')
@section('title', 'Manajemen Member')
@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('members.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Member Baru
        </a>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Kelas</th> <!-- Kolom Baru -->
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
                                        <!-- Thumbnail clickable -->
                                        <a href="#" data-toggle="modal" data-target="#photoModal{{ $member->id }}">
                                            <img src="{{ asset('storage/' . $member->photo) }}" alt="Foto"
                                                class="img-thumbnail rounded-circle mr-2" width="40" height="40">
                                        </a>

                                        <!-- Modal ukuran kecil -->
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
                                <td>{{ $member->schoolClass->name ?? '-' }}</td> <!-- Menampilkan Nama Kelas -->
                                <td><span class="badge badge-info">{{ $member->masterCard->card_uid ?? 'Belum ada' }}</span>
                                </td>
                                <td>{{ $member->rule_type == 'custom' ? 'Custom' : ($member->accessRule->name ?? 'Default') }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($member->join_date)->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('members.show', $member->id) }}" class="btn btn-info btn-sm"
                                        title="Detail"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('members.edit', $member->id) }}" class="btn btn-warning btn-sm"
                                        title="Edit"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('members.destroy', $member->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Yakin ingin hapus member ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" title="Hapus"><i
                                                class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data member</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $members->links() }}</div>
        </div>
    </div>
@endsection