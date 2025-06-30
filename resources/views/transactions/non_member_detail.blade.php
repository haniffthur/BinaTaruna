@extends('layouts.app')
@section('title', 'Detail Transaksi Non-Member')
@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Transaksi Non-Member #{{ $transaction->id }}</h1>
        <div class="d-flex">
            <a href="{{ route('transactions.index', ['type' => 'non-member']) }}" class="btn btn-secondary btn-sm mr-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Riwayat
            </a>
            {{-- Tombol untuk cetak struk --}}
            <a href="{{ route('non-member-receipt.show', $transaction->id) }}" class="btn btn-primary btn-sm" target="_blank">
                <i class="fas fa-print"></i> Cetak Struk
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informasi Transaksi</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>ID Transaksi:</strong> #{{ $transaction->id }}</p>
                    <p><strong>Nama Pelanggan:</strong> {{ $transaction->customer_name }}</p>
                    <p><strong>Total Bayar:</strong> Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Tanggal Transaksi:</strong> {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d F Y, H:i') }}</p>
                    <p><strong>Jumlah Dibayar:</strong> Rp {{ number_format($transaction->amount_paid, 0, ',', '.') }}</p>
                    <p><strong>Kembalian:</strong> Rp {{ number_format($transaction->change, 0, ',', '.') }}</p>
                    <p><strong>Status Pembayaran:</strong> <span class="badge badge-success">Lunas</span></p>
                </div>
            </div>
            <hr class="my-4"> {{-- Tambahkan garis pemisah --}}
            @if($transaction->purchasedTickets->count() > 0)
                <h5 class="mt-4">Detail Tiket yang Dibeli:</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Nama Tiket</th>
                                <th>Harga Satuan</th>
                                <th>QR Token</th>
                                <th>QR Code</th> {{-- Kolom baru untuk QR Code --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaction->purchasedTickets as $index => $ticket)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    {{-- Pastikan ticketProduct ada dan memiliki properti name dan price --}}
                                    <td>{{ $ticket->ticketProduct->name ?? 'N/A' }}</td>
                                    <td>Rp {{ number_format($ticket->ticketProduct->price ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ $ticket->qrcode }}</td> {{-- Menampilkan QR Token --}}
                                    <td class="text-center">
                                        @if(isset($qrcodes[$index]))
                        {!! $qrcodes[$index] !!}
                        <p class="small text-muted mt-1" style="word-break: break-all;">{{ $ticket->qrcode }}</p>
                    @else
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total Tiket:</th>
                                <th colspan="2">{{ $transaction->purchasedTickets->count() }} tiket</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <p class="mt-4">Tidak ada detail tiket untuk transaksi ini.</p>
            @endif
        </div>
    </div>
@endsection