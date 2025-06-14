@extends('layouts.app')
@section('title', 'Tambah Pelatih Baru')
@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('coaches.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> Terdapat beberapa masalah dengan input Anda.<br><br>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <form action="{{ route('coaches.store') }}" method="POST">
                @csrf
                <h6 class="font-weight-bold text-primary">Data Diri Pelatih</h6>
                <div class="row">
                    <div class="col-md-6 form-group"><label for="name">Nama Lengkap</label><input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required></div>
                    <div class="col-md-6 form-group"><label for="phone_number">No. Telepon</label><input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ old('phone_number') }}"></div>
                </div>
                <div class="form-group"><label for="address">Alamat</label><textarea name="address" id="address" class="form-control" rows="2">{{ old('address') }}</textarea></div>
                <div class="row">
                    <div class="col-md-6 form-group"><label for="specialization">Spesialisasi</label><input type="text" name="specialization" id="specialization" class="form-control" value="{{ old('specialization') }}"></div>
                    <div class="col-md-6 form-group"><label for="join_date">Tanggal Bergabung</label><input type="date" name="join_date" id="join_date" class="form-control" value="{{ old('join_date', date('Y-m-d')) }}" required></div>
                </div>
                <hr>

                <h6 class="font-weight-bold text-primary">Kartu RFID</h6>
                <div class="form-group">
                    <label for="master_card_id">Pilih Kartu RFID</label>
                    <select name="master_card_id" id="master_card_id" class="form-control" required>
                        <option value="">-- Pilih Kartu yang Tersedia --</option>
                        @foreach($availableCards as $card)
                            <option value="{{ $card->id }}" {{ old('master_card_id') == $card->id ? 'selected' : '' }}>{{ $card->card_uid }}</option>
                        @endforeach
                    </select>
                </div>
                <hr>

                <h6 class="font-weight-bold text-primary">Aturan Akses</h6>
                <div class="form-group">
                    <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                        <label class="btn btn-outline-primary {{ old('rule_type', 'template') == 'template' ? 'active' : '' }}"><input type="radio" name="rule_type" value="template" {{ old('rule_type', 'template') == 'template' ? 'checked' : '' }}> Gunakan Template Aturan</label>
                        <label class="btn btn-outline-secondary {{ old('rule_type') == 'custom' ? 'active' : '' }}"><input type="radio" name="rule_type" value="custom" {{ old('rule_type') == 'custom' ? 'checked' : '' }}> Aturan Custom Manual</label>
                    </div>
                </div>

                <div id="form_template_rule">
                    <div class="form-group">
                        <label for="access_rule_id">Pilih Template Aturan</label>
                        <select name="access_rule_id" id="access_rule_id" class="form-control">
                            <option value="">-- Akses Default (Tanpa Batasan) --</option>
                            @foreach($accessRules as $rule)
                                <option value="{{ $rule->id }}" {{ old('access_rule_id') == $rule->id ? 'selected' : '' }}>{{ $rule->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="form_custom_rule">
                    <p class="text-muted small">Isi kolom di bawah untuk membuat aturan khusus hanya untuk pelatih ini.</p>
                    <div class="row">
                        <div class="col-md-6 form-group"><label>Maksimal Tap per Hari</label><input type="number" name="max_taps_per_day" class="form-control" value="{{ old('max_taps_per_day') }}" min="0" placeholder="Kosongkan jika tak terbatas"></div>
                        <div class="col-md-6 form-group"><label>Maksimal Tap per Bulan</label><input type="number" name="max_taps_per_month" class="form-control" value="{{ old('max_taps_per_month') }}" min="0" placeholder="Kosongkan jika tak terbatas"></div>
                    </div>
                    <div class="form-group">
                        <label>Hari yang Diizinkan (kosongkan jika semua hari boleh)</label>
                        <div class="d-flex flex-wrap">
                            @php $old_days = old('allowed_days', []); @endphp
                            @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                            <div class="form-check form-check-inline mr-3">
                                <input class="form-check-input" type="checkbox" name="allowed_days[]" value="{{ $day }}" id="day_{{ $day }}" {{ in_array($day, $old_days) ? 'checked' : '' }}>
                                <label class="form-check-label text-capitalize" for="day_{{ $day }}">{{ $day }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label>Jam Mulai</label><input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}"></div>
                        <div class="col-md-6 form-group"><label>Jam Selesai</label><input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}"></div>
                    </div>
                </div>
                <hr>
                <button class="btn btn-primary" type="submit">Simpan Pelatih</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
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
    });
</script>
@endpush