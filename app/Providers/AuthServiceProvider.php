<?php

namespace App\Providers;

use App\Models\Collaborateur;
use App\Policies\CollaborateurPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
     protected $policies = [
        Collaborateur::class => CollaborateurPolicy::class,
    ];
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        //
    }
}
