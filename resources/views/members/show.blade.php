@extends('layouts.app') {{-- Sesuaikan dengan layout utama Anda --}}

@section('title', 'Detail Member: ' . $member->name)

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('members.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Member
        </a>
    </div>

    <div class="row">
        {{-- Kolom Kiri: Detail Profil Member dengan Foto --}}
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Profil</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Bagian untuk Foto --}}
                        <div class="col-md-4 text-center">
                            @if($member->photo)
                                <img src="{{ asset('storage/' . $member->photo) }}" class="img-fluid rounded mb-3" alt="Foto Profil">
                            @else
                                {{-- Placeholder jika tidak ada foto --}}
                                <img src="https://via.placeholder.com/150" class="img-fluid rounded mb-3" alt="Tidak ada foto">
                            @endif
                        </div>
                        {{-- Bagian untuk Tabel Detail --}}
                        <div class="col-md-8">
                             <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <th width="40%">Nama Lengkap</th>
                                            <td>{{ $member->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Kelas</th>
                                            <td>{{ $member->schoolClass->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Nomor Telepon</th>
                                            <td>{{ $member->phone_number ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Lahir</th>
                                            <td>{{ $member->date_of_birth ? \Carbon\Carbon::parse($member->date_of_birth)->translatedFormat('d F Y') : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Nama Orang Tua</th>
                                            <td>{{ $member->parent_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Alamat</th>
                                            <td>{{ $member->address ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Bergabung</th>
                                            <td>{{ \Carbon\Carbon::parse($member->join_date)->translatedFormat('d F Y') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Detail Kartu dan Aturan Akses --}}
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Kartu & Aturan Akses</h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">Kartu RFID</h6>
                    <p>
                        @if($member->masterCard)
                            <i class="fas fa-id-card fa-fw mr-2"></i>UID: <strong>{{ $member->masterCard->cardno }}</strong>
                        @else
                            <span class="text-danger">Belum ada kartu yang terhubung.</span>
                        @endif
                    </p>
                    <hr>
                    <h6 class="font-weight-bold">Status & Sisa Tap</h6>
                    
                    {{-- Tampilan untuk Tap Harian --}}
                    <div>
                        <span>Tap Hari Ini:</span>
                        @if ($tapsData['max_daily'] == 'N/A')
                            <span class="font-weight-bold float-right text-muted">Tidak Dibatasi</span>
                        @elseif ($tapsData['max_daily'] == 'Tak Terbatas')
                             <span class="font-weight-bold float-right text-success">Tak Terbatas</span>
                        @else
                            <span class="font-weight-bold float-right">{{ $tapsData['used_daily'] }} / {{ $tapsData['max_daily'] }}</span>
                        @endif
                    </div>
                    @if (is_numeric($tapsData['remaining_daily']))
                        <div class="progress my-2" style="height: 10px;">
                            @php
                                $percentage_daily = ($tapsData['max_daily'] > 1) ? ($tapsData['used_daily'] / $tapsData['max_daily']) * 100 : 1;
                            @endphp
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percentage_daily }}%" aria-valuenow="{{ $percentage_daily }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <p class="text-right">Sisa tap hari ini: <strong>{{ $tapsData['remaining_daily'] }}</strong></p>
                    @endif

                    {{-- Tampilan untuk Tap Bulanan --}}
                     <div class="mt-3">
                        <span>Tap Bulan Ini:</span>
                        @if ($tapsData['max_monthly'] == 'N/A')
                            <span class="font-weight-bold float-right text-muted">Tidak Dibatasi</span>
                        @elseif ($tapsData['max_monthly'] == 'Tak Terbatas')
                             <span class="font-weight-bold float-right text-success">Tak Terbatas</span>
                        @else
                            <span class="font-weight-bold float-right">{{ $tapsData['used_monthly'] }} / {{ $tapsData['max_monthly'] }}</span>
                        @endif
                    </div>
                     @if (is_numeric($tapsData['remaining_monthly']))
                        <div class="progress my-2" style="height: 10px;">
                            @php
                                $percentage_monthly = ($tapsData['max_monthly'] > 1) ? ($tapsData['used_monthly'] / $tapsData['max_monthly']) * 100 : 1;
                            @endphp
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $percentage_monthly }}%" aria-valuenow="{{ $percentage_monthly }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <p class="text-right">Sisa tap bulan ini: <strong>{{ $tapsData['remaining_monthly'] }}</strong></p>
                    @endif
                    
                    <hr>
                    <h6 class="font-weight-bold">Detail Aturan</h6>
                    @if ($member->rule_type == 'custom')
                        <p class="font-weight-bold text-info"><i class="fas fa-star fa-fw mr-2"></i>Menggunakan Aturan Custom</p>
                    @elseif($member->accessRule)
                         <p class="font-weight-bold text-secondary"><i class="fas fa-file-alt fa-fw mr-2"></i>Menggunakan Template: {{ $member->accessRule->name }}</p>
                    @else
                        <p class="text-muted">Tidak ada aturan spesifik yang diterapkan.</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
