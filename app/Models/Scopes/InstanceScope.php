<?php

namespace App\Models\Scopes; // <-- Se asegura de que esté en la carpeta correcta

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class InstanceScope implements Scope
{
    /**
     * Aplica el filtro a todas las consultas.
     */
    public function apply(Builder $builder, Model $model)
    {
        // 1. Revisa si hay un usuario logueado
        if (!Auth::check()) {
            $builder->whereRaw('1 = 0'); // No mostrar nada si no hay login
            return;
        }

        $user = Auth::user();

        // 2. ¡EL INTERRUPTOR DE SEGURIDAD!
        // Usa tu función 'isSuperAdmin()' de tu modelo User.
        // Si es el super admin, el filtro no hace NADA y ve todo.
        if ($user->isSuperAdmin()) {
            return; 
        }

        // 3. PARA TODOS LOS DEMÁS USUARIOS (Admins, Encargados)
        
        // Obtiene la lista de IDs de instancias que este admin SÍ PUEDE VER
        $instanceIds = $user->whatsappInstances()
                            ->pluck('whatsapp_instances.id')
                            ->toArray();

        // 4. APLICA EL FILTRO
        // Le dice a Laravel: "Trae solo los mensajes donde
        // la columna 'whatsapp_instance_id' esté EN ESTA LISTA".
        $builder->whereIn('whatsapp_instance_id', $instanceIds);
    }
}