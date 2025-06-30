@extends('layouts.app')
@section('title', 'Log Aktivitas Scan Tiket')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
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
                    <tbody>
                        @forelse($logs as $index => $log)
                            <tr>
                                <td>{{ $index + $logs->firstItem() }}</td>
                                <td>{{ \Carbon\Carbon::parse($log->scanned_at)->format('d M Y, H:i:s') }}</td>
                                <td><span class="font-monospace">{{ $log->scanned_token }}</span></td>
                                <td>{{ $log->nonMemberTicket->ticketProduct->name ?? '-' }}</td>
                                <td>{{ $log->nonMemberTicket->transaction->customer_name ?? 'Tamu' }}</td>
                                <td>
                                    @if($log->status == 'success')
                                        <span class="badge badge-success">Success</span>
                                    @elseif($log->status == 'not_found')
                                        <span class="badge badge-danger">Not Found</span>
                                    @else
                                        <span class="badge badge-warning">{{ ucfirst($log->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $log->message }}</td>
                            </tr>
                        @empty
                            <tr>
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

