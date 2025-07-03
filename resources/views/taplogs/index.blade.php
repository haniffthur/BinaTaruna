@extends('layouts.app')
@section('title', 'Log Aktivitas Tap Kartu (Real-time)')

@push('styles')
<style>
    /* Animasi sederhana untuk baris baru */
    @keyframes fadeIn {
        from { background-color: #e6f7ff; }
        to { background-color: transparent; }
    }
    .new-log-entry {
        animation: fadeIn 2s ease-out;
    }
</style>
@endpush

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <span class="badge badge-pill badge-primary" id="status-indicator">
            <i class="fas fa-circle-notch fa-spin mr-1"></i>Menghubungkan...
        </span>
    </div>

    {{-- Card untuk Filter --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-2"></i>Filter Log</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('tap-logs.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label for="name">Nama Pemilik</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Cari nama..." value="{{ request('name') }}">
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="owner_type">Tipe Pemilik</label>
                        <select name="owner_type" id="owner_type" class="form-control">
                            <option value="">Semua Tipe</option>
                            <option value="member" {{ request('owner_type') == 'member' ? 'selected' : '' }}>Member</option>
                            <option value="coach" {{ request('owner_type') == 'coach' ? 'selected' : '' }}>Pelatih</option>
                            <option value="staff" {{ request('owner_type') == 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="class_id">Kelas (Hanya Member)</label>
                        <select name="class_id" id="class_id" class="form-control">
                            <option value="">Semua Kelas</option>
                            @foreach($schoolClasses as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
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
                                <div class="col-md-6 form-group mb-md-0"><label for="start_date">Dari</label><input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}"></div>
                                <div class="col-md-6 form-group mb-md-0"><label for="end_date">Sampai</label><input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date', now()->format('Y-m-d')) }}"></div>
                            </div>
                        </div>
                    </div>
                  <div class="col-md-4 d-flex justify-content-end">
                        <a href="{{ route('tap-logs.index') }}" class="btn btn-secondary mr-2">Reset</a>
                        <button type="submit" class="btn btn-primary mr-2">Terapkan</button>
                        
                        {{-- TOMBOL EXPORT BARU --}}
                        <a href="{{ route('tap-logs.export.excel', request()->query()) }}" id="export-excel-btn" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Waktu Tap</th>
                            <th>Nomor Kartu</th>
                            <th>Pemilik Kartu</th>
                            <th>Tipe & Detail</th>
                            <th>Status</th>
                            <th>Pesan/Alasan</th>
                        </tr>
                    </thead>
                    <tbody id="log-table-body">
                        @forelse($logs as $index => $log)
                            @php
                                $card = $log->masterCard;
                                $ownerName = 'N/A';
                                $ownerType = 'Tidak Diketahui';
                                $cardDetail = '-';
                                $typeBadgeClass = 'badge-secondary';
                                $cardDisplayNumber = $log->card_uid;

                                if ($card) {
                                    $owner = $card->member ?? $card->coach ?? $card->staff;
                                    $ownerName = $owner->name ?? 'Kartu Tidak Terhubung';
                                    $cardDisplayNumber = $card->cardno ?? $log->card_uid;
                                    
                                    if ($card->member) { $ownerType = 'Member'; $typeBadgeClass = 'badge-info'; $cardDetail = $owner->schoolClass->name ?? 'Tanpa Kelas'; }
                                    elseif ($card->coach) { $ownerType = 'Pelatih'; $typeBadgeClass = 'badge-warning'; $cardDetail = $owner->specialization ?? 'Tanpa Spesialisasi'; }
                                    elseif ($card->staff) { $ownerType = 'Staff'; $typeBadgeClass = 'badge-primary'; $cardDetail = $owner->position ?? 'Tanpa Posisi'; }
                                } else {
                                    $ownerName = 'Kartu Telah Dihapus';
                                }
                            @endphp
                            <tr data-log-id="{{ $log->id }}">
                                <td>{{ $index + $logs->firstItem() }}</td>
                                <td>{{ \Carbon\Carbon::parse($log->tapped_at)->format('d M Y, H:i:s') }}</td>
                                <td><span class="font-monospace">{{ $cardDisplayNumber }}</span></td>
                                <td><strong>{{ $ownerName }}</strong></td>
                                <td><span class="badge {{ $typeBadgeClass }}">{{ $ownerType }}</span><small class="d-block text-muted">{{ $cardDetail }}</small></td>
                                <td>@if($log->status == 1)<span class="badge badge-success">Granted</span>@else<span class="badge badge-danger">Denied</span>@endif</td>
                                <td>{{ $log->message }}</td>
                            </tr>
                        @empty
                            <tr id="no-data-row"><td colspan="7" class="text-center text-muted py-3">Tidak ada data tap yang cocok dengan filter Anda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const periodSelect = document.getElementById('period');
    const customDateRange = document.getElementById('custom-date-range');
    periodSelect.addEventListener('change', function() {
        customDateRange.style.display = (this.value === 'custom') ? 'block' : 'none';
    });

    let latestLogId = 0;
    const tableBody = document.getElementById('log-table-body');
    const statusIndicator = document.getElementById('status-indicator');
    const firstRow = tableBody.querySelector('tr[data-log-id]');
    if (firstRow) {
        latestLogId = parseInt(firstRow.dataset.logId);
    }
    const currentFilters = new URLSearchParams(window.location.search).toString();

    async function fetchLatestLogs() {
        try {
            const url = `{{ route('api.tap-logs.latest') }}?since_id=${latestLogId}&${currentFilters}`;
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) {
                if (response.status === 419) { console.warn('Sesi berakhir, mencoba lagi.'); }
                throw new Error('Network response was not ok');
            }
            const newLogs = await response.json();
            if (newLogs.length > 0) {
                $('#no-data-row').remove();
                newLogs.reverse().forEach(log => {
                    const statusBadge = log.status === 1 ? '<span class="badge badge-success">Granted</span>' : '<span class="badge badge-danger">Denied</span>';
                    const typeBadgeClass = log.owner_type === 'Member' ? 'badge-info' : (log.owner_type === 'Pelatih' ? 'badge-warning' : 'badge-primary');
                    const newRowHtml = `<tr data-log-id="${log.id}" class="new-log-entry"><td><i class="fas fa-star text-warning"></i></td><td>${log.tapped_at}</td><td><span class="font-monospace">${log.card_uid}</span></td><td><strong>${log.owner_name}</strong></td><td><span class="badge ${typeBadgeClass}">${log.owner_type}</span><small class="d-block text-muted">${log.owner_detail}</small></td><td>${statusBadge}</td><td>${log.message}</td></tr>`;
                    tableBody.insertAdjacentHTML('afterbegin', newRowHtml);
                });
                latestLogId = newLogs[newLogs.length - 1].id;
            }
            statusIndicator.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Terhubung';
            statusIndicator.className = 'badge badge-pill badge-success';
        } catch (error) {
            console.error('Gagal mengambil data log:', error);
            statusIndicator.innerHTML = '<i class="fas fa-sync-alt fa-spin mr-1"></i>Menyambung Ulang...';
            statusIndicator.className = 'badge badge-pill badge-warning';
        }
    }
    setInterval(fetchLatestLogs, 5000);
    setTimeout(fetchLatestLogs, 500);
});
</script>
@endpush
