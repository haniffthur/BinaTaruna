@extends('layouts.app')
@section('title', 'Riwayat Seluruh Transaksi')
@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
    </div>

    {{-- Card untuk Filter --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-2"></i>Filter Transaksi</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('transactions.index') }}" method="GET">
                {{-- Simpan filter tipe yang sedang aktif --}}
                <input type="hidden" name="type" value="{{ request('type', 'all') }}">
                
                {{-- PERBAIKAN UTAMA: Tata letak filter baru yang lebih rapi --}}
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="name">Nama Pelanggan</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Cari berdasarkan nama..." value="{{ request('name') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="class_id">Filter Kelas</label>
                        <select name="class_id" id="class_id" class="form-control">
                            <option value="">Semua Kelas</option>
                            @foreach($schoolClasses as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="period">Periode</label>
                        <select name="period" id="period" class="form-control">
                            <option value="all_time" {{ request('period', 'all_time') == 'all_time' ? 'selected' : '' }}>Semua Waktu</option>
                            <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Hari Ini</option>
                            <option value="this_week" {{ request('period') == 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
                            <option value="this_month" {{ request('period') == 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                            <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Pilih Rentang</option>
                        </select>
                    </div>
                </div>

                <div class="row align-items-end">
                    <div class="col-md-8">
                        <div id="custom-date-range" style="{{ request('period') == 'custom' ? '' : 'display: none;' }}">
                            <div class="row">
                                <div class="col-md-6 form-group mb-md-0"><label for="start_date">Dari Tanggal</label><input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}"></div>
                                <div class="col-md-6 form-group mb-md-0"><label for="end_date">Sampai Tanggal</label><input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date', now()->format('Y-m-d')) }}"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex justify-content-end">
                        <a href="{{ route('transactions.index', ['type' => request('type', 'all')]) }}" class="btn btn-secondary mr-2">Reset</a>
                        <button type="submit" class="btn btn-primary mr-2">Terapkan</button>
                        
                        {{-- TOMBOL EXPORT BARU --}}
                        <a href="{{ route('transactions.export.excel', request()->query()) }}" id="export-excel-btn" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export
                        </a>
                    </div>
                    
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation"><a class="nav-link {{ request('type', 'all') == 'all' ? 'active' : '' }}" href="{{ route('transactions.index', array_merge(request()->except('type'), ['type' => 'all'])) }}">Semua Transaksi</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link {{ request('type') == 'member' ? 'active' : '' }}" href="{{ route('transactions.index', array_merge(request()->except('type'), ['type' => 'member'])) }}">Transaksi Member</a></li>
                <!-- <li class="nav-item" role="presentation"><a class="nav-link {{ request('type') == 'non-member' ? 'active' : '' }}" href="{{ route('transactions.index', array_merge(request()->except('type'), ['type' => 'non-member'])) }}">Transaksi Non-Member</a></li> -->
            </ul>
            <div class="tab-content mt-3" id="myTabContent">
                <div class="tab-pane fade show active" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>ID Transaksi</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Tipe Transaksi</th>
                                    <th>Kelas</th>
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
                                        <td><strong>{{ $transaction->item_name ?? '-' }}</strong></td>
                                        <td>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y, H:i') }}</td>
                                         <!-- <td>
                                            @if($transaction->transaction_type == 'Non-Member')
                                                {{-- PERBAIKAN PENTING DI SINI: Arahkan ke route 'non-member-receipt.show' --}}
                                               <a href="{{ route('non-member-receipt.show', $transaction->id) }}" class="btn btn-info btn-sm">Struk</a>
                                            @else
                                                -
                                            @endif
                                        </td> -->
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada riwayat transaksi yang cocok dengan filter Anda.</td>
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
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const filterForm = document.getElementById('transaction-filter-form');
        const exportBtn = document.getElementById('export-excel-btn');
        
        // Fungsi untuk mengupdate URL tombol export
        function updateExportUrl() {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData).toString();
            exportBtn.href = `{{ route('transactions.export.excel') }}?${params}`;
        }

        // Panggil fungsi setiap kali ada perubahan pada form filter
        filterForm.addEventListener('change', updateExportUrl);

        // Panggil sekali saat halaman dimuat untuk set URL awal
        updateExportUrl();

        // JavaScript untuk menampilkan/menyembunyikan rentang tanggal kustom
        document.getElementById('period').addEventListener('change', function() {
            document.getElementById('custom-date-range').style.display = (this.value === 'custom') ? 'block' : 'none';
        });
    });
</script>
@endpush
