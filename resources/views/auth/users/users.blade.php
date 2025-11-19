@extends('template')

@section('title', 'Usuarios')

@section('content')



    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Usuarios</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateUser">Nuevo Usuario</button>
    </div>

    <table id="table" class="table table-striped table-bordered align-middle">
        <thead>
        <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Roles</th>
            <th style="width: 200px;">Acciones</th>
        </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->roles->pluck('name')->join(', ') }}</td>
                <td>
                    <a href="javascript:void(0)"
                        class="btn-edit-user"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditUser"
                        data-id="{{ $user->id }}"
                        data-name="{{ $user->name }}"
                        data-email="{{ $user->email }}"
                        data-roles="{{ $user->roles->pluck('name')->join(',') }}"
                        data-instances="{{ $user->whatsappInstances->pluck('id')->join(',') }}"
                        title="Editar usuario"
                    >
                        <i class="bi bi-pencil-square text-primary h4"></i>
                    </a>

                    <a type="submit" class="border-0 ms-2" title="Eliminar usuario">
                        <i class="bi bi-trash text-danger h4"></i>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>


    <!-- Modal Crear Usuario -->
    <div class="modal fade" id="modalCreateUser" tabindex="-1" aria-labelledby="modalCreateUserLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('users.store') }}" method="POST" id="formCreateUser">
                @csrf
                <div class="modal-content text-start">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCreateUserLabel">Nuevo Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-8">
                                <label for="create_name" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="create_name" name="name" required>
                            </div>
                            <div class="col-12">
                                <label for="create_email" class="form-label">Correo</label>
                                <input type="email" class="form-control" id="create_email" name="email" required>
                            </div>
                            <div class="col-6">
                                <label for="create_password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="create_password" name="password" required>
                            </div>
                            <div class="col-6">
                                <label for="create_password_confirmation" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="create_password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Roles</label>
                            <div class="row">
                                @foreach($roles as $role)
                                    <div class="col-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="{{ $role->name }}" name="roles[]" id="create_role_{{ $role->id }}">
                                            <label class="form-check-label" for="create_role_{{ $role->id }}">{{ $role->name }}</label>
                                        </div>
                                    </div>
                        <div class="col-12 mt-3">
                                    <label class="form-label fw-bold">Asignar Instancia (Área)</label>
                                    <div class="card p-2" style="background: #f8f9fa;">
                                        <div class="row">
                                            @if(isset($instances))
                                                @foreach($instances as $instance)
                                                    <div class="col-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" 
                                                                type="checkbox" 
                                                                value="{{ $instance->id }}" 
                                                                name="instances[]" 
                                                                id="create_instance_{{ $instance->id }}">
                                                            <label class="form-check-label" for="create_instance_{{ $instance->id }}">
                                                                {{ $instance->name }} <small class="text-muted">({{ $instance->area }})</small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div class="modal fade" id="modalEditUser" tabindex="-1" aria-labelledby="modalEditUserLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="formEditUser">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditUserLabel">Editar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_user_id" name="user_id" />

                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Correo</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">Contraseña (dejar vacío para no cambiar)</label>
                            <input type="password" class="form-control" id="edit_password" name="password" autocomplete="new-password">
                        </div>
                        <div class="mb-3">
                            <label for="edit_password_confirmation" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation" autocomplete="new-password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Roles</label>
                            @foreach($roles as $role)
                                <div class="form-check">
                                    <input class="form-check-input edit-role-checkbox" type="checkbox" value="{{ $role->name }}" name="roles[]" id="edit_role_{{ $role->id }}">
                                    <label class="form-check-label" for="edit_role_{{ $role->id }}">{{ $role->name }}</label>
                                </div>
                            @endforeach
                            <div class="mb-3">
                                <label class="form-label fw-bold">Asignar Instancia (Área)</label>
                                <div class="card p-2" style="background: #f8f9fa;">
                                    <div class="row">
                                        @if(isset($instances))
                                            @foreach($instances as $instance)
                                                <div class="col-6">
                                                    <div class="form-check">
                                                        {{-- Nota la clase 'edit-instance-checkbox' para el JS --}}
                                                        <input class="form-check-input edit-instance-checkbox" 
                                                            type="checkbox" 
                                                            value="{{ $instance->id }}" 
                                                            name="instances[]" 
                                                            id="edit_instance_{{ $instance->id }}">
                                                        <label class="form-check-label" for="edit_instance_{{ $instance->id }}">
                                                            {{ $instance->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {

        new DataTable('#table', {
            language: {
                processing:     "Procesando...",
                search:         "",
                lengthMenu:     "_MENU_",
                info:           "Mostrando de _START_ a _END_ de _TOTAL_ registros",
                infoEmpty:      "Mostrando 0 registros",
                infoFiltered:   "(filtrado de _MAX_ registros totales)",
                loadingRecords: "Cargando...",
                zeroRecords:    "No se encontraron resultados",
                emptyTable:     "No hay datos disponibles en esta tabla",
                // paginate: {
                //     first:      "Primero",
                //     previous:   "Anterior",
                //     next:       "Siguiente",
                //     last:       "Último"
                // },
                aria: {
                    sortAscending:  ": activar para ordenar la columna de manera ascendente",
                    sortDescending: ": activar para ordenar la columna de manera descendente"
                }
            }
        });

        $('.btn-edit-user').on('click', function() {
            var button = $(this);
            var userId = button.data('id');
            var name = button.data('name');
            var email = button.data('email');
            var roles = button.data('roles') ? button.data('roles').split(',') : [];
            var instances = button.data('instances') ? String(button.data('instances')).split(',') : [];

            var form = $('#formEditUser');
            // Ajustamos la acción del formulario con la ruta correcta:
            var urlTemplate = "{{ route('users.update', ':id') }}";
            var url = urlTemplate.replace(':id', userId);
            form.attr('action', url);

            $('#edit_user_id').val(userId);
            $('#edit_name').val(name);
            $('#edit_email').val(email);

            // Limpiar roles
            $('.edit-role-checkbox').prop('checked', false);

            // Marcar roles seleccionados
            roles.forEach(function(roleName) {
                $('.edit-role-checkbox').filter(function() {
                    return $(this).val() === roleName;
                }).prop('checked', true);
            });
            $('.edit-instance-checkbox').prop('checked', false);
            instances.forEach(function(instId) {
        $('#edit_instance_' + instId).prop('checked', true);
    });
        });
    });


</script>
@endsection
