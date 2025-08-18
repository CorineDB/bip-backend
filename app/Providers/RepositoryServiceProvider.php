<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind base repository interface
        $this->app->bind(
            \App\Repositories\Contracts\BaseRepositoryInterface::class,
            \App\Repositories\Eloquent\BaseRepository::class
        );

        // Manual bindings for critical repositories
        $this->app->bind(
            \App\Repositories\Contracts\RoleRepositoryInterface::class,
            \App\Repositories\RoleRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\PermissionRepositoryInterface::class,
            \App\Repositories\PermissionRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\NotificationRepositoryInterface::class,
            \App\Repositories\NotificationRepository::class
        );

        $contractPath = app_path('Repositories/Contracts');

        if (!File::exists($contractPath) || !File::isDirectory($contractPath)) {
            return;
        }

        $contractFiles = File::files($contractPath);

        foreach ($contractFiles as $file) {
            $interfaceName = $file->getFilenameWithoutExtension();
            
            // Skip BaseRepositoryInterface as it's already bound
            if ($interfaceName === 'BaseRepositoryInterface') {
                continue;
            }

            $model = Str::replaceLast('RepositoryInterface', '', $interfaceName);
            $interface = "App\\Repositories\\Contracts\\{$interfaceName}";
            $implementation = "App\\Repositories\\{$model}Repository";

            // Skip if manually bound above
            if ($interfaceName === 'RoleRepositoryInterface') {
                continue;
            }

            if (interface_exists($interface) && class_exists($implementation)) {
                $this->app->bind($interface, $implementation);
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
