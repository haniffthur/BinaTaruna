@extends('layouts.app')
@section('title', 'Dashboard')

@push('styles')
<style>
    /* Memberi ruang yang cukup untuk grafik */
    .chart-area {
        position: relative;
        height: 320px;
        width: 100%;
    }
    /* Mengatur agar kursor berubah saat bisa digeser */
    #mainChart.is-panning {
        cursor: grabbing;
    }
</style>
@endpush

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        {{-- PERBAIKAN: Tombol Report sekarang memiliki ID dan target _blank --}}
        <a href="{{ route('dashboard.report') }}" id="generate-report-btn" target="_blank" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-2"></i>Tampilkan Statistik Untuk</h6>
        </div>
        <div class="card-body">
            <form id="dashboard-filter-form">
                <div class="row align-items-end">
                    <div class="col-md-3 form-group mb-md-0">
                        <label for="filter">Periode</label>
                        <select name="filter" id="filter" class="form-control">
                            <option value="this_month">Bulan Ini</option>
                            <option value="this_week">Minggu Ini</option>
                            <option value="today">Hari Ini</option>
                            <option value="custom">Pilih Rentang</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-md-0">
                        <label for="class_id">Filter Kelas</label>
                        <select name="class_id" id="class_id" class="form-control">
                            <option value="">Semua Kelas</option>
                            @foreach($schoolClasses as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- NEW: Status Filter --}}
                    <div class="col-md-3 form-group mb-md-0">
                        <label for="status_filter">Filter Status Tap</label>
                        <select name="status_filter" id="status_filter" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="granted">Granted</option>
                            <option value="denied">Denied</option>
                        </select>
                    </div>
                    <div class="col-md-3"> {{-- Adjusted column for date range and button --}}
                        <div id="custom-date-range" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 form-group mb-md-0"><label for="start_date">Dari</label><input type="date" name="start_date" id="start_date" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}"></div>
                                <div class="col-md-6 form-group mb-md-0"><label for="end_date">Sampai</label><input type="date" name="end_date" id="end_date" class="form-control" value="{{ now()->format('Y-m-d') }}"></div>
                            </div>
                        </div>
                        {{-- Adjusted button position if custom date range is visible --}}
                        <div class="d-grid mt-3 mt-md-0">
                            <button type="submit" class="btn btn-primary">Terapkan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Pendapatan (<span class="period-label">...</span>)</div><div id="card-revenue" class="h5 mb-0 font-weight-bold text-gray-800">Memuat...</div></div><div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div></div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Member</div>
                            {{-- Nilai ini diisi langsung dari controller saat halaman dimuat, tidak terpengaruh filter --}}
                            <div id="card-total-members" class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalAllMembers) }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-plus fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Transaksi (<span class="period-label">...</span>)</div><div id="card-transactions" class="h5 mb-0 font-weight-bold text-gray-800">Memuat...</div></div><div class="col-auto"><i class="fas fa-receipt fa-2x text-gray-300"></i></div></div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-warning shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Tap Masuk (<span class="period-label">...</span>)</div><div id="card-taps" class="h5 mb-0 font-weight-bold text-gray-800">Memuat...</div></div><div class="col-auto"><i class="fas fa-id-card-alt fa-2x text-gray-300"></i></div></div></div></div></div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 id="chart-title" class="m-0 font-weight-bold text-primary">Grafik Aktivitas Tap Kartu</h6>
                    <button id="reset-zoom-btn" class="btn btn-sm btn-outline-secondary" style="display: none;"><i class="fas fa-sync-alt fa-sm"></i> Reset Zoom</button>
                </div>
                <div class="card-body">
                    <div class="chart-area"><canvas id="mainChart"></canvas></div>
                    <small class="form-text text-muted text-center mt-2">Gunakan scroll mouse untuk zoom, seret untuk menggeser grafik.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
               <div class="card shadow mb-4">
                 <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">5 Aktivitas Tap Terbaru</h6>
                    <a href="{{ route('tap-logs.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua Log &rarr;</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Pemilik Kartu</th>
                                    <th>Tipe & Detail</th>
                                    <th>Nomor Kartu</th>
                                    <th>Status</th>
                                    <th>Pesan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTapLogs as $log)
                                    @php
                                        $card = $log->masterCard;
                                        $ownerName = 'N/A';
                                        $cardDisplayNumber = $log->card_uid;
                                        $cardType = 'Tidak Diketahui';
                                        $cardDetail = '-';
                                        $typeBadgeClass = 'badge-secondary';
                                        if ($card) {
                                            $owner = $card->member ?? $card->coach ?? $card->staff;
                                            $ownerName = $owner->name ?? 'Kartu Tidak Terhubung';
                                            $cardDisplayNumber = $card->cardno ?? $log->card_uid;
                                            if ($card->member) { $cardType = 'Member'; $typeBadgeClass = 'badge-info'; $cardDetail = $owner->schoolClass->name ?? 'Tanpa Kelas'; }
                                            elseif ($card->coach) { $cardType = 'Pelatih'; $typeBadgeClass = 'badge-warning'; $cardDetail = $owner->specialization ?? 'Tanpa Spesialisasi'; }
                                            elseif ($card->staff) { $cardType = 'Staff'; $typeBadgeClass = 'badge-primary'; $cardDetail = $owner->position ?? 'Tanpa Posisi'; }
                                        } else { $ownerName = 'Kartu Telah Dihapus'; }
                                    @endphp
                                    <tr>
                                        <td>{{ $log->tapped_at->diffForHumans() }}</td>
                                        <td><strong>{{ $ownerName }}</strong></td>
                                        <td><span class="badge {{ $typeBadgeClass }}">{{ $cardType }}</span><small class="d-block text-muted">{{ $cardDetail }}</small></td>
                                        <td><span class="font-monospace">{{ $cardDisplayNumber }}</span></td>
                                        <td>
                                            @if($log->status == 'granted' || $log->status == 1)
                                                <span class="badge badge-success">Granted</span>
                                            @else
                                                <span class="badge badge-danger">Denied</span>
                                            @endif
                                        </td>
                                        <td>{{ $log->message }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">Belum ada aktivitas tap.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1/dist/chartjs-plugin-zoom.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const filterForm = document.getElementById('dashboard-filter-form');
            const reportBtn = document.getElementById('generate-report-btn');
            const mainChartCanvas = document.getElementById('mainChart').getContext('2d');
            let mainChart;

            // --- FUNGSI BARU: Mengupdate URL tombol report ---
            function updateReportUrl() {
                const formData = new FormData(filterForm);
                const params = new URLSearchParams(formData).toString();
                reportBtn.href = `{{ route('dashboard.report') }}?${params}`;
            }

            function updateDashboard(params = '') {
                $('.period-label').text('Memuat...');
                $('#card-revenue, #card-new-members, #card-transactions, #card-taps').text('...');
                $('#chart-title').text('Grafik Aktivitas Tap Kartu (Memuat...)');

                fetch(`{{ route('api.dashboard.chart-data') }}${params}`)
                    .then(response => response.json())
                    .then(data => {
                        $('.period-label').text(data.chart.period_label);
                        $('#card-revenue').text(data.cards.revenue);
                        $('#card-new-members').text(data.cards.new_members);
                        $('#card-transactions').text(data.cards.transactions);
                        $('#card-taps').text(data.cards.taps);
                        $('#chart-title').text(`Grafik Aktivitas Tap Kartu (${data.chart.period_label})`);

                        if (mainChart) mainChart.destroy();
                        mainChart = new Chart(mainChartCanvas, {
                            type: 'bar',
                            data: {
                                labels: data.chart.labels,
                                datasets: [
                                    { label: "Akses Diberikan", backgroundColor: "rgba(28, 200, 138, 0.8)", data: data.chart.granted_data, borderRadius: 4 },
                                    { label: "Akses Ditolak", backgroundColor: "rgba(231, 74, 59, 0.8)", data: data.chart.denied_data, borderRadius: 4 }
                                ]
                            },
                            options: {
                                maintainAspectRatio: false,
                                scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 } } },
                                plugins: {
                                    legend: { display: true, position: 'bottom' },
                                    tooltip: { backgroundColor: "#fff", titleColor: "#6e707e", bodyColor: "#858796", borderColor: '#dddfeb', borderWidth: 1, padding: 15 },
                                    zoom: {
                                        pan: { enabled: true, mode: 'x', onPanComplete: () => $('#reset-zoom-btn').show() },
                                        zoom: { wheel: { enabled: true }, pinch: { enabled: true }, mode: 'x', onZoomComplete: () => $('#reset-zoom-btn').show() }
                                    }
                                }
                            }
                        });
                    })
                    .catch(error => console.error('Error fetching dashboard data:', error));
            }

            // --- EVENT LISTENERS ---
            filterForm.addEventListener('change', updateReportUrl);
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                updateReportUrl();
                const params = new URLSearchParams(new FormData(this)).toString();
                updateDashboard('?' + params);
            });
            $('#filter').change(function() {
                // Adjusting layout slightly for the date pickers when custom is selected
                const customDateRange = $('#custom-date-range');
                const submitButtonCol = $(this).closest('.row').find('.col-md-3:last'); // Find the submit button's column
                if ($(this).val() === 'custom') {
                    customDateRange.show();
                    submitButtonCol.removeClass('col-md-2').addClass('col-md-3').addClass('order-last'); // Make it wider, push to end
                } else {
                    customDateRange.hide();
                    submitButtonCol.removeClass('col-md-3 order-last').addClass('col-md-2'); // Revert width
                }
            });
            $('#reset-zoom-btn').click(() => { if(mainChart) { mainChart.resetZoom(); $('#reset-zoom-btn').hide(); } });

            // --- INISIALISASI ---
            updateReportUrl();
            updateDashboard('?filter=this_month');
        });
    </script>
@endpush