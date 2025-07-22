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
            margin-bottom: 2.5rem; /* Increased margin for better separation */
            border-bottom: 2px solid #e9ecef; /* Slightly lighter border */
            padding-bottom: 1.5rem; /* Increased padding */
        }

        .report-header h2 {
            font-weight: 700;
            color: #212529; /* Darker heading color */
            margin-bottom: 0.5rem;
        }

        .lead {
            font-size: 1.15rem; /* Slightly larger lead text */
            color: #495057; /* Darker lead text */
            margin-bottom: 0.25rem;
        }

        .section-title {
            font-weight: 700;
            color: #212529;
            margin-bottom: 1.5rem; /* Consistent spacing for section titles */
        }

        .summary-card {
            background-color: #fff;
            border: 1px solid #e0e0e0; /* Slightly more prominent border */
            border-radius: 0.65rem; /* Slightly more rounded corners */
            padding: 1.25rem; /* Increased padding */
            margin-bottom: 1.5rem; /* Increased margin */
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08); /* Stronger, softer shadow */
            transition: transform 0.2s ease-in-out; /* Add subtle hover effect */
        }

        .summary-card:hover {
            transform: translateY(-3px); /* Lift card on hover */
        }

        .summary-card .label {
            font-size: 0.95rem; /* Slightly larger label */
            color: #6c757d;
            margin-bottom: 0.3rem; /* Space between label and value */
        }

        .summary-card .value {
            font-size: 1.8rem; /* Larger value */
            font-weight: 700; /* Bolder value */
            color: #1a1a1a; /* Even darker value color */
        }

        .table {
            --bs-table-striped-bg: #f2f5f7; /* Lighter stripe color */
            --bs-table-hover-bg: #e9ecef; /* Lighter hover color */
        }

        .table thead th {
            background-color: #e9ecef; /* Consistent header background */
            color: #343a40; /* Darker header text */
            border-bottom: 2px solid #dee2e6;
            vertical-align: middle; /* Center text vertically in headers */
            padding: 0.75rem 1rem; /* Adjust header padding */
        }

        .table-bordered td, .table-bordered th {
            border: 1px solid #e0e0e0; /* Lighter border for cells */
            vertical-align: middle; /* Center text vertically in cells */
        }

        .table tbody tr:last-child td {
            border-bottom: 1px solid #e0e0e0; /* Ensure bottom border for last row */
        }

        .badge {
            padding: 0.4em 0.7em;
            font-size: 0.8em;
            font-weight: 600;
        }

        @media print {
            .no-print {
                display: none;
            }
            body {
                font-size: 10pt;
                background-color: #fff;
            }
            .table {
                font-size: 9pt;
            }
            .summary-card {
                box-shadow: none; /* Remove shadow for print */
                border: 1px solid #e0e0e0; /* Keep borders for definition */
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="report-header">
            <h2>Laporan Aktivitas & Transaksi</h2>
            <p class="lead">Periode: <strong>{{ $periodLabel }}</strong></p>
            @if($filteredClass)
                <p class="fw-bold">Filter Kelas: {{ $filteredClass->name }}</p>
            @endif
        </div>

        <h3 class="section-title">Ringkasan Data</h3>
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="label">Pendapatan</div>
                    <div class="value">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="label">Pendaftaran Baru</div>
                    <div class="value">{{ number_format($summary['new_members']) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="label">Total Transaksi</div>
                    <div class="value">{{ number_format($summary['transactions']) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="label">Total Tap Masuk</div>
                    <div class="value">{{ number_format($summary['taps']) }}</div>
                </div>
            </div>
        </div>

        <h3 class="section-title">Detail Log Tap Kartu</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle caption-top">
                <caption>Daftar lengkap aktivitas tap kartu.</caption>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 18%;">Waktu</th>
                        <th style="width: 22%;">Pemilik Kartu</th>
                        <th style="width: 15%;">Tipe</th>
                        <th style="width: 20%;">Detail</th> <th style="width: 10%;">Nomor Kartu</th>
                        <th style="width: 10%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detailedTapLogs as $index => $log)
                        @php
                            $card = $log->masterCard;
                            $ownerName = 'N/A';
                            $ownerType = 'Tidak Diketahui';
                            $cardDisplayNumber = $log->card_uid;
                            $cardDetail = '-'; // Initialize cardDetail

                            if ($card) {
                                $owner = $card->member ?? $card->coach ?? $card->staff;
                                $ownerName = $owner->name ?? 'Kartu Tidak Terhubung';
                                $cardDisplayNumber = $card->cardno ?? $log->card_uid;
                                
                                if ($card->member) {
                                    $ownerType = 'Member';
                                    $cardDetail = $owner->schoolClass->name ?? 'Tanpa Kelas';
                                } elseif ($card->coach) {
                                    $ownerType = 'Pelatih';
                                    $cardDetail = $owner->specialization ?? 'Tanpa Spesialisasi';
                                } elseif ($card->staff) {
                                    $ownerType = 'Staff';
                                    $cardDetail = $owner->position ?? 'Tanpa Posisi';
                                }
                            } else {
                                $ownerName = 'Kartu Telah Dihapus';
                                $ownerType = 'N/A';
                            }
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($log->tapped_at)->format('d M Y, H:i:s') }}</td>
                            <td>{{ $ownerName }}</td>
                            <td>{{ $ownerType }}</td>
                            <td>{{ $cardDetail }}</td> <td>{{ $cardDisplayNumber }}</td>
                            <td>
                                @if($log->status == 'granted' || $log->status == 1)
                                    <span class="badge bg-success">Granted</span>
                                @else
                                    <span class="badge bg-danger">Denied</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada data tap untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Icon support (optional, Bootstrap Icons) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</body>
</html>