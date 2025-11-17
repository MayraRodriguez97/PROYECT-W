<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        #sidebar a:hover {
            background-color: #128C7E;
            border-left-color: #25D366;
        }

        .sidebar-header a:hover {
            background-color: transparent !important;
            border-left-color: transparent !important;
        }

        html, body {
            height: 100%;
            margin: 0;
        }

        #content {
            min-height: 100vh;
        }
        body {
            overflow-x: hidden;
            background: #f0f7f6; /* color de fondo suave */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        #sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 220px;
            background-color: #075E54; /* verde WhatsApp */
            padding-top: 60px;
            transition: transform 0.3s ease;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            z-index: 1100; /* Agrega esto */
        }

        #sidebar.collapsed {
            transform: translateX(-220px);
        }
        #sidebar a {
            display: block;
            color: white;
            padding: 15px 25px;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: background-color 0.2s, border-left-color 0.2s;
        }
        #sidebar a:hover {
            background-color: #128C7E;
            border-left-color: #25D366;
        }
        #content {
            margin-left: 220px;
            padding: 40px 20px;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        #content.expanded {
            margin-left: 0;
        }
        #menu-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            background-color: #075E54;
            border: none;
            color: white;
            padding: 10px 18px;
            cursor: pointer;
            z-index: 1000;
            border-radius: 6px;
            font-size: 1.2rem;
            font-weight: 700;
        }
        #menu-btn:hover {
            background-color: #128C7E;
        }
        h1 {
            font-size: 3rem;
            color: #075E54;
            margin-bottom: 0.3rem;
        }
        p {
            font-size: 1.25rem;
            color: #333;
            max-width: 450px;
        }
        /* Imagen decorativa */
        .welcome-image {
            margin-top: 40px;
            max-width: 300px;
            opacity: 0.85;
        }
        /* Responsive */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-220px);
            }
            #sidebar.collapsed {
                transform: translateX(0);
            }
            #content {
                margin-left: 0;
                padding: 20px 15px;
            }
            #content.expanded {
                margin-left: 220px;
            }
        }

        .sidebar-logo {
            max-width: 100px;
            opacity: 0.85;
        }

        .sidebar-user {
            position: relative;
        }
    </style>
</head>
<body>

{{--<button id="menu-btn">☰ Menú</button>--}}
<nav id="sidebar" class="pt-2">
    <!-- Logo principal -->
    <div class="sidebar-header text-center pt-0 pb-2">
        <a href="{{ route('dashboard') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/>
            </svg>
        </a>
    </div>

    <div class="sidebar-user px-3 mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi bi-person-circle text-white me-2" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
                </svg>
                <div>
                    <div class="fw-bold text-white">{{ Auth::user()->name }}</div>
                    <div class="text-white-50 small">{{ Auth::user()->username }}</div>
                </div>
            </div>

            <!-- Botón de configuración -->
            <div class="dropdown">
                <!-- Botón con ícono de engranaje -->
                <button class="btn btn-sm p-0 text-white" type="button" id="profileDropdownToggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                        <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                        <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
                    </svg>
                </button>

                <!-- Dropdown personalizado -->
                <ul class="dropdown-menu  p-3" aria-labelledby="profileDropdownToggle" style="min-width: 250px; background-color: #1e1e2d; position: absolute !important; z-index: 1050 !important;">
                    <li class="mb-2">
                        <div class="fw-bold text-white">{{ Auth::user()->name }}</div>
{{--                        <span class="badge bg-success text-white small">Administrador</span>--}}
                        <div class="text-muted small">{{ Auth::user()->email }}</div>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger w-100 mt-2">Salir</button>
                        </form>
                    </li>
                </ul>
            </div>

        </div>
    </div>

    <a class="d-flex justify-content-between align-items-center px-3 py-2 text-white"
       data-bs-toggle="collapse"
       href="#adminSubmenu"
       role="button"
       aria-expanded="false"
       aria-controls="adminSubmenu">
        <div class="d-flex align-items-center">
            <i class="bi bi-gear me-2"></i>
            <span>Administración</span>
        </div>
        <i class="bi bi-chevron-down fs-6"></i>
    </a>

    <div class="collapse ps-2" id="adminSubmenu">
   @can('manage-users')
        <a href="{{route('users.index')}}" class="d-block px-4 py-2 text-white-50">Usuarios</a>
      @endcan
       @can('rol-write')
        <a href="{{ route('roles.index') }}" class="d-block px-4 py-2 text-white-50">Roles</a>
       @endcan
       @can('permission-write')
        <a href="{{ route('permission.index') }}" class="d-block px-4 py-2 text-white-50">Permisos</a>
        @endcan
    </div>
    <a class="d-flex justify-content-between align-items-center px-3 py-2 text-white"
       data-bs-toggle="collapse"
       href="#sendMessage"
       role="button"
       aria-expanded="false"
       aria-controls="sendMessage">
        <div class="d-flex align-items-center">
            <i class="bi bi-send me-2"></i>
            <span>Enviar Mensajes</span>
        </div>
        <i class="bi bi-chevron-down fs-6"></i>
    </a>

    <div class="collapse ps-2" id="sendMessage">
        @can('send-message')
            <a href="{{ route('excel.upload') }}" class="d-block px-4 py-2 text-white-50">Subir Excel</a>
        @endcan
    </div>

    <a class="d-flex justify-content-between align-items-center px-3 py-2 text-white"
       data-bs-toggle="collapse"
       href="#responses"
       role="button"
       aria-expanded="false"
       aria-controls="responses">
        <div class="d-flex align-items-center">
            <i class="bi bi-chat-dots me-2"></i>
            <span>Respuestas</span>
        </div>
        <i class="bi bi-chevron-down fs-6"></i>
    </a>

    <div class="collapse ps-2" id="responses">
            <a href="{{ route('responses') }}" class="d-block px-4 py-2 text-white-50">Ver Respuestas</a>
        
    </div>
</nav>



<div id="content" class="content d-flex flex-column flex-column-fluid bg-secondary bg-opacity-25 text-start">
    <div id="kt_content_container" class="container-fluid">
        <div class="card">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.js"></script>

@yield('scripts')
</body>
</html>
