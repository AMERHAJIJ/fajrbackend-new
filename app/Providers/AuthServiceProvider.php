<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
    protected function boot(): void
    {
        $this->registerPolicies();

        // تعريف صلاحيات الواجبات
        Gate::define('view homeworks', function ($user) {
            return $user->hasRole(['Admin', 'Teacher', 'Student']);
        })->guard('web');

        Gate::define('create homeworks', function ($user) {
            return $user->hasRole(['Admin', 'Teacher']);
        })->guard('web');

        Gate::define('edit homeworks', function ($user) {
            return $user->hasRole(['Admin', 'Teacher']);
        })->guard('web');

        Gate::define('delete homeworks', function ($user) {
            return $user->hasRole('Admin');
        })->guard('web');
    }
}
