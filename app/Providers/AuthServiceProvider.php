<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use App\Policies\RolePolicy;

use App\Models\Document;
use App\Policies\DocumentPolicy;
use App\Policies\UserPolicy;
use App\Policies\DocumentSignaturePolicy;
use Illuminate\Support\Facades\Gate;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class, 
        Document::class => DocumentPolicy::class,
        DocumentSignature::class => DocumentSignaturePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        Gate::define('view-dashboard', function (User $user) {
            return $user->role && strtolower($user->role->name) === 'new_collaborateur';
        });
           
        Gate::define('view-formations', function (User $user) {
            return $user->role && strtolower($user->role->name) === 'new_collaborateur';
        });

        Gate::define('view-integration-plan', function (User $user) {
            return $user->role && strtolower($user->role->name) === 'new_collaborateur';
        });
    }
}
