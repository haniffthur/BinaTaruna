<nav class="navbar navbar-expand navbar-light bg-white border-bottom mb-4  py-2 mt-1">

    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars text-dark"></i>
    </button>

    <ul class="navbar-nav ml-auto align-items-center">

        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                <span class="mr-2 d-none d-lg-inline text-dark small font-weight-medium">
                    {{ auth()->user()->name }}
                </span>
                <img class="img-profile rounded-circle" src="{{ asset('img/undraw_profile.svg') }}" width="32" height="32">
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#"><i class="fas fa-user fa-sm fa-fw mr-2 text-muted"></i> Profile</a>
                <a class="dropdown-item" href="#"><i class="fas fa-cogs fa-sm fa-fw mr-2 text-muted"></i> Settings</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="{{ route('logout') }}"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2"></i> Logout</a>
            </div>
        </li>
    </ul>
</nav>
