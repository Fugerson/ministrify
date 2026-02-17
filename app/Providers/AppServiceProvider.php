<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS in production (for queue workers that don't have request context)
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        } else {
            // Allow non-secure cookies in development (HTTP)
            config(['session.secure' => false]);
        }

        // Prevent lazy loading in development
        Model::preventLazyLoading(!$this->app->isProduction());

        // Custom Blade directives for roles
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });

        Blade::if('leader', function () {
            return auth()->check() && auth()->user()->hasRole(['admin', 'leader']);
        });

        Blade::if('role', function (string $role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        // Check if user has ANY church role assigned (not a pending self-registered user)
        // Super admins always have access (whether impersonating a church or not)
        Blade::if('hasChurchRole', function () {
            if (!auth()->check()) {
                return false;
            }
            $user = auth()->user();
            // Super admins always have access
            if ($user->isSuperAdmin()) {
                return true;
            }
            // Regular users need a church role
            return $user->churchRole !== null;
        });
    }
}
