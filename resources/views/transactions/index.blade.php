@extends('layouts.app')
@section('title', 'Riwayat Seluruh Transaksi')
@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Transaksi</h6>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ request('type', 'all') == 'all' ? 'active' : '' }}" id="all-tab" href="{{ route('transactions.index', ['type' => 'all']) }}" role="tab" aria-controls="all" aria-selected="{{ request('type', 'all') == 'all' ? 'true' : 'false' }}">Semua Transaksi</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ request('type') == 'member' ? 'active' : '' }}" id="member-tab" href="{{ route('transactions.index', ['type' => 'member']) }}" role="tab" aria-controls="member" aria-selected="{{ request('type') == 'member' ? 'true' : 'false' }}">Transaksi Member</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ request('type') == 'non-member' ? 'active' : '' }}" id="non-member-tab" href="{{ route('transactions.index', ['type' => 'non-member']) }}" role="tab" aria-controls="non-member" aria-selected="{{ request('type') == 'non-member' ? 'true' : 'false' }}">Transaksi Non-Member</a>
                </li>
            </ul>
            <div class="tab-content mt-3" id="myTabContent">
                <div class="tab-pane fade show active" role="tabpanel" aria-labelledby="current-tab">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>ID Transaksi</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Tipe Transaksi</th>
                                    <th>Total Bayar</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $index => $transaction)
                                    <tr>
                                        <td>{{ $index + $transactions->firstItem() }}</td>
                                        <td>#{{ $transaction->id }}</td>
                                        <td>{{ $transaction->customer_name ?? 'Tamu' }}</td>
                                        <td>
                                            @if($transaction->transaction_type == 'Member')
                                                <span class="badge badge-success">Member</span>
                                            @else
                                                <span class="badge badge-warning">Non-Member</span>
                                            @endif
                                        </td>
                                        <td>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y, H:i') }}</td>
                                        <td>
                                            @if($transaction->transaction_type == 'Non-Member')
                                                {{-- PERBAIKAN PENTING DI SINI: Arahkan ke route 'non-member-receipt.show' --}}
                                               <a href="{{ route('non-member-receipt.show', $transaction->id) }}" class="btn btn-info btn-sm">Struk</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada riwayat transaksi.</td> </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $transactions->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection