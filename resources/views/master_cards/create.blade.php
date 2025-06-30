@extends('layouts.app')

@section('title', 'Tambah Kartu RFID Baru')

{{-- Style notifikasi bisa dihapus karena kita tidak lagi menggunakannya --}}

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

            <form action="{{ route('master-cards.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="cardno">UID Kartu</label>
                    {{-- Hapus 'readonly' dan ubah placeholder agar lebih instruktif --}}
                    <input type="text" name="cardno" id="cardno" class="form-control" value="{{ old('cardno') }}" placeholder="Klik di sini, lalu tap kartu pada USB reader" required autocomplete="off">
                    <small class="form-text text-muted">Input ini akan terisi otomatis saat Anda men-tap kartu.</small>
                </div>

                <div class="form-group">
                    <label for="card_type">Tipe Kartu</label>
                    <select name="card_type" id="card_type" class="form-control" required>
                        <option value="">-- Pilih Peruntukan Kartu --</option>
                        <option value="member" {{ old('card_type') == 'member' ? 'selected' : '' }}>Member</option>
                        <option value="coach" {{ old('card_type') == 'coach' ? 'selected' : '' }}>Pelatih</option>
                        <option value="staff" {{ old('card_type') == 'staff' ? 'selected' : '' }}>Staff</option>
                    </select>
                </div>
                
                <hr>

                <button class="btn btn-primary" type="submit">Simpan Kartu</button>
                <a href="{{ route('master-cards.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cardUidInput = document.getElementById('cardno');
    const cardTypeSelect = document.getElementById('card_type');

    // Tambahkan event listener untuk input UID
    cardUidInput.addEventListener('keydown', function(event) {
        // Cek jika tombol yang ditekan adalah "Enter"
        // Sebagian besar USB reader mengirimkan "Enter" setelah UID
        if (event.key === 'Enter') {
            // 1. Mencegah form tersubmit secara otomatis
            event.preventDefault();
            
            // 2. Pindahkan fokus ke field berikutnya (Tipe Kartu)
            // Ini menciptakan alur kerja yang sangat cepat
            cardTypeSelect.focus();
            
            console.log('UID terdeteksi, fokus dipindahkan ke Tipe Kartu.');
        }
    });

    // Beri fokus awal ke field UID saat halaman dimuat
    cardUidInput.focus();
});
</script>
@endpush
