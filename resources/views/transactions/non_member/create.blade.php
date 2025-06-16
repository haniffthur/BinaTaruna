@extends('layouts.app')
@section('title', 'Transaksi Tiket Non-Member')
@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Form Transaksi Non-Member</h6></div>
        <div class="card-body">
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if ($errors->any())<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

            <form action="{{ route('transactions.non-member.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-8 form-group">
                        <label for="ticket_id">Pilih Tiket</label>
                        <select name="ticket_id" id="ticket_id" class="form-control" required>
                                <option value="" data-price="0">-- Pilih Jenis Tiket --</option>
                            @foreach($tickets as $ticket)
                                <option value="{{ $ticket->id }}" data-price="{{ $ticket->price }}" {{ old('ticket_id') == $ticket->id ? 'selected' : '' }}> 
                                    {{ $ticket->name }} - Rp {{ number_format($ticket->price) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="quantity">Jumlah</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" required value="{{ old('quantity', 1) }}" min="1">
                    </div>
                </div>

                <div class="form-group">
                    <label for="customer_name">Nama Pelanggan (Opsional)</label>
                    <input type="text" id="customer_name" name="customer_name" class="form-control" value="{{ old('customer_name') }}" placeholder="Isi nama jika perlu">
                </div>

                <hr>
                
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Total Harga</label>
                        <input type="text" id="total_price_display" class="form-control bg-light" value="Rp 0" readonly>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="amount_paid">Jumlah Bayar</label>
                        <input type="number" id="amount_paid" name="amount_paid" class="form-control" value="{{ old('amount_paid') }}" required min="0" placeholder="Masukkan nominal">
                    </div>
                     <div class="col-md-4 form-group">
                        <label>Kembalian</label>
                        <input type="text" id="change_display" class="form-control bg-light" value="Rp 0" readonly>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Proses & Cetak Struk</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ticketSelect = document.getElementById('ticket_id');
    const quantityInput = document.getElementById('quantity');
    const amountPaidInput = document.getElementById('amount_paid');
    const totalPriceDisplay = document.getElementById('total_price_display');
    const changeDisplay = document.getElementById('change_display');

    function calculate() {
        const selectedOption = ticketSelect.options[ticketSelect.selectedIndex];
        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const quantity = parseInt(quantityInput.value) || 1;
        const amountPaid = parseFloat(amountPaidInput.value) || 0;
        
        const totalPrice = price * quantity;
        const change = amountPaid - totalPrice;

        const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
        
        totalPriceDisplay.value = formatter.format(totalPrice);
        changeDisplay.value = formatter.format(Math.max(0, change));
    }

    ticketSelect.addEventListener('change', calculate);
    quantityInput.addEventListener('input', calculate);
    amountPaidInput.addEventListener('input', calculate);
    
    calculate();
});
</script>
@endpush