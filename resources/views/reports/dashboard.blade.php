<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Dashboard - {{ $periodLabel }}</title>

    {{-- Gunakan Bootstrap 5 untuk tampilan modern --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font modern --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #333;
            background-color: #f8f9fa;
        }

        .report-header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 1rem;
        }

        .report-header h2 {
            font-weight: 700;
            color: #2c3e50;
        }

        .lead {
            font-size: 1.1rem;
            color: #6c757d;
        }

        .summary-card {
            background-color: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .summary-card .label {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .summary-card .value {
            font-size: 1.5rem;
            font-weight: 600;
            color: #111;
        }

        .table thead th {
            background-color: #f1f3f5;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }

        .table-bordered td, .table-bordered th {
            border: 1px solid #dee2e6;
        }

        .btn-print {
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            border-radius: 0.375rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        @media print {
            .no-print {
                display: none;
            }
            body {
                font-size: 11pt;
                background-color: #fff;
            }
            .table {
                font-size: 10pt;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="report-header">
            <h2>Laporan Aktivitas & Transaksi</h2>
            <p class="lead">Periode: {{ $periodLabel }}</p>
            @if($filteredClass)
                <p class="fw-bold">Filter Kelas: {{ $filteredClass->name }}</p>
            @endif
        </div>

        <h5 class="mb-3 fw-semibold">Ringkasan Data</h5>
        <div class="row g-3">
            <div class="col-md-3"><div class="summary-card"><div class="label">Pendapatan</div><div class="value">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</div></div></div>
            <div class="col-md-3"><div class="summary-card"><div class="label">Pendaftaran Baru</div><div class="value">{{ number_format($summary['new_members']) }}</div></div></div>
            <div class="col-md-3"><div class="summary-card"><div class="label">Total Transaksi</div><div class="value">{{ number_format($summary['transactions']) }}</div></div></div>
            <div class="col-md-3"><div class="summary-card"><div class="label">Total Tap Masuk</div><div class="value">{{ number_format($summary['taps']) }}</div></div></div>
        </div>

        <h5 class="mt-5 mb-3 fw-semibold">Detail Log Tap Kartu</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Waktu</th>
                        <th>Pemilik Kartu</th>
                        <th>Tipe</th>
                        <th>Nomor Kartu</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailedTapLogs as $index => $log)
                        @php
                            $card = $log->masterCard;
                            $ownerName = 'N/A'; $ownerType = 'Tidak Diketahui'; $cardDisplayNumber = $log->card_uid;
                            if ($card) {
                                $owner = $card->member ?? $card->coach ?? $card->staff;
                                $ownerName = $owner->name ?? 'Kartu Tidak Terhubung';
                                $cardDisplayNumber = $card->cardno ?? $log->card_uid;
                                if ($card->member) $ownerType = 'Member';
                                elseif ($card->coach) $ownerType = 'Pelatih';
                                elseif ($card->staff) $ownerType = 'Staff';
                            } else { $ownerName = 'Kartu Telah Dihapus'; }
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($log->tapped_at)->format('d M Y, H:i:s') }}</td>
                            <td>{{ $ownerName }}</td>
                            <td>{{ $ownerType }}</td>
                            <td>{{ $cardDisplayNumber }}</td>
                            <td>
                                @if($log->status == 'granted' || $log->status == 1)
                                    <span class="badge bg-success">Granted</span>
                                @else
                                    <span class="badge bg-danger">Denied</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Tidak ada data tap untuk periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        
    </div>

    {{-- Icon support (optional, Bootstrap Icons) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</body>
</html>
