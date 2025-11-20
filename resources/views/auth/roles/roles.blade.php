@extends('template-v2')
@section('title', 'Roles')

@section('content')
    <div class="card shadow-lg">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Roles</h1>
                <a class="btn btn-primary add-class">Nuevo Rol</a>
            </div>

            <div class="">
                <table id="table" class="table table-striped table-hover  w-100">
                    <thead>
                    <tr>
                        <th>Nombre</th>
                        <th style="width: 200px;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($roles as $rol)
                        <tr>
                            <td>{{ $rol->name }}</td>
                            <td>
                                <a href="javascript:void(0)"
                                   class="edit-class"
                                   title="Editar Rol"
                                   data-name=" {{ $rol->name }}"
                                   data-id="{{ $rol->id }}"
                                >
                                    <i class="bi bi-pencil-square text-primary h4"></i>
                                </a>

                                <a href="javascript:void(0)" data-id="{{ $rol->id }}" class="border-0 ms-2 delete-class" title="Eliminar Rol">
                                    <i class="bi bi-trash text-danger h4"></i>
                                </a>
                                <a href="javascript:void(0)"
                                   class="add-permission"
                                   data-role-id="{{ $rol->id }}">
                                    <i class="bi bi-plus-circle h4 text-success"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCreateRol" tabindex="-1" aria-labelledby="modalCreateRolLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('roles.store') }}" method="POST" id="formCreateRol">
                @csrf
                <div class="modal-content text-start">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCreateRolLabel">Nuevo Rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <label>Nombre</label>
                                <input class="form-control" id="name" name="name" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" value="" id="id" name="id">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="addPermissionModal" tabindex="-1" aria-labelledby="addPermissionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formAddPermission">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPermissionLabel">Agregar Permisos al Rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="role_id" id="role_id">

                        <div class="mb-3">
                            <label for="permissions" class="form-label">Seleccione Permisos</label>
                            <select name="permissions[]" id="permissions" class="form-control" multiple="multiple">
                                @foreach ($permissions as $permission)
                                    <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            var body = $('body');

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
                    aria: {
                        sortAscending:  ": activar para ordenar la columna de manera ascendente",
                        sortDescending: ": activar para ordenar la columna de manera descendente"
                    }
                }
            });

            body.on('click', '.add-class', function (){
                $('#modalCreateRol').modal('show');
                $('#modalCreateRol #id').val("");
                $('#modalCreateRol #name').val("");
            });

            body.on('click', '.edit-class', function (){
                let id = $(this).data('id');
                let name = $(this).data('name');
                $('#modalCreateRol').modal('show');
                $('#modalCreateRol #name').val(name);
                $('#modalCreateRol #id').val(id);
            });

            body.on('click', '.delete-class', function () {
                let id = $(this).data('id');
                let url = '{{ route('roles.destroy') }}';

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede deshacer",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            method: "POST",
                            data: {
                                'id': id,
                                '_token': $("meta[name='csrf-token']").prop("content")
                            },
                            success: function () {
                                Swal.fire(
                                    '¡Eliminado!',
                                    'El registro ha sido eliminado.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            },
                            error: function (xhr) {
                                Swal.fire('Error', xhr.responseJSON, 'error');
                            }
                        });
                    }
                });
            });

            body.on('click', '.add-permission', function() {
                let roleId = $(this).data('role-id');
                $('#role_id').val(roleId);

                // Limpiar selección anterior
                $('#permissions').val([]);

                // AJAX para obtener permisos actuales
                $.ajax({
                    url: '{{ route("roles.getPermissions") }}', // Nueva ruta
                    method: 'GET',
                    data: { role_id: roleId },
                    success: function(response) {
                        if (response.permissions && response.permissions.length > 0) {
                            $('#permissions').val(response.permissions).trigger('change');
                        }
                        $('#addPermissionModal').modal('show');
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'No se pudieron cargar los permisos del rol', 'error');
                    }
                });
            });


            $('#formAddPermission').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: '{{ route("roles.addPermissions") }}', // Ruta que crees en Laravel
                    method: 'POST',
                    data: {
                        role_id: $('#role_id').val(),
                        permissions: $('#permissions').val(),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire('Éxito', 'Permisos agregados correctamente', 'success');
                        $('#addPermissionModal').modal('hide');
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'No se pudieron agregar los permisos', 'error');
                    }
                });
            });




            {{--$('.btn-edit-user').on('click', function() {--}}
            {{--    var button = $(this);--}}
            {{--    var userId = button.data('id');--}}
            {{--    var name = button.data('name');--}}
            {{--    var email = button.data('email');--}}
            {{--    var roles = button.data('roles') ? button.data('roles').split(',') : [];--}}

            {{--    var form = $('#formEditUser');--}}
            {{--    // Ajustamos la acción del formulario con la ruta correcta:--}}
            {{--    var urlTemplate = "{{ route('users.update', ':id') }}";--}}
            {{--    var url = urlTemplate.replace(':id', userId);--}}
            {{--    form.attr('action', url);--}}

            {{--    $('#edit_user_id').val(userId);--}}
            {{--    $('#edit_name').val(name);--}}
            {{--    $('#edit_email').val(email);--}}

            {{--    // Limpiar roles--}}
            {{--    $('.edit-role-checkbox').prop('checked', false);--}}

            {{--    // Marcar roles seleccionados--}}
            {{--    roles.forEach(function(roleName) {--}}
            {{--        $('.edit-role-checkbox').filter(function() {--}}
            {{--            return $(this).val() === roleName;--}}
            {{--        }).prop('checked', true);--}}
            {{--    });--}}
            {{--});--}}
        });

    </script>
@endsection






























