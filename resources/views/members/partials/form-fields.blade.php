{{--
|--------------------------------------------------------------------------
| Form Fields Partial for Members
|--------------------------------------------------------------------------
|
| File ini berisi semua field form yang dibutuhkan untuk membuat atau
| mengedit data member. Ini bisa digunakan ulang di berbagai halaman.
|
--}}

<h6 class="font-weight-bold text-primary">Data Diri Member</h6>
<div class="row">
    <div class="col-md-6 form-group">
        <label for="name">Nama Lengkap</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $member->name ?? '') }}" required>
    </div>
    <div class="col-md-6 form-group">
        <label for="nickname">Nama Panggilan (Opsional)</label>
        <input type="text" name="nickname" class="form-control" value="{{ old('nickname', $member->nickname ?? '') }}">
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-group">
        <label for="nis">NIS (Opsional)</label>
        <input type="text" name="nis" class="form-control" value="{{ old('nis', $member->nis ?? '') }}">
    </div>
    <div class="col-md-6 form-group">
        <label for="nisnas">NISNAS (Opsional)</label>
        <input type="text" name="nisnas" class="form-control" value="{{ old('nisnas', $member->nisnas ?? '') }}">
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-group">
        <label for="photo">Foto Profil (Opsional)</label>
        <input type="file" name="photo" class="form-control-file" accept="image/*">
        @if(isset($member) && $member->photo)
            <img src="{{ asset('storage/' . $member->photo) }}" alt="Foto" height="80" class="mt-2 img-thumbnail">
        @endif
    </div>
    <div class="col-md-6 form-group">
        <label for="phone_number">No. Telepon</label>
        <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $member->phone_number ?? '') }}">
    </div>
</div>
<div class="form-group">
    <label for="address">Alamat</label>
    <textarea name="address" class="form-control" rows="2">{{ old('address', $member->address ?? '') }}</textarea>
</div>
<div class="row">
    <div class="col-md-6 form-group">
        <label for="date_of_birth">Tanggal Lahir</label>
        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', ($member->date_of_birth ?? false) ? \Carbon\Carbon::parse($member->date_of_birth)->format('Y-m-d') : '') }}">
    </div>
    <div class="col-md-6 form-group">
        <label for="parent_name">Nama Orang Tua</label>
        <input type="text" name="parent_name" class="form-control" value="{{ old('parent_name', $member->parent_name ?? '') }}">
    </div>
</div>
<div class="form-group">
    <label for="join_date">Tanggal Bergabung</label>
    <input type="date" name="join_date" id="join_date" class="form-control" value="{{ old('join_date', ($member->join_date ?? false) ? \Carbon\Carbon::parse($member->join_date)->format('Y-m-d') : date('Y-m-d')) }}" required>
</div>

<hr>
<h6 class="font-weight-bold text-primary">Kartu & Aturan Akses</h6>

{{-- Bagian untuk memilih kartu RFID --}}
<div class="form-group">
    <label for="master_card_id">Pilih Kartu RFID (Opsional)</label>
    <select name="master_card_id" id="master_card_id_new" class="form-control">
        <option value="">-- Tanpa Kartu --</option>
        @foreach($availableCards as $card)
            <option value="{{ $card->id }}" {{ old('master_card_id', $member->master_card_id ?? '') == $card->id ? 'selected' : '' }}>
                {{-- PERBAIKAN: Menggunakan 'cardno' sesuai dengan controller Anda --}}
                {{ $card->cardno }}
            </option>
        @endforeach
    </select>
</div>

{{-- Bagian untuk aturan akses yang akan di-toggle oleh JavaScript --}}
<div id="access_rule_section_new_member" style="display: none;">
    <p class="small text-muted">Aturan akses ini hanya berlaku jika kartu RFID dipilih.</p>
    <div class="form-group">
        <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
            <label class="btn btn-outline-primary {{ old('rule_type', 'template') == 'template' ? 'active' : '' }}">
                <input type="radio" name="rule_type" value="template" id="rule_type_template_new" {{ old('rule_type', 'template') == 'template' ? 'checked' : '' }}> Gunakan Template
            </label>
            <label class="btn btn-outline-secondary {{ old('rule_type') == 'custom' ? 'active' : '' }}">
                <input type="radio" name="rule_type" value="custom" id="rule_type_custom_new" {{ old('rule_type') == 'custom' ? 'checked' : '' }}> Aturan Custom
            </label>
        </div>
    </div>
    <div id="form_template_rule_new" style="{{ old('rule_type', 'template') == 'template' ? '' : 'display: none;' }}">
        <div class="form-group">
            <label>Pilih Template Aturan</label>
            <select name="access_rule_id" class="form-control">
                <option value="">-- Akses Default (Tanpa Batasan) --</option>
                @foreach($accessRules as $rule)
                    <option value="{{ $rule->id }}" {{ old('access_rule_id') == $rule->id ? 'selected' : '' }}>{{ $rule->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div id="form_custom_rule_new" style="{{ old('rule_type') == 'custom' ? '' : 'display: none;' }}">
        <p class="small text-muted">Isi kolom di bawah untuk membuat aturan khusus hanya untuk member ini.</p>
        <div class="row">
            <div class="col-md-6 form-group"><label>Maksimal Tap per Hari</label><input type="number" name="max_taps_per_day" class="form-control" min="0" value="{{ old('max_taps_per_day') }}"></div>
            <div class="col-md-6 form-group"><label>Maksimal Tap per Bulan</label><input type="number" name="max_taps_per_month" class="form-control" min="0" value="{{ old('max_taps_per_month') }}"></div>
        </div>
        <div class="form-group">
            <label>Hari yang Diizinkan</label>
            <div class="d-flex flex-wrap">
                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                <div class="form-check form-check-inline mr-3">
                    <input class="form-check-input" type="checkbox" name="allowed_days[]" value="{{ $day }}" {{ is_array(old('allowed_days')) && in_array($day, old('allowed_days')) ? 'checked' : '' }}>
                    <label class="form-check-label text-capitalize">{{ $day }}</label>
                </div>
                @endforeach
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group"><label>Jam Mulai</label><input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}"></div>
            <div class="col-md-6 form-group"><label>Jam Selesai</label><input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}"></div>
        </div>
    </div>
</div>
