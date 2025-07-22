@extends('layouts.app')
@section('title', 'Transaksi Kelas Member')

@push('styles')
<style>
    /* Styling untuk membuat kartu detail terlihat lebih bagus */
    .member-details-card {
        background-color: #f8f9fc;
        border-left: 4px solid #4e73df;
    }
    #update_rules_form {
        border-left: 4px solid #f6c23e;
    }
</style>
@endpush

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">@yield('title')</h1>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Form Transaksi Member</h6></div>
        <div class="card-body">
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> Terdapat beberapa masalah dengan input Anda.<br><br>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('transactions.member.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label>Tipe Transaksi</label>
                    <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                        <label class="btn btn-outline-primary {{ old('transaction_type', 'lama') == 'lama' ? 'active' : '' }}">
                            <input type="radio" name="transaction_type" value="lama" {{ old('transaction_type', 'lama') == 'lama' ? 'checked' : '' }}> Member Lama
                        </label>
                        <label class="btn btn-outline-secondary {{ old('transaction_type') == 'baru' ? 'active' : '' }}">
                            <input type="radio" name="transaction_type" value="baru" {{ old('transaction_type') == 'baru' ? 'checked' : '' }}> Daftarkan Member Baru
                        </label>
                    </div>
                </div>

                {{-- Opsi untuk Member Lama --}}
                <div id="form_member_lama" style="{{ old('transaction_type', 'lama') == 'lama' ? '' : 'display: none;' }}">
                    <div class="form-group">
                        <label for="member_id">Pilih Member</label>
                        <select name="member_id" id="member_id" class="form-control">
                            <option value="">-- Pilih Member --</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Area ini akan diisi oleh JavaScript -->
                    <div id="member_details_section" class="card member-details-card mb-3" style="display: none;"></div>

                    <!-- Form untuk mengupdate aturan akses member lama (awalnya tersembunyi) -->
                    <div id="update_access_rule_section" style="display: none;">
                        <hr>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="update_rules" value="1" id="update_rules_checkbox">
                            <label class="form-check-label font-weight-bold text-primary" for="update_rules_checkbox">
                                Ubah Aturan Akses Member Ini?
                            </label>
                        </div>
                        <div id="update_rules_form" class="p-3 border rounded bg-white" style="display: none;">
                            {{-- Konten form ini akan diisi oleh JavaScript --}}
                        </div>
                    </div>
                </div>

                {{-- Form untuk Member Baru --}}
                <div id="form_member_baru" style="{{ old('transaction_type') == 'baru' ? '' : 'display: none;' }}">
                    {{-- File partial ini berisi semua field pendaftaran member --}}
                    @include('members.partials.form-fields', ['member' => new \App\Models\Member])
                </div>
                <hr>

                {{-- Bagian Transaksi Inti --}}
                <h6 class="font-weight-bold text-primary">Detail Transaksi</h6>
                <div class="form-group">
                    <label for="class_id">Pilih Kelas</label>
                    <select name="class_id" id="class_id" class="form-control" required>
                        <option value="" data-price="0">-- Pilih Kelas --</option>
                        @foreach($schoolClasses as $class)
                            <option value="{{ $class->id }}" data-price="{{ $class->price }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }} - Rp {{ number_format($class->price) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group"><label>Total Harga</label><input type="text" id="total_price_display" class="form-control bg-light" value="Rp 0" readonly></div>
                    <div class="col-md-4 form-group"><label for="amount_paid">Jumlah Bayar</label><input type="number" id="amount_paid" name="amount_paid" class="form-control" required min="0" value="{{ old('amount_paid') }}"></div>
                    <div class="col-md-4 form-group"><label>Kembalian</label><input type="text" id="change_display" class="form-control bg-light" value="Rp 0" readonly></div>
                </div>

                <button type="submit" class="btn btn-success">Proses Transaksi</button>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        // --- CACHE ELEMEN-ELEMEN PENTING ---
        const memberForm = $('#form_member_lama');
        const newMemberForm = $('#form_member_baru');
        const transactionTypeRadios = $('input[name="transaction_type"]');
        const memberSelect = $('#member_id');
        const detailSection = $('#member_details_section');
        const updateRuleSection = $('#update_access_rule_section');
        const updateRulesCheckbox = $('#update_rules_checkbox');
        const updateRulesForm = $('#update_rules_form');
        const classSelect = $('#class_id');
        const amountPaidInput = $('#amount_paid');
        const totalPriceDisplay = $('#total_price_display');
        const changeDisplay = $('#change_display');

        // PENTING: TAMBAHKAN DEFINISI VARIABEL INI DI SINI!
        const newMemberMasterCardSelect = $('#master_card_id_new');
        const newMemberAccessRuleSection = $('#access_rule_section_new_member');
        // --- AKHIR CACHE ELEMEN-ELEMEN PENTING ---


        // 1. Fungsi untuk beralih antara form Member Lama dan Member Baru
        function toggleMemberForms(type) {
            if (type === 'lama') {
                memberForm.show();
                newMemberForm.hide();
                memberSelect.prop('required', true);
               
            } else { // 'baru'
                memberForm.hide();
                detailSection.hide();
                updateRuleSection.hide();
                newMemberForm.show();
                memberSelect.prop('required', false).val('');
                newMemberForm.find('input[name="name"]').prop('required', true);
            }
        }

        // 2. Fungsi untuk mengisi dan menampilkan form update aturan akses
        function populateAndShowUpdateForm(data) {
            const accessRulesData = {!! json_encode($accessRules) !!};
            let templateOptions = `<option value="">-- Akses Default --</option>`;
            accessRulesData.forEach(rule => {
                const isSelected = data.access_rule_id == rule.id ? 'selected' : '';
                templateOptions += `<option value="${rule.id}" ${isSelected}>${rule.name}</option>`;
            });
            const days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
            let daysCheckboxes = '';
            days.forEach(day => {
                const isChecked = data.allowed_days && data.allowed_days.includes(day) ? 'checked' : '';
                daysCheckboxes += `<div class="form-check form-check-inline mr-3"><input class="form-check-input" type="checkbox" name="update_allowed_days[]" value="${day}" ${isChecked}><label class="form-check-label text-capitalize">${day}</label></div>`;
            });
            const ruleTypeTemplateChecked = (data.rule_type === 'template') ? 'checked' : '';
            const ruleTypeCustomChecked = (data.rule_type === 'custom') ? 'checked' : '';
            const ruleTypeTemplateActive = (data.rule_type === 'template') ? 'active' : '';
            const ruleTypeCustomActive = (data.rule_type === 'custom') ? 'active' : '';

            const formTemplateDisplay = (data.rule_type === 'template') ? '' : 'display:none;';
            const formCustomDisplay = (data.rule_type === 'custom') ? '' : 'display:none;';

            const formHtml = `
                <div class="form-group">
                    <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                        <label class="btn btn-outline-primary ${ruleTypeTemplateActive}"><input type="radio" name="update_rule_type" value="template" ${ruleTypeTemplateChecked}> Template</label>
                        <label class="btn btn-outline-secondary ${ruleTypeCustomActive}"><input type="radio" name="update_rule_type" value="custom" ${ruleTypeCustomChecked}> Custom</label>
                    </div>
                </div>
                <div id="form_template_rule_update" style="${formTemplateDisplay}">
                    <div class="form-group"><label>Pilih Template</label><select name="update_access_rule_id" class="form-control">${templateOptions}</select></div>
                </div>
                <div id="form_custom_rule_update" style="${formCustomDisplay}">
                    <p class="small text-muted">Isi aturan custom baru.</p>
                    <div class="row"><div class="col-md-6 form-group"><label>Maks. Tap/Hari</label><input type="number" name="update_max_taps_per_day" class="form-control" value="${data.max_taps_per_day || ''}" min="0"></div><div class="col-md-6 form-group"><label>Maks. Tap/Bulan</label><input type="number" name="update_max_taps_per_month" class="form-control" value="${data.max_taps_per_month || ''}" min="0"></div></div>
                    <div class="form-group"><label>Hari</label><div class="d-flex flex-wrap">${daysCheckboxes}</div></div>
                    <div class="row"><div class="col-md-6 form-group"><label>Jam Mulai</label><input type="time" name="update_start_time" class="form-control" value="${data.start_time || ''}"></div><div class="col-md-6 form-group"><label>Jam Selesai</label><input type="time" name="update_end_time" class="form-control" value="${data.end_time || ''}"></div></div>
                </div>`;
            updateRulesForm.html(formHtml).slideDown();
        }
        function toggleUpdateRuleForms(type) {
             if (type === 'template') {
                $('#form_template_rule_update').show();
                $('#form_custom_rule_update').hide();
            } else {
                $('#form_template_rule_update').hide();
                $('#form_custom_rule_update').show();
            }
        }
        // --- LOGIKA BARU DAN DIPERBAIKI ---

        // Untuk form member BARU
        newMemberMasterCardSelect.change(function() {
            if ($(this).val()) { // If a card is selected (value is not empty)
                newMemberAccessRuleSection.slideDown();
            } else {
                newMemberAccessRuleSection.slideUp();
                // Reset rule type to template and clear selections/inputs when no card is selected
                newMemberAccessRuleSection.find('input[name="rule_type"][value="template"]').prop('checked', true).change();
                newMemberAccessRuleSection.find('select[name="access_rule_id"]').val('');
                newMemberAccessRuleSection.find('input[type="number"], input[type="time"]').val('');
                newMemberAccessRuleSection.find('input[type="checkbox"]').prop('checked', false);
            }
        });
        $('input[name="rule_type"]').change(function() {
            if ($(this).val() === 'template') {
                $('#form_template_rule_new').show();
                $('#form_custom_rule_new').hide();
            } else {
                $('#form_template_rule_new').hide();
                $('#form_custom_rule_new').show();
            }
        });
        function calculatePrice() {
            const selectedOption = classSelect.find('option:selected');
            const price = parseFloat(selectedOption.data('price')) || 0;
            const amountPaid = parseFloat(amountPaidInput.val()) || 0;
            const change = amountPaid - price;
            const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
            totalPriceDisplay.val(formatter.format(price));
            changeDisplay.val(formatter.format(Math.max(0, change)));
        }

        // Fungsi populateAndShowUpdateForm sudah dipindahkan dan diperbaiki di atas.
        // Tidak perlu diduplikasi di sini.

        // Fungsi toggleUpdateRuleForms sudah dipindahkan dan diperbaiki di atas.
        // Tidak perlu diduplikasi di sini.

        // --- EVENT LISTENERS ---
        transactionTypeRadios.change(function() { toggleMemberForms($(this).val()); });
        classSelect.add(amountPaidInput).on('change input', calculatePrice);

        memberSelect.change(function() {
            const memberId = $(this).val();
            if (!memberId) {
                detailSection.slideUp();
                updateRuleSection.slideUp();
                return;
            }

            detailSection.html('<div class="card-body">Memuat data...</div>').slideDown();
            updateRuleSection.hide();
            updateRulesCheckbox.prop('checked', false);
            updateRulesForm.hide().empty();

            const url = `{{ url('/api/members') }}/${memberId}`;

            fetch(url).then(response => response.json()).then(data => {
                const memberDetailsHtml = `<div class="card-body"><div class="row"><div class="col-md-3 text-center align-self-center"><img src="${data.photo_url}" class="img-fluid rounded" style="max-height: 120px;"></div><div class="col-md-9"><h5 class="font-weight-bold">${data.name}</h5><p class="mb-1"><strong>Kelas:</strong> <span>${data.class_name}</span></p><p class="mb-1"><strong>Kartu RFID:</strong> <span class="badge badge-info">${data.card_uid}</span></p><p class="mb-0"><strong>Aturan Akses:</strong> <span>${data.access_rule}</span></p></div></div></div>`;
                detailSection.html(memberDetailsHtml);
                if(data.card_uid !== 'Tidak ada kartu') {
                    updateRuleSection.slideDown().data('rules', data);
                }
            }).catch(error => {
                detailSection.html(`<div class="card-body text-danger">Gagal memuat data.</div>`);
            });
        });
        updateRulesCheckbox.change(function() {
            if ($(this).is(':checked')) {
                const data = updateRuleSection.data('rules');
                if (data) populateAndShowUpdateForm(data);
            } else {
                updateRulesForm.slideUp().empty();
            }
        });
        $(document).on('change', '#update_rules_form input[name="update_rule_type"]', function() {
            toggleUpdateRuleForms($(this).val());
        });

        // --- INISIALISASI HALAMAN PADA SAAT DIMUAT ---
        // Jika ada kesalahan validasi atau halaman dimuat ulang dengan tipe 'baru' yang terpilih
        if (transactionTypeRadios.filter(':checked').val() === 'baru') {
            // Kita sudah set display di Blade, jadi tinggal trigger event change untuk aturan akses
            newMemberMasterCardSelect.trigger('change');
        } else {
            // Jika 'lama' yang terpilih (default atau dari old())
            // Trigger member select change if a member was old-selected
            if(memberSelect.val()) {
                memberSelect.trigger('change');
            }
        }
        calculatePrice(); // Always calculate price on load
    });
</script>
@endpush
