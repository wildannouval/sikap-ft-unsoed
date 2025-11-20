<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\KerjaPraktik;
use App\Policies\KerjaPraktikPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        KerjaPraktik::class => KerjaPraktikPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // (opsional) Gate::before untuk Admin superuser
        // Gate::before(fn($user) => $user->hasRole('Admin') ? true : null);
    }
}
