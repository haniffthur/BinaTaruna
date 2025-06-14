@extends('layouts.app')

@section('title', 'Tambah Kartu RFID Baru')

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
                    <label for="card_uid">UID Kartu</label>
                    {{-- Hapus placeholder lama, tambahkan readonly --}}
                    <input type="text" name="card_uid" id="card_uid" class="form-control" value="{{ old('card_uid') }}" placeholder="Menunggu tap kartu..." readonly required>
                    <small class="form-text text-muted">Tempelkan kartu pada reader. UID akan otomatis terisi di sini.</small>
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
    // Fungsi untuk mengambil UID dari server
    function getTappedUid() {
        fetch('/api/cards/get-tapped-uid') // Sesuaikan URL API jika berbeda
            .then(response => response.json())
            .then(data => {
                if (data.card_uid) {
                    document.getElementById('card_uid').value = data.card_uid;
                    alert('Kartu terdeteksi! UID: ' + data.card_uid + '\nSilakan pilih tipe kartu dan simpan.');
                    // Opsional: Jika Anda ingin langsung submit setelah UID terisi dan card_type sudah dipilih
                    // if (document.getElementById('card_type').value !== '') {
                    //     document.querySelector('form').submit();
                    // }
                }
            })
            .catch(error => {
                console.error('Error fetching tapped UID:', error);
            });
    }

    // Panggil fungsi getTappedUid setiap 2 detik
    setInterval(getTappedUid, 2000); // Cek setiap 2 detik

    // Atur placeholder saat halaman pertama kali dimuat
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('card_uid').placeholder = 'Menunggu tap kartu...';
    });

</script>
@endpush