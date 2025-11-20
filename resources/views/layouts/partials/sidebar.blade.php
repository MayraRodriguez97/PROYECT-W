<ul class="navbar-nav sidebar  accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-cubes"></i> </div>
        <div class="sidebar-brand-text mx-3">W App</div>
    </a>



    <hr class="sidebar-divider my-0">

    <li class="nav-item active"> <a class="nav-link" href="#">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        INTERFACES
    </div>
    @can('manage-users')
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseConfig"
           aria-expanded="false" aria-controls="collapseConfig">
            <i class="fas fa-fw fa-cogs"></i>
            <span>Configuración</span>
        </a>
        <div id="collapseConfig" class="collapse" aria-labelledby="headingConfig" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded  h-100">
                <a class="collapse-item text-body"  href="{{route('users.index')}}">
                    <i class="fas fa-users fa-fw me-2 text-dark"></i>
                    Usuarios
                </a>
                <a class="collapse-item text-body" href="{{ route('roles.index') }}">

                    <i class="fa fa-id-card fa-fw me-2 text-dark"></i>
                    Roles
                </a>
                <a class="collapse-item text-body" href="{{ route('permission.index') }}">
                    <i class="fas fa-user-lock fa-fw me-2 text-dark"></i>
                    Permisos
                </a>
            </div>
        </div>
    </li>
    @endcan

    @can('send-message')
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseAdminMessage"
               aria-expanded="false" aria-controls="collapseAdminMessage">
                <i class="bi bi-chat-dots"></i>
                <span>Administrar Mensajes</span>
            </a>
            <div id="collapseAdminMessage" class="collapse" aria-labelledby="headingConfig" data-parent="#accordionSidebar">
                <div class="py-2 collapse-inner rounded  h-100">
                    <a class="collapse-item text-body"   href="{{ route('excel.upload') }}">
                        <i class="bi bi-cloud-upload me-2 text-dark"></i>
                        Subir Archivos
                    </a>
                </div>
            </div>
        </li>
    @endcan

{{--    <hr class="sidebar-divider d-none d-md-block">--}}

{{--    <div class="text-center d-none d-md-inline">--}}
{{--        <button class="rounded-circle border-0" id="sidebarToggle"></button>--}}
{{--    </div>--}}

</ul>
