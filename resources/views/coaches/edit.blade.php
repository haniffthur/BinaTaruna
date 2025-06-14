@extends('layouts.app')
@section('title', 'Edit coaches: ')
@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
        <a href="{{ route('coaches.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left fa-sm"></i> Kembali</a>
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            @if ($errors->any())<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form action="{{ route('coaches.update', $coach->id) }}" method="POST">
                @csrf
                @method('PUT')

                <h6 class="font-weight-bold text-primary">Data Diri coaches</h6>
                <div class="row">
                    <div class="col-md-6 form-group"><label>Nama Lengkap</label><input type="text" name="name" class="form-control" value="{{ old('name', $coach->name) }}" required></div>
                    <div class="col-md-6 form-group"><label>Posisi</label><input type="text" name="position" class="form-control" value="{{ old('position', $coach->position) }}" required></div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group"><label>No. Telepon</label><input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $coach->phone_number) }}"></div>
                    <div class="col-md-6 form-group"><label>Tanggal Bergabung</label><input type="date" name="join_date" class="form-control" value="{{ old('join_date', $coach->join_date) }}" required></div>
                </div>
                <hr>

                <h6 class="font-weight-bold text-primary">Kartu RFID</h6>
                <div class="form-group">
                    <label>Pilih Kartu RFID (Opsional)</label>
                    <select name="master_card_id" class="form-control">
                        <option value="">-- Tanpa Kartu --</option>
                        @foreach($availableCards as $card)
                            <option value="{{ $card->id }}" {{ old('master_card_id', $coach->master_card_id) == $card->id ? 'selected' : '' }}>{{ $card->card_uid }}</option>
                        @endforeach
                    </select>
                </div>
                <hr>

                <h6 class="font-weight-bold text-primary">Aturan Akses</h6>
                @php
                    $ruleType = old('rule_type', ($coach->access_rule_id !== null || (!$coach->max_taps_per_day && !$coach->allowed_days && !$coach->start_time)) ? 'template' : 'custom');
                @endphp
                <div class="form-group">
                    <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                        <label class="btn btn-outline-primary {{ $ruleType == 'template' ? 'active' : '' }}"><input type="radio" name="rule_type" value="template" {{ $ruleType == 'template' ? 'checked' : '' }}> Gunakan Template Aturan</label>
                        <label class="btn btn-outline-secondary {{ $ruleType == 'custom' ? 'active' : '' }}"><input type="radio" name="rule_type" value="custom" {{ $ruleType == 'custom' ? 'checked' : '' }}> Aturan Custom Manual</label>
                    </div>
                </div>

                <div id="form_template_rule">
                    <div class="form-group">
                        <label for="access_rule_id_template">Pilih Template Aturan</label>
                        <select name="access_rule_id" id="access_rule_id_template" class="form-control">
                            <option value="">-- Akses Default (Tanpa Batasan) --</option>
                            @foreach($accessRules as $rule)
                                <option value="{{ $rule->id }}" {{ old('access_rule_id', $coach->access_rule_id) == $rule->id ? 'selected' : '' }}>{{ $rule->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="form_custom_rule">
                    <p class="text-muted small">Isi kolom di bawah untuk mengubah aturan khusus hanya untuk coaches ini.</p>
                    <div class="row">
                        <div class="col-md-6 form-group"><label>Maksimal Tap per Hari</label><input type="number" name="max_taps_per_day" class="form-control" value="{{ old('max_taps_per_day', $coach->max_taps_per_day) }}" min="0"></div>
                        <div class="col-md-6 form-group"><label>Maksimal Tap per Bulan</label><input type="number" name="max_taps_per_month" class="form-control" value="{{ old('max_taps_per_month', $coach->max_taps_per_month) }}" min="0"></div>
                    </div>
                    <div class="form-group">
                        <label>Hari yang Diizinkan</label>
                        <div class="d-flex flex-wrap">
                            @php $selectedDays = old('allowed_days', $coach->allowed_days ?? []); @endphp
                            @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                            <div class="form-check form-check-inline mr-3">
                                <input class="form-check-input" type="checkbox" name="allowed_days[]" value="{{ $day }}" id="day_{{ $day }}" {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                                <label class="form-check-label text-capitalize" for="day_{{ $day }}">{{ $day }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label>Jam Mulai</label><input type="time" name="start_time" class="form-control" value="{{ old('start_time', $coach->start_time) }}"></div>
                        <div class="col-md-6 form-group"><label>Jam Selesai</label><input type="time" name="end_time" class="form-control" value="{{ old('end_time', $coach->end_time) }}"></div>
                    </div>
                </div>
                <hr>
                <button class="btn btn-primary" type="submit">Update coaches</button>
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
            } else {
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
