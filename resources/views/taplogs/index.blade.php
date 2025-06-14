@extends('layouts.app')

@section('title', 'Log Aktivitas Tap Kartu')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Tap Kartu RFID</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Waktu Tap</th>
                            <th>UID Kartu</th>
                            <th>Pemilik Kartu</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Pesan/Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $index => $log)
                            @php
                                // Logika untuk menemukan nama pemilik dari berbagai kemungkinan relasi
                                $card = $log->masterCard;
                                $owner = $card->member ?? $card->coach ?? $card->staff ?? null;
                                $ownerName = $owner->name ?? 'N/A';
                                $ownerType = 'Tidak Diketahui';
                                if ($card->member) $ownerType = 'Member';
                                elseif ($card->coach) $ownerType = 'Pelatih';
                                elseif ($card->staff) $ownerType = 'Staff';
                            @endphp
                            <tr>
                                <td>{{ $index + $logs->firstItem() }}</td>
                                <td>{{ \Carbon\Carbon::parse($log->tapped_at)->format('d M Y, H:i:s') }}</td>
                                <td>{{ $card->card_uid }}</td>
                                <td>{{ $ownerName }}</td>
                                <td>{{ $ownerType }}</td>
                                <td>
                                    @if($log->status == 'granted')
                                        <span class="badge badge-success">Granted</span>
                                    @else
                                        <span class="badge badge-danger">Denied</span>
                                    @endif
                                </td>
                                <td>{{ $log->message }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">Tidak ada aktivitas tap yang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection