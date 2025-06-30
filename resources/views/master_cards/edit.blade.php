@extends('layouts.app')

@section('title', 'Edit Kartu RFID')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('master-cards.index') }}" class="btn btn-secondary btn-sm shadow-sm">
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

            <form action="{{ route('master-cards.update', $masterCard->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="cardno">UID Kartu</label>
                    <input type="text" name="cardno" id="cardno" class="form-control" value="{{ old('cardno', $masterCard->cardno) }}" required>
                </div>

                <div class="form-group">
                    <label for="card_type">Tipe Kartu</button></label>
                    <select name="card_type" id="card_type" class="form-control" required>
                        <option value="member" {{ old('card_type', $masterCard->card_type) == 'member' ? 'selected' : '' }}>Member</option>
                        <option value="coach" {{ old('card_type', $masterCard->card_type) == 'coach' ? 'selected' : '' }}>Pelatih</option>
                        <option value="staff" {{ old('card_type', $masterCard->card_type) == 'staff' ? 'selected' : '' }}>Staff</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="assignment_status">Status Penggunaan</label>
                    <select name="assignment_status" id="assignment_status" class="form-control" required>
                        <option value="available" {{ old('assignment_status', $masterCard->assignment_status) == 'available' ? 'selected' : '' }}>Tersedia (Available)</option>
                        <option value="assigned" {{ old('assignment_status', $masterCard->assignment_status) == 'assigned' ? 'selected' : '' }}>Digunakan (Assigned)</option>
                    </select>
                    <small class="form-text text-muted">Mengubah ke 'Tersedia' akan melepas kartu dari pemilik saat ini.</small>
                </div>

                <hr>

                <button class="btn btn-primary" type="submit">Update Kartu</button>
                <a href="{{ route('master-cards.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection