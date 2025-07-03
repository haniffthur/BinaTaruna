@extends('layouts.app')
@section('title', 'Log Scan Tiket (Real-time)')

@push('styles')
<style>
    /* Animasi sederhana untuk baris baru */
    @keyframes fadeIn {
        from { background-color: #d1e7dd; } /* Warna hijau muda */
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Scan Tiket QR Code</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Waktu Scan</th>
                            <th>Token yang Di-scan</th>
                            <th>Nama Tiket</th>
                            <th>Nama Pelanggan</th>
                            <th>Status</th>
                            <th>Pesan/Alasan</th>
                        </tr>
                    </thead>
                    <tbody id="log-table-body">
                        @forelse($logs as $index => $log)
                            @php
                                $ticket = $log->nonMemberTicket;
                                $ticketName = '-';
                                $customerName = 'Tamu';
                                if ($ticket) {
                                    $ticketName = $ticket->ticketProduct->name ?? 'Tiket Dihapus';
                                    $customerName = $ticket->transaction->customer_name ?? 'Tamu';
                                } else {
                                    $ticketName = 'Tiket Telah Dihapus';
                                    $customerName = 'Data tidak tersedia';
                                }
                            @endphp
                            <tr data-log-id="{{ $log->id }}">
                                <td>{{ $index + $logs->firstItem() }}</td>
                                <td>{{ \Carbon\Carbon::parse($log->scanned_at)->format('d M Y, H:i:s') }}</td>
                                <td><span class="font-monospace">{{ $log->scanned_token }}</span></td>
                                <td>{{ $ticketName }}</td>
                                <td>{{ $customerName }}</td>
                                <td>
                                    @if($log->status == 'success')
                                        <span class="badge badge-success">Success</span>
                                    @elseif($log->status == 'not_found')
                                        <span class="badge badge-danger">Not Found</span>
                                    @else
                                        <span class="badge badge-warning">{{ ucfirst(str_replace('_', ' ', $log->status)) }}</span>
                                    @endif
                                </td>
                                <td>{{ $log->message }}</td>
                            </tr>
                        @empty
                            <tr id="no-data-row">
                                <td colspan="7" class="text-center text-muted py-3">Tidak ada aktivitas scan yang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-center">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let latestLogId = 0;
    const tableBody = document.getElementById('log-table-body');
    const statusIndicator = document.getElementById('status-indicator');

    const firstRow = tableBody.querySelector('tr[data-log-id]');
    if (firstRow) {
        latestLogId = parseInt(firstRow.dataset.logId);
    }

    function createLogRow(log) {
        let statusBadge;
        switch(log.status) {
            case 'success':
                statusBadge = '<span class="badge badge-success">Success</span>';
                break;
            case 'not_found':
                statusBadge = '<span class="badge badge-danger">Not Found</span>';
                break;
            default:
                statusBadge = `<span class="badge badge-warning">${log.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>`;
        }

        return `
            <tr data-log-id="${log.id}" class="new-log-entry">
                <td><i class="fas fa-star text-warning"></i></td>
                <td>${log.scanned_at}</td>
                <td><span class="font-monospace">${log.scanned_token}</span></td>
                <td>${log.ticket_name}</td>
                <td>${log.customer_name}</td>
                <td>${statusBadge}</td>
                <td>${log.message}</td>
            </tr>
        `;
    }

    async function fetchLatestLogs() {
        try {
            const response = await fetch("{{ route('api.ticket-scan-logs.latest') }}?since_id=" + latestLogId, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            // --- PERBAIKAN UTAMA ADA DI SINI ---
            if (!response.ok) {
                // Jika statusnya 419, itu adalah error session/CSRF, kita abaikan dan coba lagi nanti
                if (response.status === 419) {
                    console.warn('Session token mismatch. Akan mencoba lagi otomatis.');
                    throw new Error('Session Expired'); // Lemparkan error agar ditangkap di bawah
                }
                throw new Error('Network response was not ok');
            }
            // --- AKHIR DARI PERBAIKAN ---

            const newLogs = await response.json();

            if (newLogs.length > 0) {
                const noDataRow = document.getElementById('no-data-row');
                if(noDataRow) noDataRow.remove();

                newLogs.reverse().forEach(log => {
                    const newRowHtml = createLogRow(log);
                    tableBody.insertAdjacentHTML('afterbegin', newRowHtml);
                });
                
                latestLogId = newLogs[newLogs.length - 1].id;
            }
            
            // Jika berhasil, set status menjadi "Terhubung"
            statusIndicator.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Terhubung';
            statusIndicator.className = 'badge badge-pill badge-success';

        } catch (error) {
            console.error('Gagal mengambil data log:', error.message);
            
            // Tampilkan status "Gagal" atau "Menyambung Ulang" hanya sesaat
            statusIndicator.innerHTML = '<i class="fas fa-sync-alt fa-spin mr-1"></i>Menyambung Ulang...';
            statusIndicator.className = 'badge badge-pill badge-warning';
            // Jangan hentikan interval, biarkan ia mencoba lagi pada putaran berikutnya
        }
    }

    // Jalankan fungsi fetch setiap 5 detik
    setInterval(fetchLatestLogs, 5000);

    // Beri jeda sedikit sebelum pemanggilan pertama
    setTimeout(fetchLatestLogs, 500);
});
</script>
@endpush
