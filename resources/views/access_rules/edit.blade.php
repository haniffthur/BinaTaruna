@extends('layouts.app')

@section('title', 'Edit Aturan Akses')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('access-rules.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('access-rules.update', $accessRule->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Nama Aturan</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $accessRule->name) }}" required>
                </div>
                <div class="form-group">
                    <label for="description">Deskripsi Singkat</label>
                    <textarea name="description" id="description" class="form-control" rows="2">{{ old('description', $accessRule->description) }}</textarea>
                </div>
                
                <hr>
                <h6 class="font-weight-bold">Batasan Tap</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_taps_per_day">Maksimal Tap per Hari</label>
                            <input type="number" name="max_taps_per_day" id="max_taps_per_day" class="form-control" value="{{ old('max_taps_per_day', $accessRule->max_taps_per_day) }}" min="0">
                            <small class="form-text text-muted">Kosongkan jika tidak ada batasan.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_taps_per_month">Maksimal Tap per Bulan</label>
                            <input type="number" name="max_taps_per_month" id="max_taps_per_month" class="form-control" value="{{ old('max_taps_per_month', $accessRule->max_taps_per_month) }}" min="0">
                            <small class="form-text text-muted">Kosongkan jika tidak ada batasan.</small>
                        </div>
                    </div>
                </div>

                <hr>
                <h6 class="font-weight-bold">Jadwal Akses</h6>
                <div class="form-group">
                    <label>Hari yang Diizinkan</label>
                    <div class="d-flex flex-wrap">
                        @php
                            $selectedDays = old('allowed_days', $accessRule->allowed_days ?? []);
                        @endphp
                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                        <div class="form-check form-check-inline mr-3">
                            <input class="form-check-input" type="checkbox" name="allowed_days[]" value="{{ $day }}" id="{{ $day }}" {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                            <label class="form-check-label text-capitalize" for="{{ $day }}">{{ $day }}</label>
                        </div>
                        @endforeach
                    </div>
                    <small class="form-text text-muted">Kosongkan jika semua hari diizinkan.</small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_time">Jam Mulai Akses</label>
                            <input type="time" name="start_time" id="start_time" class="form-control" value="{{ old('start_time', $accessRule->start_time) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_time">Jam Selesai Akses</label>
                            <input type="time" name="end_time" id="end_time" class="form-control" value="{{ old('end_time', $accessRule->end_time) }}">
                        </div>
                    </div>
                </div>

                <hr>

                <button class="btn btn-primary" type="submit">Update Aturan</button>
                <a href="{{ route('access-rules.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection