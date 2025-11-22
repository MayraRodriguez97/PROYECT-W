<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Models\WhatsappInstance;


class UserController extends Controller
{
    // Mostrar la lista con roles
    public function index()
    {
        $users = User::with('roles')->get();
        $roles = Role::all();
        $instances = WhatsappInstance::all();
        return view('auth.users.users', compact('users', 'roles', 'instances'));
    }

    // Guardar usuario nuevo
    public function store(Request $request)
    {
        DB::beginTransaction();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'roles' => 'required|array',
            'instances' => 'nullable|array', // <--- 5. NUEVO: Validar que sea un array
            'instances.*' => 'exists:whatsapp_instances,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->syncRoles($request->roles);
       if ($request->has('instances')) {
            // Esto guarda la relación en la tabla 'instance_user' automáticamente
            $user->whatsappInstances()->sync($request->instances);
        }
        DB::commit();
        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    // Actualizar usuario existente
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'roles' => 'required|array',
            'instances.*' => 'exists:whatsapp_instances,id',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $user->syncRoles($request->roles);
        $user->whatsappInstances()->sync($request->input('instances', []));
        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    // Eliminar usuario
    public function destroy(User $user)
    {
        // Evitar que un usuario se elimine a sí mismo
        if (auth()->user()->id === $user->id) {
            return redirect()->route('users.index')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        // Opcional: evitar eliminar usuarios admin
        if ($user->hasRole('admin')) {
            return redirect()->route('users.index')->with('error', 'No se puede eliminar un usuario administrador.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
