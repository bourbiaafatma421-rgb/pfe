<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use App\Policies\RolePolicy;

;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
         User::class => UserPolicy::class,
         Role::class => RolePolicy::class, 
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
