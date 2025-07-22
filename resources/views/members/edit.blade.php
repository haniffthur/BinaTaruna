@extends('layouts.app')
@section('title', 'Edit Member: ' . $member->name)
@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('members.index') }}" class="btn btn-secondary btn-sm shadow-sm"><i class="fas fa-arrow-left fa-sm"></i> Kembali</a>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> Terdapat beberapa masalah dengan input Anda.<br><br>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            
            {{-- Tambahkan blok untuk flash messages (success, error, warning) --}}
            @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ $message }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            @if ($message = Session::get('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $message }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            @if ($message = Session::get('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ $message }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
            {{-- Akhir blok flash messages --}}
            
            <form action="{{ route('members.update', $member->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <h6 class="font-weight-bold text-primary">Data Diri Member</h6>
                
                <div class="row">
                    {{-- Kolom Kiri untuk Form Data Teks --}}
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name">Nama Lengkap</label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $member->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="nickname">Nama Panggilan (Opsional)</label>
                                <input type="text" name="nickname" id="nickname" class="form-control @error('nickname') is-invalid @enderror" value="{{ old('nickname', $member->nickname) }}">
                                @error('nickname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="nis">NIS (Opsional)</label>
                                <input type="text" name="nis" id="nis" class="form-control @error('nis') is-invalid @enderror" value="{{ old('nis', $member->nis) }}">
                                @error('nis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="nisnas">NISNAS (Opsional)</label>
                                <input type="text" name="nisnas" id="nisnas" class="form-control @error('nisnas') is-invalid @enderror" value="{{ old('nisnas', $member->nisnas) }}">
                                @error('nisnas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Alamat</label>
                            <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $member->address) }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="date_of_birth">Tanggal Lahir</label>
                                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', $member->date_of_birth ? $member->date_of_birth->format('Y-m-d') : '') }}">
                                @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="parent_name">Nama Orang Tua</label>
                                <input type="text" name="parent_name" id="parent_name" class="form-control @error('parent_name') is-invalid @enderror" value="{{ old('parent_name', $member->parent_name) }}">
                                @error('parent_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="join_date">Tanggal Bergabung</label>
                                <input type="date" name="join_date" id="join_date" class="form-control @error('join_date') is-invalid @enderror" value="{{ old('join_date', $member->join_date ? $member->join_date->format('Y-m-d') : '') }}" required>
                                @error('join_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Kelas</label>
                                <select name="school_class_id" class="form-control @error('school_class_id') is-invalid @enderror">
                                    <option value="">-- Tidak Masuk Kelas Apapun --</option>
                                    @foreach($schoolClasses as $class)
                                        <option value="{{ $class->id }}" {{ old('school_class_id', $member->school_class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                @error('school_class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan untuk Foto Profil --}}
                    <div class="col-md-4">
                        <div class="form-group text-center">
                            <label>Foto Profil</label>
                            <div class="mb-2">
                                <img src="{{ $member->photo ? asset('storage/' . $member->photo) : 'https://via.placeholder.com/150' }}" id="photo-preview" alt="Foto Profil" class="img-fluid img-thumbnail" style="max-height: 200px;">
                            </div>
                            <input type="file" name="photo" id="photo" class="form-control-file d-inline-block @error('photo') is-invalid @enderror">
                            <small class="form-text text-muted">Pilih file baru untuk mengganti foto.</small>
                            @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror

                            {{-- Tombol hapus foto (opsional) --}}
                            @if($member->photo)
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="remove-photo-btn">Hapus Foto</button>
                                <input type="hidden" name="photo_removed" id="photo_removed" value="false">
                            @endif
                        </div>
                    </div>
                </div>

                <hr>

                <h6 class="font-weight-bold text-primary">Kartu & Aturan Akses</h6>
                
                <div class="form-group">
                    <label for="master_card_id">Pilih Kartu RFID</label>
                    <select name="master_card_id" id="master_card_id" class="form-control @error('master_card_id') is-invalid @enderror" required>
                        @forelse($availableCards as $card)
                            <option value="{{ $card->id }}" {{ old('master_card_id', $member->master_card_id) == $card->id ? 'selected' : '' }}>
                                {{ $card->cardno }}
                                @if($card->id == $member->master_card_id) (Kartu Saat Ini) @endif
                            </option>
                        @empty
                            <option value="" disabled>Tidak ada kartu yang tersedia. Harap tambah kartu baru terlebih dahulu.</option>
                        @endforelse
                    </select>
                    @error('master_card_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                {{-- Sisa form untuk aturan akses --}}
                <div class="form-group">
                    <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                        <label class="btn btn-outline-primary {{ old('rule_type', $member->rule_type) == 'template' ? 'active' : '' }}"><input type="radio" name="rule_type" value="template" {{ old('rule_type', $member->rule_type) == 'template' ? 'checked' : '' }}> Gunakan Template Aturan</label>
                        <label class="btn btn-outline-secondary {{ old('rule_type', $member->rule_type) == 'custom' ? 'active' : '' }}"><input type="radio" name="rule_type" value="custom" {{ old('rule_type', $member->rule_type) == 'custom' ? 'checked' : '' }}> Aturan Custom Manual</label>
                    </div>
                    @error('rule_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror {{-- d-block untuk radio/checkbox --}}
                </div>

                <div id="form_template_rule">
                    <div class="form-group">
                        <label>Pilih Template Aturan</label>
                        <select name="access_rule_id" class="form-control @error('access_rule_id') is-invalid @enderror">
                            <option value="">-- Akses Default (Tanpa Batasan) --</option>
                            @foreach($accessRules as $rule)<option value="{{ $rule->id }}" {{ old('access_rule_id', $member->access_rule_id) == $rule->id ? 'selected' : '' }}>{{ $rule->name }}</option>@endforeach
                        </select>
                        @error('access_rule_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div id="form_custom_rule">
                    <p class="text-muted small">Isi kolom di bawah untuk membuat aturan khusus hanya untuk member ini.</p>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Maksimal Tap per Hari</label>
                            <input type="number" name="max_taps_per_day" class="form-control @error('max_taps_per_day') is-invalid @enderror" value="{{ old('max_taps_per_day', $member->max_taps_per_day) }}" min="0">
                            @error('max_taps_per_day')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Maksimal Tap per Bulan</label>
                            <input type="number" name="max_taps_per_month" class="form-control @error('max_taps_per_month') is-invalid @enderror" value="{{ old('max_taps_per_month', $member->max_taps_per_month) }}" min="0">
                            @error('max_taps_per_month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Hari yang Diizinkan</label>
                        <div class="d-flex flex-wrap">
                            @php
                                // --- PERBAIKAN DI SINI ---
                                $selectedDays = old('allowed_days', $member->allowed_days);
                                
                                // Pastikan $selectedDays adalah array
                                if (is_string($selectedDays)) {
                                    $decodedDays = json_decode($selectedDays, true);
                                    $selectedDays = is_array($decodedDays) ? $decodedDays : [];
                                } elseif (!is_array($selectedDays)) {
                                    $selectedDays = []; // Jika bukan array atau string valid, jadikan array kosong
                                }
                                // --- AKHIR PERBAIKAN ---
                            @endphp
                            @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                            <div class="form-check form-check-inline mr-3">
                                <input class="form-check-input @error('allowed_days') is-invalid @enderror" type="checkbox" name="allowed_days[]" value="{{ $day }}" id="day_{{ $day }}" {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                                <label class="form-check-label text-capitalize" for="day_{{ $day }}">{{ $day }}</label>
                            </div>
                            @endforeach
                            {{-- Error message untuk allowed_days (jika ada) --}}
                            @error('allowed_days')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Jam Mulai</label>
                            <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', $member->start_time ? \Carbon\Carbon::parse($member->start_time)->format('H:i') : '') }}">
                            @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Jam Selesai</label>
                            <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', $member->end_time ? \Carbon\Carbon::parse($member->end_time)->format('H:i') : '') }}">
                            @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                
                <hr>
                <button class="btn btn-primary" type="submit">Update Member</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
{{-- JavaScript Anda untuk toggle aturan dan preview foto --}}
<script>
    // Script untuk toggle form aturan
    $(document).ready(function() {
        function toggleRuleForms(type) {
            if (type === 'template') {
                $('#form_template_rule').show();
                $('#form_custom_rule').hide();
            } else { // type === 'custom'
                $('#form_template_rule').hide();
                $('#form_custom_rule').show();
            }
        }
        var initialType = $('input[name="rule_type"]:checked').val();
        toggleRuleForms(initialType);
        $('input[name="rule_type"]').change(function() {
            toggleRuleForms($(this).val());
        });

        // Script untuk tombol "Hapus Foto"
        $('#remove-photo-btn').on('click', function() {
            if (confirm('Anda yakin ingin menghapus foto profil ini?')) {
                $('#photo-preview').attr('src', 'https://via.placeholder.com/150'); // Ganti dengan placeholder
                $('#photo').val(''); // Kosongkan input file
                $('#photo_removed').val('true'); // Set nilai hidden input untuk menandai penghapusan
                $(this).hide(); // Sembunyikan tombol hapus
            }
        });
    });

    // Script baru untuk live preview gambar
    document.getElementById('photo').addEventListener('change', function(event) {
        if (event.target.files && event.target.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photo-preview').src = e.target.result;
            }
            reader.readAsDataURL(event.target.files[0]);
            // Jika ada tombol hapus foto, pastikan disembunyikan dan photo_removed direset
            $('#remove-photo-btn').hide();
            $('#photo_removed').val('false');
        }
    });
</script>
@endpush