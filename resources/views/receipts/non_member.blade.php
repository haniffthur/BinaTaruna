@extends('layouts.app')
@section('title', 'Cetak Struk Transaksi')

@push('styles')
<style>
    /* Gaya untuk tampilan di browser (tidak diubah) */
    .receipt-card {
        width: 100%;
        max-width: 450px;
        margin: auto;
        font-family: 'Courier New', Courier, monospace;
        font-size: 14px;
        color: #000;
        margin-bottom: 1rem;
        border: 1px solid #eee;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .receipt-card hr { border-top: 1px dashed #000; }
    .receipt-card .table { margin-bottom: 0; }
    .receipt-card .table td { border-top: none; padding: 2px 0; }
    .receipt-card svg { width: 180px; height: 180px; display: block; margin: 0 auto; }
    .receipt-footer { padding: 0.5rem; border-top: 1px solid #eee; }

    /* ========================================================= */
    /* PENTING: GAYA UNTUK PRINTING                              */
    /* ========================================================= */
    @media print {
        @page {
            size: 80mm auto;
            margin: 0;
        }

        body, html {
            margin: 0 !important;
            padding: 0 !important;
            width: 80mm;
            background: #fff;
        }

        body * {
            visibility: hidden;
        }
        
        #print-area, #print-area * {
            visibility: visible;
        }
        
        #print-area {
            display: block !important;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        .receipt-card {
            width: 118mm; /* PENTING: Ukuran ini harus dipertahankan agar tidak terpotong */
            margin: 0 auto !important;
            padding: 0 !important;
            box-shadow: none !important;
            border: none !important;
            color: #000 !important;
            font-family: 'Courier New', Courier, monospace !important;
        }

        .card-body {
            padding: 0 5mm 2mm 5mm !important;
            text-align: center;
        }
        
        /* DIKEMBALIKAN: Ukuran font sesuai permintaan Anda */
        h5 { 
            font-size: 20pt !important; 
            font-weight: bold; 
            margin-bottom: 2mm; 
            margin-top:-2 !important;
        }

        .receipt-card p,
        .receipt-card small,
        .receipt-card strong,
        .receipt-card table td,
        .receipt-card table th {
            font-size: 15pt !important;
            line-height: 1.3;
            color: #000 !important;
        }

        /* Ini untuk ID Transaksi dan Tanggal */
        .receipt-card .small {
             font-size: 15pt !important;
             font-weight:bold;
        }

        .receipt-card strong {
             font-weight: bold !important;
        }
        /* Akhir dari penyesuaian font */

        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        
        .receipt-card hr {
            border-top: 1px dashed #000 !important;
            margin: 2mm 0 !important;
        }

        /* DIKEMBALIKAN: Ukuran QR Code sesuai permintaan Anda */
        .receipt-card .qr-code-container {
            margin: 2mm 0 !important;
            text-align: center;
        }
        .receipt-card .qr-code-container svg {
            width: 50mm !important;
            height: 50mm !important;
            display: block;
            margin: 0 auto !important;
        }
        .receipt-card .qr-code-container p {
            font-size: 8pt !important; /* Token QR bisa sedikit lebih kecil */
            word-break: break-all;
            margin-top: 1mm;
        }
        
        .no-print, .main-header, #accordionSidebar, .navbar, .sticky-footer, .card-footer, .btn, .d-sm-flex {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')

{{-- Tidak ada perubahan di bagian HTML & Blade --}}
<div class="d-sm-flex align-items-center justify-content-between mb-4 no-print">
    <h1 class="h3 mb-0 text-gray-800">Cetak Struk</h1>
    <div class="d-flex">
        <a href="{{ route('transactions.non-member.create') }}" class="btn btn-info btn-sm shadow-sm mr-2">
            <i class="fas fa-plus fa-sm text-white-50"></i> Transaksi Baru
        </a>
        <button onclick="printAllTickets()" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-print fa-sm text-white-50"></i> Cetak Semua Struk
        </button>
    </div>
</div>

<div id="tickets-wrapper">
@if(isset($tickets) && is_iterable($tickets) && count($tickets) > 0)
    @foreach($tickets as $index => $ticket)
        <div class="card shadow receipt-card" id="ticket-{{ $index }}">
            <div class="card-body text-center">
                <h5 class="font-weight-bold mb-0">Bina Taruna</h5>
                <small class="font-weight-bold">Struk Pembelian Tiket</small>
                {{-- Penambahan class "small" agar bisa ditarget CSS --}}
                <p class="small mb-1 font-weight-bold">ID Transaksi: {{ $transaction->id }} | Tiket {{ $index + 1 }} dari {{ count($tickets) }}</p>
                <hr>
                <div class="text-left">
                    <p class="mb-1"><strong>Pelanggan:</strong> {{ $transaction->customer_name ?? 'Tamu' }}</p>
                    <p class="mb-1"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y H:i') }}</p>
                </div>
                <hr>

                <table class="table table-sm">
                    <tr>
                        <td class="text-left"><strong>Item</strong></td>
                        <td class="text-right"><strong>Harga</strong></td>
                    </tr>
                    <tr>
                        <td class="text-left"><strong>{{ $ticket->ticketProduct->name ?? 'Tiket' }}</strong></td>
                        <td class="text-right"><strong>Rp{{ number_format($ticket->ticketProduct->price ?? 0) }}</strong></td>
                    </tr>
                </table>
                <hr>

<div class="text-right">
    <p class="mb-1"><strong>Total: Rp{{ number_format($transaction->total_amount) }} </strong> </p>
    <p class="mb-1"><strong>Bayar: Rp{{ number_format($transaction->amount_paid) }} </strong> </p>
    <p class="font-weight-bold"><strong>Kembali:</strong> Rp{{ number_format($transaction->change) }}</p>
</div>
<hr>

                <p class="font-weight-bold mb-0">TIKET MASUK #{{ $index + 1 }}</p>

                <div class="qr-code-container">
                    @if(isset($qrcodes[$index]))
                        {!! $qrcodes[$index] !!}
                        <p class="small text-muted mt-1">{{ $ticket->qr_code_token }}</p>
                    @else
                        <p class="text-danger">QR Code tidak tersedia.</p>
                    @endif
                </div>

                <hr>
                <p class="font-weight-bold">Terima kasih atas kunjungan Anda!</p>
            </div>
            <div class="card-footer text-center no-print">
                 <button class="btn btn-sm btn-outline-secondary" onclick="printSingleTicket({{ $index }})">
                    <i class="fas fa-print"></i> Cetak Tiket #{{ $index + 1 }}
                </button>
            </div>
        </div>
    @endforeach
@else
    <div class="alert alert-warning">Tidak ada tiket untuk ditampilkan.</div>
@endif
</div>

<div id="print-area" style="display:none;"></div>

@endsection

@push('scripts')
<script>
    function printSingleTicket(index) {
        const ticketContent = document.getElementById('ticket-' + index).cloneNode(true);
        const footer = ticketContent.querySelector('.card-footer');
        if (footer) {
            footer.remove();
        }
        const printArea = document.getElementById('print-area');
        printArea.innerHTML = '';
        printArea.appendChild(ticketContent);
        window.print();
    }

    function printAllTickets() {
        const tickets = document.querySelectorAll('#tickets-wrapper .receipt-card');
        if (tickets.length === 0) return;

        let currentTicketIndex = 0;

        function printNext() {
            if (currentTicketIndex < tickets.length) {
                const ticketId = tickets[currentTicketIndex].id;
                const index = ticketId.split('-')[1];
                printSingleTicket(index);
                currentTicketIndex++;
                setTimeout(printNext, 1000); // jeda agar print tidak bentrok
            }
        }

        printNext();
    }

    // ✅ Cetak semua tiket otomatis saat halaman selesai dimuat
    window.addEventListener('DOMContentLoaded', () => {
        printAllTickets();
    });
</script>
@endpush
