@extends('layouts.app')
@section('title', 'Riwayat Seluruh Transaksi')
@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Transaksi Member & Non-Member</h6>
        </div>
        <div class="card-body">
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada riwayat transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $transactions->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection