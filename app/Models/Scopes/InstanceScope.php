<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class InstanceScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // 0. Si no hay usuario logueado, no mostrar nada.
        if (!Auth::check()) {
            $builder->whereRaw('1 = 0');
            return;
        }

        $user = Auth::user();

        // =========================================================
        // 👑 NIVEL 1: SUPER ADMIN (El Jefe Supremo)
        // =========================================================
        // Si es Super Admin, NO aplicamos ningún filtro. Ve todo.
        if ($user->isSuperAdmin()) {
            return; 
        }

        // Obtener IDs de instancias asignadas (Para Admin y Encargado)
        $instanceIds = $user->whatsappInstances()
                            ->pluck('whatsapp_instances.id')
                            ->toArray();


                            // 2. Admins y Encargados ven TODO lo de sus instancias
        $instanceIds = $user->whatsappInstances()
                            ->pluck('whatsapp_instances.id')
                            ->toArray();

        // 👇 ESTA ES LA LÍNEA MÁGICA QUE ARREGLA LA VISIBILIDAD
        // Permite ver mensajes propios, ajenos y de clientes, siempre que sean de la instancia.
        $builder->whereIn('whatsapp_instance_id', $instanceIds);
    
        // =========================================================
        // 👔 NIVEL 2: ADMIN (El Jefe de Área)
        // =========================================================
        // Si tiene el rol 'admin', ve TODO lo de sus instancias.
       // if ($user->hasRole('admin')) {
            // Solo filtramos por Instancia (VTO, Mora, etc.)
         //   $builder->whereIn('whatsapp_instance_id', $instanceIds);
           // return; // Termina aquí, no filtra por usuario.
        //}

        // =========================================================
        // 👷 NIVEL 3: ENCARGADO (El Agente)
        // =========================================================
        // Si llegamos aquí, es un 'encargado' (o cualquier otro rol menor).
        // Aplicamos DOBLE FILTRO:
        
        //$builder->whereIn('whatsapp_instance_id', $instanceIds) // 1. Filtro de Instancia
          //      ->where(function ($query) use ($user) {
                    // 2. Filtro de Propiedad:
                    // Muestra el mensaje SOLO SI:
                    // a) Él lo envió/recibió (user_id == su ID)
                    // b) O el mensaje no tiene dueño (user_id == NULL) para poder tomarlo
            //        $query->where('user_id', $user->id)
              //            ->orWhereNull('user_id');
                //});
    }
}