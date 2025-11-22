<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    // Listado de permisos a
    public function index()
    {
        $permissions = Permission::select('id', 'name', 'created_at')->get();
        $roles = Role::all();
        return view('auth.permission.permission', compact('permissions', 'roles'));
    }

    // Crear o actualizar permiso
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $request->id,
        ]);

        if ($request->id == null) {
            // Crear nuevo permiso
            Permission::create(['name' => $request->name, 'guard_name' => 'web']);
        } else {
            // Editar permiso existente
            $edit = Permission::find($request->id);
            if (!$edit) {
                return redirect()->back()->withErrors(['msg' => 'Permiso no encontrado']);
            }
            $edit->update(['name' => $request->name]);
        }

        return redirect()->back()->with('success', 'Permiso guardado correctamente');
    }

    // Eliminar permiso
    public function destroy(Request $request)
    {
        $permission = Permission::find($request->id);
        if (!$permission) {
            return response()->json('Permiso no encontrado', 404);
        }

        if (DB::table('model_has_permissions')->where('permission_id', $request->id)->exists()) {
            return response()->json('El permiso está asignado a un usuario o rol', 400);
        }

        $permission->delete();

        return response()->json(['message' => 'Permiso eliminado correctamente']);
    }

    // Obtener roles asignados a un permiso
    public function getRoles(Request $request)
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $permission = Permission::findOrFail($request->permission_id);
        $roles = $permission->roles()->pluck('id')->toArray();

        return response()->json(['roles' => $roles]);
    }

    // Asignar roles a un permiso
    public function addRoles(Request $request)
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $permission = Permission::findOrFail($request->permission_id);

        $roleNames = Role::whereIn('id', $request->roles)
            ->pluck('name')
            ->toArray();

        $permission->syncRoles($roleNames);

        return response()->json(['message' => 'Roles asignados correctamente al permiso']);
    }
}
