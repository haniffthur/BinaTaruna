<ul class="navbar-nav bg-white sidebar sidebar-light accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center py-4" href="{{ route('dashboard') }}">
        <div class="sidebar-brand-icon text-primary">
            <i class="fas fa-fingerprint fa-lg"></i>
        </div>
        <div class="sidebar-brand-text mx-2 text-dark font-weight-bold">BinaTaruna</div>
    </a>

    <li class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-home text-primary"></i>
            <span class="ml-2">Dashboard</span>
        </a>
    </li>

    {{-- Menu ini hanya akan muncul untuk user dengan role 'admin' --}}
    @if(auth()->user()->role == 'admin')
    <li class="nav-item {{ request()->is('users*','members*','coaches*','staffs*','classes*','tickets*','access-rules*') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseData">
            <i class="fas fa-database text-primary"></i>
            <span class="ml-2">Management Data</span>
        </a>
        <div id="collapseData" class="collapse {{ request()->is('users*','members*','coaches*','staffs*','classes*','tickets*','access-rules*') ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Pengguna & Peran:</h6>
                <a class="collapse-item {{ request()->is('users*') ? 'active' : '' }}" href="{{ route('users.index') }}">Akun Login</a>
                <a class="collapse-item {{ request()->is('members*') ? 'active' : '' }}" href="{{ route('members.index') }}">Data Member</a>
                <a class="collapse-item {{ request()->is('staffs*') ? 'active' : '' }}" href="{{ route('staffs.index') }}">Data Staff</a>
                <a class="collapse-item {{ request()->is('coaches*') ? 'active' : '' }}" href="{{ route('coaches.index') }}">Data Pelatih</a>
                <div class="collapse-divider"></div>
                <h6 class="collapse-header">Produk & Aturan:</h6>
                <a class="collapse-item {{ request()->is('classes*') ? 'active' : '' }}" href="{{ route('classes.index') }}">Manajemen Kelas</a>
                <a class="collapse-item {{ request()->is('tickets*') ? 'active' : '' }}" href="{{ route('tickets.index') }}">Manajemen Tiket</a>
                <a class="collapse-item {{ request()->is('access-rules*') ? 'active' : '' }}" href="{{ route('access-rules.index') }}">Aturan Akses</a>
            </div>
        </div>
    </li>
    @endif

    {{-- Menu ini akan muncul untuk 'admin' dan 'petugas' --}}
    @if(auth()->user()->role == 'admin' || auth()->user()->role == 'petugas')
    <li class="nav-item {{ request()->is('transactions/member*') || request()->is('transactions/non-member*') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTransaksi">
            <i class="fas fa-shopping-cart text-primary"></i>
            <span class="ml-2">Kasir</span>
        </a>
        <div id="collapseTransaksi" class="collapse {{ request()->is('transactions/member*') || request()->is('transactions/non-member*') ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item {{ request()->is('transactions/non-member/create') ? 'active' : '' }}" href="{{ route('transactions.non-member.create') }}">Transaksi Tiket (Non-Member)</a>
                <a class="collapse-item {{ request()->is('transactions/member/create') ? 'active' : '' }}" href="{{ route('transactions.member.create') }}">Transaksi Kelas (Member)</a>
            </div>
        </div>
    </li>

    <li class="nav-item {{ request()->is('master-cards*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('master-cards.index') }}">
            <i class="fas fa-id-card text-primary"></i>
            <span class="ml-2">Stok Kartu RFID</span>
        </a>
    </li>
    @endif

    @if(auth()->user()->role == 'admin')
    <li class="nav-item {{ request()->is('transactions') || request()->is('enrollments*') || request()->is('tap-logs*') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLaporan">
            <i class="fas fa-file-alt text-primary"></i>
            <span class="ml-2">Laporan & Aktivitas</span>
        </a>
        <div id="collapseLaporan" class="collapse {{ request()->is('transactions') || request()->is('enrollments*') || request()->is('tap-logs*') ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item {{ request()->is('transactions') ? 'active' : '' }}" href="{{ route('transactions.index') }}">Riwayat Transaksi</a>
                <a class="collapse-item {{ request()->is('enrollments*') ? 'active' : '' }}" href="{{ route('enrollments.index') }}">Pendaftaran Kelas</a>
                {{-- Anda perlu membuat route dan view untuk tap-logs.index --}}
                <a class="collapse-item {{ request()->is('tap-logs*') ? 'active' : '' }}" href="{{ route('tap-logs.index') }}">Log Tap Kartu</a>
                 <a class="collapse-item {{ request()->is('ticket-scan-logs*') ? 'active' : '' }}" href="{{ route('ticket-scan-logs.index') }}">Log Scan Tiket</a>
            </div>
        </div>
    </li>
    @endif

    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0 bg-light" id="sidebarToggle"></button>
    </div>

</ul>