<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    
    @stack('styles')
   <style>
/* Shadow kanan */
.sidebar {
    box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
    z-index: 1030;
}

/* Collapse container */
.sidebar .collapse-inner {
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
    background-color: #ffffff;
    padding: 0;
    overflow: hidden;
}

/* Item collapse */
.sidebar .collapse-item {
    display: block;
    width: 100%;
    padding: 0.65rem 1rem;
    color: #4e73df;
    font-size: 0.925rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    transition: background-color 0.2s ease, color 0.2s ease;
}

/* Hover & Active Style */
.sidebar .collapse-item:hover {
    background-color: #f1f3f9;
    color: #2e59d9;
    text-decoration: none;
}

.sidebar .collapse-item.active {
    background-color: #e8edfb;
    font-weight: 600;
    color: #224abe;
}
</style>

</head>

<body id="page-top">
    

    <div id="wrapper">
        @include('layouts.sidebar')

        <div id="content-wrapper" class="d-flex flex-column bg-white">
            <div id="content">
                @include('layouts.topbar')
                

                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>

            @include('layouts.footer')
        </div>
    </div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
        <!-- Scripts dari halaman spesifik akan dimuat di sini -->
    @stack('scripts')
</body>
</html>
