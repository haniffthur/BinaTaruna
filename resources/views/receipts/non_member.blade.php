@extends('layouts.app')
@section('title', 'Struk Transaksi')

@push('styles')
<style>
    @media print {
        /* Sembunyikan semua elemen yang tidak perlu dicetak */
        body #accordionSidebar, 
        body #content-wrapper .navbar, 
        body #content-wrapper .sticky-footer,
        .card-footer {
            display: none !important;
        }

        /* Pastikan konten utama mengisi seluruh halaman cetak */
        body #content-wrapper {
            margin: 0;
            padding: 0;
        }

        /* Hapus shadow dan border pada card struk saat dicetak */
        .card {
            box-shadow: none !important;
            border: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="card shadow mb-4" style="max-width: 450px; margin: auto;">
    <div class="card-body text-center" id="receipt-content">
        <h4 class="font-weight-bold">Bina Taruna</h4>
        <p class="mb-0">Struk Pembelian Tiket</p>
        <p class="small text-muted">ID Transaksi: #{{ $transaction->id }}</p>
        <hr>
        <div class="text-left">
            <p class="mb-1"><strong>Nama Pelanggan:</strong> {{ $transaction->customer_name ?? 'Tamu' }}</p>
            <p class="mb-1"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y, H:i') }}</p>
        </div>
        <hr>

        <table class="table table-sm">
            <tr>
                <td class="text-left"><strong>Item</strong></td>
                <td class="text-right"><strong>Total</strong></td>
            </tr>
            @foreach($transaction->details as $detail)
            <tr>
                <td class="text-left">{{ $detail->purchasable->name }}</td>
                <td class="text-right">Rp {{ number_format($detail->price) }}</td>
            </tr>
            @endforeach
        </table>

        <hr>
        <div class="text-right">
            <p class="mb-1"><strong>Total:</strong> Rp {{ number_format($transaction->total_amount) }}</p>
            <p class="mb-1"><strong>Bayar:</strong> Rp {{ number_format($transaction->amount_paid) }}</p>
            <p class="font-weight-bold"><strong>Kembali:</strong> Rp {{ number_format($transaction->change) }}</p>
        </div>
        <div class="my-4">
              {!! $qr !!}
            <p class="small text-muted mt-1">{{ $transaction->qr_code_token }}</p>
        </div>
        <p class="font-weight-bold">GUNAKAN QR CODE INI UNTUK MASUK</p>
        <hr>
        <p class="text-muted small">Terima kasih atas kunjungan Anda!</p>
    </div>
    <div class="card-footer bg-white text-center">
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Cetak Struk</button>
        <a href="{{ route('transactions.non-member.create') }}" class="btn btn-secondary">Transaksi Baru</a>
    </div>
</div>
@endsection