<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // 👇 ESTE ES EL CÓDIGO MÁGICO 👇
        
        Gate::before(function ($user, $ability) {
            
            // Asegúrate de que el nombre del rol sea EXACTO
            // Si es 'Super Admin' (con mayúscula), cámbialo aquí.
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}