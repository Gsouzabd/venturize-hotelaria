<?php

namespace App\Providers;

use App\Models\Usuario;
use Laravel\Passport\Passport;
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
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('usar-admin', fn(Usuario $usuario) => $usuario->fl_ativo && $usuario->tipo == 'administrador');
        $this->registerPolicies();

        Passport::routes();
        foreach (array_keys(config('app.enums.permissoes_plano')) as $permissao) {
            Gate::define($permissao, fn(Usuario $usuario) => $usuario->temPermissao($permissao));
        }
    }
}
