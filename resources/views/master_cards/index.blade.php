@extends('layouts.app')

@section('title', 'Manajemen Kartu RFID')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('master-cards.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Kartu Baru
        </a>
    </div>

    {{-- Cards for summary data --}}
    <div class="row" id="summary-cards-container">
        {{-- Total Cards Card --}}
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Kartu RFID
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalCardsCount">{{ $totalCards }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Available Cards Card --}}
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Kartu Tersedia
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="availableCardsCount">{{ $availableCards }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Assigned Cards Card --}}
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Kartu Digunakan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="assignedCardsCount">{{ $assignedCards }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- End of Cards for summary data --}}

    {{-- Filter Form --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Kartu</h6>
        </div>
        <div class="card-body">
            <form id="filterForm" action="{{ route('master-cards.index') }}" method="GET" class="form-inline">
                <div class="form-group mb-2 mr-2">
                    <label for="search" class="sr-only">Cari UID Kartu</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Cari UID Kartu..." value="{{ $search }}">
                </div>
                <div class="form-group mb-2 mr-2">
                    <label for="card_type" class="sr-only">Tipe Kartu</label>
                    <select class="form-control" id="card_type" name="card_type">
                        <option value="">Semua Tipe</option>
                        <option value="member" {{ $cardType == 'member' ? 'selected' : '' }}>Member</option>
                        <option value="coach" {{ $cardType == 'coach' ? 'selected' : '' }}>Pelatih</option>
                        <option value="staff" {{ $cardType == 'staff' ? 'selected' : '' }}>Staff</option>
                    </select>
                </div>
                <div class="form-group mb-2 mr-2">
                    <label for="assignment_status" class="sr-only">Status</label>
                    <select class="form-control" id="assignment_status" name="assignment_status">
                        <option value="">Semua Status</option>
                        <option value="available" {{ $assignmentStatus == 'available' ? 'selected' : '' }}>Tersedia</option>
                        <option value="assigned" {{ $assignmentStatus == 'assigned' ? 'selected' : '' }}>Digunakan</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-info mb-2 mr-2">Filter</button>
                <a href="{{ route('master-cards.index') }}" class="btn btn-secondary mb-2">Reset</a>
            </form>
        </div>
    </div>
    {{-- End Filter Form --}}

    <div class="card shadow mb-4">
        <div class="card-body">
            {{-- Container untuk pesan alert AJAX --}}
            <div id="ajax-alerts-container">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>

            <div id="card-list-wrapper">
                {{-- Ini adalah tempat untuk tabel --}}
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>UID Kartu</th>
                                <th>Tipe Kartu</th>
                                <th>Status</th>
                                <th>Dibuat Pada</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="cardTableBody">
                            {{-- Baris tabel akan diisi di sini oleh JavaScript --}}
                        </tbody>
                    </table>
                </div>

                {{-- Ini adalah tempat untuk paginasi --}}
                <div class="mt-3" id="paginationLinks">
                    {{-- Paginasi akan diisi di sini oleh JavaScript --}}
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Fungsi untuk membuat baris tabel dari data kartu
        function createTableRow(card, index, firstItem) {
            let cardTypeBadge = '';
            if (card.card_type === 'member') {
                cardTypeBadge = '<span class="badge badge-primary">Member</span>';
            } else if (card.card_type === 'coach') {
                cardTypeBadge = '<span class="badge badge-info">Pelatih</span>';
            } else {
                cardTypeBadge = '<span class="badge badge-dark">Staff</span>';
            }

            let statusBadge = '';
            if (card.assignment_status === 'available') {
                statusBadge = '<span class="badge badge-success">Tersedia</span>';
            } else {
                statusBadge = '<span class="badge badge-warning">Digunakan</span>';
            }

            // Format tanggal created_at
            const createdAt = new Date(card.created_at).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });

            return `
                <tr>
                    <td>${firstItem + index}</td>
                    <td><span class="font-weight-bold">${card.cardno}</span></td>
                    <td>${cardTypeBadge}</td>
                    <td>${statusBadge}</td>
                    <td>${createdAt}</td>
                    <td>
                        <a href="/master-cards/${card.id}/edit" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="/master-cards/${card.id}" method="POST" class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            `;
        }

        // Fungsi untuk menampilkan pesan alert AJAX
        function showAjaxAlert(message, type) {
            // Hapus alert yang sudah ada terlebih dahulu
            $('#ajax-alerts-container').empty();

            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            $('#ajax-alerts-container').append(alertHtml);

            // Sembunyikan alert setelah 2 detik
            setTimeout(function() {
                $('#ajax-alerts-container .alert').alert('close');
            }, 2000); // 2000 milidetik = 2 detik
        }

        // Fungsi untuk memuat ulang tabel kartu menggunakan AJAX
        function loadCardTable(url, showLoading = true) {
            if (showLoading) {
                $('#cardTableBody').html('<tr><td colspan="6" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-2">Memuat data...</p></td></tr>');
                $('#paginationLinks').empty(); // Kosongkan paginasi saat memuat
            }

            $.ajax({
                url: url,
                type: 'GET',
                data: $('#filterForm').serialize(), // Kirim data form filter
                dataType: 'json', // Harapkan respons JSON
                success: function(response) {
                    let tableBodyHtml = '';
                    if (response.data.length > 0) {
                        let currentPage = response.current_page || 1;
                        let perPage = response.per_page || 20;
                        let firstItemCalculated = (currentPage - 1) * perPage + 1;

                        $.each(response.data, function(index, card) {
                            tableBodyHtml += createTableRow(card, index, firstItemCalculated);
                        });
                    } else {
                        tableBodyHtml = '<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data kartu</td></tr>';
                    }
                    $('#cardTableBody').html(tableBodyHtml);
                    $('#paginationLinks').html(response.links); // Sisipkan HTML paginasi

                    // Perbarui summary cards jika data summary dikirim
                    if (response.summary) {
                        $('#totalCardsCount').text(response.summary.total);
                        $('#availableCardsCount').text(response.summary.available);
                        $('#assignedCardsCount').text(response.summary.assigned);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", status, error);
                    $('#cardTableBody').html('<tr><td colspan="6" class="text-center"><div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div></td></tr>');
                    $('#paginationLinks').empty();
                }
            });
        }

        // --- Event Handlers ---

        // Pemuatan awal tabel saat halaman dimuat
        loadCardTable("{{ route('master-cards.index') }}", false);

        // Tangani submit form filter
        $('#filterForm').submit(function(e) {
            e.preventDefault();
            loadCardTable($(this).attr('action'));
        });

        // Tangani perubahan pada dropdown filter
        $('#card_type, #assignment_status').change(function() {
            $('#filterForm').submit();
        });

        // Tangani klik pada link paginasi (Delegated Event Handling)
        $(document).on('click', '#paginationLinks .pagination a', function(e) {
            e.preventDefault();
            loadCardTable($(this).attr('href'));
        });

        // Tangani klik pada tombol delete (Delegated Event Handling)
        $(document).on('submit', '.delete-form', function(e) {
            e.preventDefault();
            const form = $(this);

            if (confirm('Yakin ingin hapus kartu ini? Jika kartu sedang digunakan, pemiliknya akan kehilangan akses kartu.')) {
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST', // Laravel uses POST for DELETE with _method
                    data: form.serialize(), // Includes _token and _method=DELETE
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showAjaxAlert(response.success, 'success'); // Tampilkan alert sukses
                            loadCardTable("{{ route('master-cards.index') }}"); // Muat ulang tabel setelah berhasil
                        } else {
                            // Ini seharusnya tidak terjadi jika backend mengembalikan 4xx untuk error
                            // Tapi sebagai fallback jika response sukses tapi tidak ada 'success'
                            showAjaxAlert('Operasi berhasil, namun ada masalah dalam respons.', 'warning');
                            loadCardTable("{{ route('master-cards.index') }}");
                        }
                    },
                    error: function(xhr) {
                        const errorResponse = xhr.responseJSON;
                        let errorMessage = 'Terjadi kesalahan saat menghapus kartu.';
                        if (errorResponse && errorResponse.error) {
                            errorMessage = errorResponse.error;
                        }
                        showAjaxAlert(errorMessage, 'danger'); // Tampilkan alert error
                        console.error("AJAX Delete Error: ", xhr.responseText);
                    }
                });
            }
        });
    });
</script>
@endpush