@extends('layouts.app') {{-- Sesuaikan dengan layout utama Anda --}}
@section('title', 'Dashboard')

@section('content')

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        {{-- Tombol ini bisa Anda fungsikan nanti --}}
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
    </div>

     <!-- Baris Kartu Ringkasan (Info Cards) -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Pendapatan ({{ $periodLabel }})</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($revenueInRange, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pendaftaran Baru ({{ $periodLabel }})</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalMembersInRange) }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-plus fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Transaksi ({{ $periodLabel }})</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalTransactionsInRange) }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-receipt fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Tap Masuk ({{ $periodLabel }})</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($grantedTapsInRange) }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-id-card-alt fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FORM FILTER BARU -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-2"></i>Tampilkan Statistik Untuk</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('dashboard') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="filter">Periode</label>
                        <select name="filter" id="filter" class="form-control">
                            <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Hari Ini</option>
                            <option value="this_week" {{ request('filter') == 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
                            <option value="this_month" {{ request('filter', 'this_month') == 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                            <option value="custom" {{ request('filter') == 'custom' ? 'selected' : '' }}>Pilih Rentang</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div id="custom-date-range" style="{{ request('filter') == 'custom' ? '' : 'display: none;' }}">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="start_date">Dari Tanggal</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date">Sampai Tanggal</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-block">Terapkan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

   

    <!-- Baris Grafik dan Aktivitas -->
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Grafik Transaksi ({{ $periodLabel }})</h6></div>
                <div class="card-body">
                    <div class="chart-area"><canvas id="transactionChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aktivitas Tap Terbaru</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($recentTapLogs as $log)
                            @php
                                // --- PERBAIKAN LOGIKA: Cek setiap relasi secara bertahap ---
                                $card = $log->masterCard;
                                $ownerName = 'N/A';

                                if ($card) { // Pertama, pastikan objek kartu ada
                                    $owner = $card->member ?? $card->coach ?? $card->staff;
                                    $ownerName = $owner->name ?? 'Kartu Tidak Terhubung';
                                } else {
                                    // Kartu yang terkait dengan log ini sudah dihapus dari database
                                    $ownerName = 'Kartu Tidak ditemukan';
                                }
                                // --- AKHIR DARI PERBAIKAN LOGIKA ---
                            @endphp
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 font-weight-bold">{{ $ownerName }}</h6>
                                    <small>{{ $log->tapped_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1 small">
                                    @if($log->status == 'granted' || $log->status == 1)
                                        <span class="text-success"><i class="fas fa-check-circle fa-fw"></i> Akses Diberikan</span>
                                    @else
                                        <span class="text-danger"><i class="fas fa-times-circle fa-fw"></i> Akses Ditolak</span>
                                    @endif
                                </p>
                            </a>
                        @empty
                            <p class="text-center text-muted">Belum ada aktivitas tap.</p>
                        @endforelse
                    </div>
                </div>
                 <div class="card-footer text-center">
                    <a href="{{ route('tap-logs.index') }}" class="small">Lihat Semua Aktivitas &rarr;</a>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // JavaScript untuk menampilkan/menyembunyikan filter rentang tanggal kustom
            const filterSelect = document.getElementById('filter');
            const customDateRange = document.getElementById('custom-date-range');

            filterSelect.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customDateRange.style.display = 'block';
                } else {
                    customDateRange.style.display = 'none';
                }
            });

            // Data untuk Grafik
            const chartLabels = @json($chartLabels);
            const chartData = @json($chartData);

            // Inisialisasi Grafik
            const ctx = document.getElementById('transactionChart').getContext('2d');
            const transactionChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: "Jumlah Transaksi",
                        lineTension: 0.3,
                        backgroundColor: "rgba(78, 115, 223, 0.05)",
                        borderColor: "rgba(78, 115, 223, 1)",
                        pointRadius: 3, pointBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointBorderColor: "rgba(78, 115, 223, 1)",
                        data: chartData,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    scales: { 
                        y: { 
                            ticks: { 
                                precision: 0 
                            } 
                        } 
                    },
                    plugins: { 
                        legend: { 
                            display: false 
                        } 
                    }
                }
            });
        });
    </script>
@endpush
