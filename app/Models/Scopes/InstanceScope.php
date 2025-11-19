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
        // 1. Si no hay usuario logueado, no mostrar nada.
        if (!Auth::check()) {
            $builder->whereRaw('1 = 0');
            return;
        }

        $user = Auth::user();

        // 2. SUPER ADMIN: Ve todo (Tu usuario principal)
        if ($user->isSuperAdmin()) {
            return; 
        }

        // Obtener IDs de las instancias permitidas (VTO, Mora, etc.)
        $instanceIds = $user->whatsappInstances()
                            ->pluck('whatsapp_instances.id')
                            ->toArray();

        // 3. ADMIN: Ve todo lo de su instancia
        if ($user->hasRole('admin')) {
            $builder->whereIn('whatsapp_instance_id', $instanceIds);
            return; 
        }

        // 4. ENCARGADO: Filtro Inteligente
        // Ve solo su instancia Y...
        $builder->whereIn('whatsapp_instance_id', $instanceIds)
                ->where(function ($query) use ($user) {
                    
                    // a) Mensajes que él escribió
                    $query->where('user_id', $user->id)
                    
                    // b) Mensajes del cliente (que entran sin dueño)
                          ->orWhereNull('user_id')
                          
                    // c) Mensajes de clientes que YA están asignados a este Encargado
                    // (Esta es la línea que asegura que veas las respuestas)
                          ->orWhereHas('client.users', function ($q) use ($user) {
                              $q->where('users.id', $user->id);
                          });
                });
    }
}