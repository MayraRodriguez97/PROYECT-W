@extends('template-v2')

@section('title', 'Permisos')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Permisos</h1>
                <button type="button" class="btn btn-primary add-class">Nuevo Permiso</button>
            </div>

            <div class="">
                <table id="table" class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>Nombre</th>
                        <th style="width: 200px;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($permissions as $permission)
                        <tr data-id="{{ $permission->id }}">
                            <td>{{ $permission->name }}</td>
                            <td>
                                <button type="button"
                                        class="edit-class btn btn-link p-0"
                                        title="Editar Permiso"
                                        data-name="{{ $permission->name }}"
                                        data-id="{{ $permission->id }}">
                                    <i class="bi bi-pencil-square text-primary h4"></i>
                                </button>

                                <button type="button" data-id="{{ $permission->id }}" class="delete-class btn btn-link p-0 ms-2" title="Eliminar Permiso">
                                    <i class="bi bi-trash text-danger h4"></i>
                                </button>

                                <button type="button"
                                        class="add-role btn btn-link p-0 ms-2"
                                        data-permission-id="{{ $permission->id }}"
                                        title="Agregar Roles">
                                    <i class="bi bi-plus-circle h4 text-success"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Crear/Editar Permiso -->
    <div class="modal fade" id="modalCreatePermission" tabindex="-1" aria-labelledby="modalCreatePermissionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('permission.store') }}" method="POST" id="formCreatePermission">
                @csrf
                <div class="modal-content text-start">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCreatePermissionLabel">Nuevo Permiso</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <label>Nombre</label>
                                <input class="form-control" id="name" name="name" autocomplete="off" required>
                                <input type="hidden" id="id" name="id" value="">
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

    <!-- Modal Asignar Roles -->
    <div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formAddRoles">
                @csrf
                <input type="hidden" id="permission_id_roles" name="permission_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRoleModalLabel">Asignar Roles</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <select id="roles" name="roles[]" multiple="multiple" class="form-control" style="width: 100%;">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar Roles</button>
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
    // Inicializar DataTable
    $('#table').DataTable({
        language: {
            processing: "Procesando...",
            search: "",
            lengthMenu: "_MENU_",
            info: "Mostrando de _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            loadingRecords: "Cargando...",
            zeroRecords: "No se encontraron resultados",
            emptyTable: "No hay datos disponibles en esta tabla",
            aria: {
                sortAscending: ": activar para ordenar la columna de manera ascendente",
                sortDescending: ": activar para ordenar la columna de manera descendente"
            }
        }
    });

    // Inicializar Select2 en modal roles
    $('#roles').select2({
        dropdownParent: $('#addRoleModal'),
        width: '100%'
    });

    var modalCreatePermission = new bootstrap.Modal(document.getElementById('modalCreatePermission'));
    var modalAddRole = new bootstrap.Modal(document.getElementById('addRoleModal'));

    // Botón Nuevo Permiso
    $('body').on('click', '.add-class', function () {
        $('#modalCreatePermissionLabel').text('Nuevo Permiso');
        $('#modalCreatePermission #id').val("");
        $('#modalCreatePermission #name').val("");
        modalCreatePermission.show();
    });

    // Botón Editar Permiso
    $('body').on('click', '.edit-class', function () {
        let id = $(this).data('id');
        let name = $(this).data('name');
        $('#modalCreatePermissionLabel').text('Editar Permiso');
        $('#modalCreatePermission #id').val(id);
        $('#modalCreatePermission #name').val(name);
        modalCreatePermission.show();
    });

    // Enviar formulario Crear/Editar con AJAX
    $('#formCreatePermission').submit(function(e) {
        e.preventDefault();
        let form = $(this);
        let id = $('#id').val();
        let url = id ? "{{ url('permission/store') }}" : "{{ route('permission.store') }}"; // si usas la misma ruta para crear y actualizar
        $.ajax({
            type: 'POST',
            url: url,
            data: form.serialize(),
            success: function(response) {
                alert(response.success || 'Permiso guardado');
                location.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Error al guardar permiso');
            }
        });
    });

    // Botón Eliminar con AJAX
    $('body').on('click', '.delete-class', function () {
        if (!confirm('¿Seguro que quieres eliminar este permiso?')) return;
        let id = $(this).data('id');
        $.ajax({
            url: "{{ route('permission.destroy') }}",
            type: 'POST', // si no usas DELETE en ruta, pon POST y maneja en controlador
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            success: function(response) {
                alert(response.message || 'Permiso eliminado');
                location.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Error al eliminar permiso');
            }
        });
    });

    // Botón Agregar Roles: abrir modal y cargar roles asignados
    $('body').on('click', '.add-role', function () {
        let permissionId = $(this).data('permission-id');
        $('#permission_id_roles').val(permissionId);
        $.ajax({
            url: "{{ route('permission.getRoles') }}",
            method: 'GET',
            data: { permission_id: permissionId },
            success: function(data) {
                $('#roles').val(data.roles).trigger('change');
                modalAddRole.show();
            },
            error: function() {
                alert('Error al cargar roles');
            }
        });
    });

    // Guardar roles asignados con AJAX
    $('#formAddRoles').submit(function(e) {
        e.preventDefault();
        let form = $(this);
        $.ajax({
            url: "{{ route('permission.addRoles') }}",
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                alert(response.message || 'Roles asignados');
                modalAddRole.hide();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Error al asignar roles');
            }
        });
    });
});
</script>
@endsection
