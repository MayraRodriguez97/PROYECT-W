<nav class="navbar navbar-expand navbar-light bg-white topbar mb-0 static-top shadow" id="mainNavbar">

    <!-- Sidebar Toggle (solo móvil) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Right navbar -->
    <ul class="navbar-nav ml-auto">

        <!-- Divider -->
        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Botón de modo oscuro -->
        <li class="nav-item">
            <a class="nav-link" href="#" id="themeToggle">
                <i class="fas fa-moon fa-sm fa-fw text-gray-600" id="themeIcon"></i>
            </a>
        </li>

        <!-- User Info -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name }}</span>

                <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" fill="currentColor"
                     class="bi bi-person-circle text-gray-600" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                    <path fill-rule="evenodd"
                          d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0
                          8m8-7a7 7 0 0 0-5.468 11.37C3.242
                          11.226 4.805 10 8 10s4.757 1.225
                          5.468 2.37A7 7 0 0 0 8 1"/>
                </svg>
            </a>

            <!-- Dropdown -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger small">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw me-2"></i>
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </li>

    </ul>
</nav>
