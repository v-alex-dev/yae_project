<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // This backend is an API only application: there is no "login" page
        // to redirect a guest user to. By forcing the redirect callback to
        // return null, the AuthenticationException keeps redirectTo = null
        // instead of crashing while trying to resolve route('login').
        Authenticate::redirectUsing(fn () => null);
    }
}
