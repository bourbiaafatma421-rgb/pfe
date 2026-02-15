<?php

namespace App\Providers;

<<<<<<< HEAD
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
=======
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
>>>>>>> b18fa01a33003921548a3aec3cf4c40ce7a8a510
     */
    public function boot(): void
    {
        $this->registerPolicies();
<<<<<<< HEAD
=======
        //
>>>>>>> b18fa01a33003921548a3aec3cf4c40ce7a8a510
    }
}
