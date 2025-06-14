@extends('layouts.app')
@section('title', 'Transaksi Kelas Member')
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
                <div id="form_member_lama">
                    <div class="form-group">
                        <label for="member_id">Pilih Member</label>
                        <select name="member_id" id="member_id" class="form-control">
                            <option value="">-- Pilih Member yang Sudah Terdaftar --</option>
                            @foreach($members as $member)<option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>@endforeach
                        </select>
                    </div>
                </div>

                {{-- Form untuk Member Baru (disembunyikan secara default) --}}
                <div id="form_member_baru" style="display: none;">
                    <hr>
                    <h6 class="font-weight-bold text-primary">Form Pendaftaran Member Baru</h6>
                    <div class="form-group"><label for="name">Nama Lengkap</label><input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}"></div>
                    <div class="form-group"><label for="phone_number">No. Telepon</label><input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ old('phone_number') }}"></div>
                    
                    <div class="form-group">
                        <label for="master_card_id_new">Pilih Kartu RFID (Opsional)</label>
                        <select name="master_card_id" id="master_card_id_new" class="form-control">
                            <option value="">-- Tanpa Kartu --</option>
                            @foreach($availableCards as $card)<option value="{{ $card->id }}" {{ old('master_card_id') == $card->id ? 'selected' : '' }}>{{ $card->card_uid }}</option>@endforeach
                        </select>
                    </div>
                    
                    <!-- Bagian aturan akses yang akan muncul/hilang -->
                    <div id="access_rule_section" style="display: none;">
                        <hr>
                        <h6 class="font-weight-bold">Aturan Akses</h6>
                        <div class="form-group">
                            <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                                <label class="btn btn-outline-primary {{ old('rule_type', 'template') == 'template' ? 'active' : '' }}"><input type="radio" name="rule_type" value="template" {{ old('rule_type', 'template') == 'template' ? 'checked' : '' }}> Gunakan Template</label>
                                <label class="btn btn-outline-secondary {{ old('rule_type') == 'custom' ? 'active' : '' }}"><input type="radio" name="rule_type" value="custom" {{ old('rule_type') == 'custom' ? 'checked' : '' }}> Aturan Custom</label>
                            </div>
                        </div>
                        <div id="form_template_rule_new">
                            <div class="form-group">
                                <label>Pilih Template Aturan</label>
                                <select name="access_rule_id" class="form-control">
                                    <option value="">-- Akses Default (Tanpa Batasan) --</option>
                                    @foreach($accessRules as $rule)<option value="{{ $rule->id }}" {{ old('access_rule_id') == $rule->id ? 'selected' : '' }}>{{ $rule->name }}</option>@endforeach
                                </select>
                            </div>
                        </div>
                        <div id="form_custom_rule_new" style="display: none;">
                            <p class="small text-muted">Isi aturan custom untuk member baru ini.</p>
                            {{-- Field-field aturan custom (max_taps, hari, jam) bisa ditambahkan di sini --}}
                        </div>
                    </div>
                </div>
                <hr>

                {{-- Bagian Transaksi Inti --}}
                <h6 class="font-weight-bold">Detail Transaksi</h6>
                <div class="form-group">
                    <label for="class_id">Pilih Kelas</label>
                    <select name="class_id" id="class_id" class="form-control" required>
                         <option value="" data-price="0">-- Pilih Kelas --</option>
                        @foreach($classes as $class)<option value="{{ $class->id }}" data-price="{{ $class->price }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }} - Rp {{ number_format($class->price) }}</option>@endforeach
                    </select>
                </div>

                {{-- Tampilan Kalkulasi --}}
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Total Harga</label>
                        <input type="text" id="total_price_display" class="form-control bg-light" value="Rp 0" readonly>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="amount_paid">Jumlah Bayar</label>
                        <input type="number" id="amount_paid" name="amount_paid" class="form-control" required min="0" value="{{ old('amount_paid') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Kembalian</label>
                        <input type="text" id="change_display" class="form-control bg-light" value="Rp 0" readonly>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Proses Transaksi</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // --- LOGIKA FORM UTAMA (LAMA vs BARU) ---
        function toggleMemberForms(type) {
            if (type === 'lama') {
                $('#form_member_lama').show();
                $('#form_member_baru').hide();
                $('#form_member_lama select').prop('required', true);
                $('#form_member_baru').find('input[name="name"]').prop('required', false);
            } else {
                $('#form_member_lama').hide();
                $('#form_member_baru').show();
                $('#form_member_lama select').prop('required', false);
                $('#form_member_baru').find('input[name="name"]').prop('required', true);
            }
        }
        var initialTransactionType = $('input[name="transaction_type"]:checked').val();
        toggleMemberForms(initialTransactionType);
        $('input[name="transaction_type"]').change(function() { toggleMemberForms($(this).val()); });

        // --- LOGIKA SUB-FORM (TEMPLATE vs CUSTOM) ---
        function toggleRuleForms(type) {
            if (type === 'template') {
                $('#form_template_rule_new').show();
                $('#form_custom_rule_new').hide();
            } else {
                $('#form_template_rule_new').hide();
                $('#form_custom_rule_new').show();
            }
        }
        var initialRuleType = $('#form_member_baru input[name="rule_type"]:checked').val();
        toggleRuleForms(initialRuleType);
        $('#form_member_baru input[name="rule_type"]').change(function() { toggleRuleForms($(this).val()); });

        // --- LOGIKA BARU: TAMPILKAN/SEMBUNYIKAN ATURAN AKSES ---
        const cardSelect = document.getElementById('master_card_id_new');
        const accessRuleSection = document.getElementById('access_rule_section');
        function toggleAccessRuleSection() {
            if (cardSelect.value) { // Jika nilai TIDAK kosong (kartu dipilih)
                $(accessRuleSection).slideDown();
            } else { // Jika nilai kosong (memilih "-- Tanpa Kartu --")
                $(accessRuleSection).slideUp();
            }
        }
        toggleAccessRuleSection(); 
        cardSelect.addEventListener('change', toggleAccessRuleSection);

        // --- LOGIKA KALKULASI HARGA ---
        const classSelect = document.getElementById('class_id');
        const amountPaidInput = document.getElementById('amount_paid');
        const totalPriceDisplay = document.getElementById('total_price_display');
        const changeDisplay = document.getElementById('change_display');

        function calculate() {
            const selectedOption = classSelect.options[classSelect.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            const amountPaid = parseFloat(amountPaidInput.value) || 0;
            const change = amountPaid - price;
            const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
            totalPriceDisplay.value = formatter.format(price);
            changeDisplay.value = formatter.format(Math.max(0, change));
        }
        classSelect.addEventListener('change', calculate);
        amountPaidInput.addEventListener('input', calculate);
        calculate(); 
    });
</script>
@endpush
