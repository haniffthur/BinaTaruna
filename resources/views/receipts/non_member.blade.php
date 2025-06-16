@extends('layouts.app')
@section('title', 'Cetak Struk Transaksi')

@push('styles')
<style>
    /* Styling dasar untuk tampilan di layar */
    .receipt-card {
        max-width: 450px; /* Lebar struk yang nyaman dilihat di layar */
        margin: auto;
        font-family: 'Courier New', Courier, monospace; /* Font yang mirip mesin kasir */
        font-size: 14px;
        color: #000;
    }
    .receipt-card hr {
        border-top: 1px dashed #000;
    }
    .receipt-card .table {
        margin-bottom: 0;
    }
    .receipt-card .table td {
        border-top: none;
        padding: 2px 0;
    }

    /* === ATURAN KHUSUS SAAT HALAMAN DICETAK (PRINT) === */
    @media print {
        /* Sembunyikan semua elemen yang tidak perlu dicetak */
        body #accordionSidebar, 
        body #content-wrapper .navbar, 
        body #content-wrapper .sticky-footer,
        .card-footer, .no-print {
            display: none !important;
        }

        /* Atur ukuran kertas cetak. Sesuaikan '80mm' jika printer Anda 58mm */
        @page {
            size: 80mm auto; 
            margin: 0;
        }
        body {
            margin: 0;
            padding: 5mm; /* Beri sedikit margin pada kertas */
            background-color: #fff;
        }
        
        /* Pastikan konten utama mengisi seluruh halaman cetak */
        body #content-wrapper, .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Hapus shadow dan border pada card struk saat dicetak */
        .receipt-card {
            box-shadow: none !important;
            border: none !important;
            max-width: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
        }
    }
</style>
@endpush

@section('content')
{{-- Tombol Kembali yang tidak ikut tercetak --}}
<div class="d-sm-flex align-items-center justify-content-between mb-4 no-print">
    <h1 class="h3 mb-0 text-gray-800">Cetak Struk</h1>
    <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Riwayat
    </a>
</div>

<div class="card shadow mb-4 receipt-card">
    <div class="card-body text-center" id="receipt-content">
        <h4 class="font-weight-bold">Bina Taruna</h4>
        <p class="mb-0">Struk Pembelian Tiket</p>
        <p class="small text-muted">ID Transaksi: #{{ $transaction->id }}</p>
        <hr>
        <div class="text-left">
            <p class="mb-1"><strong>Pelanggan:</strong> {{ $transaction->customer_name ?? 'Tamu' }}</p>
            <p class="mb-1"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i') }}</p>
        </div>
        <hr>

        <table class="table table-sm">
            <thead>
                <tr>
                    <td class="text-left"><strong>Item</strong></td>
                    <td class="text-center"><strong>Jml</strong></td>
                    <td class="text-right"><strong>Total</strong></td>
                </tr>
            </thead>
            <tbody>
                 @if($transaction->details && $transaction->details->isNotEmpty())
                    @foreach($transaction->details as $detail)
                    <tr>
                        <td class="text-left">{{ $detail->purchasable->name ?? 'Tiket' }}</td>
                        <td class="text-center">{{ $detail->quantity }}</td>
                        <td class="text-right">Rp{{ number_format($detail->price * $detail->quantity) }}</td>
                    </tr>
                    @endforeach
                @else
                    <tr><td colspan="3" class="text-center"><em>Tidak ada detail item.</em></td></tr>
                @endif
            </tbody>
        </table>

        <hr>
        <div class="text-right">
            <p class="mb-1"><strong>Total:</strong> Rp{{ number_format($transaction->total_amount) }}</p>
            <p class="mb-1"><strong>Bayar:</strong> Rp{{ number_format($transaction->amount_paid) }}</p>
            <p class="font-weight-bold"><strong>Kembali:</strong> Rp{{ number_format($transaction->change) }}</p>
        </div>
        
        <hr>
        <p class="font-weight-bold">TIKET MASUK</p>
        
        <div class="my-3">
            @if(isset($qr))
                {!! $qr !!}
                <p class="small text-muted mt-1" style="word-break: break-all;">{{ $transaction->qr_code_token }}</p>
            @else
                <p class="text-danger">QR Code tidak tersedia.</p>
            @endif
        </div>

        <hr>
        <p class="text-muted small">Terima kasih atas kunjungan Anda!</p>
    </div>
    <div class="card-footer bg-white text-center no-print">
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Cetak Ulang Struk</button>
        <a href="{{ route('transactions.non-member.create') }}" class="btn btn-secondary">Transaksi Baru</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Secara otomatis memicu dialog cetak saat halaman selesai dimuat.
    window.onload = function() {
        window.print();
    }
</script>
@endpush
