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
        $this->app->bind(
            \App\Repositories\Contracts\BaseRepositoryInterface::class,
            \App\Repositories\Eloquent\BaseRepository::class
        );

        $contractPath = app_path('Repositories/Contracts');
        $implementationPath = app_path('Repositories');

         if (!File::exists($contractPath)) {
            return;
        }
        if (!File::exists($contractPath) || !File::isDirectory($contractPath)) {
            return;
        }

        $contractFiles = File::files($contractPath);

        foreach ($contractFiles as $file) {
            $interfaceName = $file->getFilenameWithoutExtension(); // e.g., UserRepositoryInterface
            $model = Str::replaceLast('RepositoryInterface', '', $interfaceName); // e.g., User

            $interface = "App\\Repositories\\Contracts\\{$interfaceName}";
            $implementation = "App\\Repositories\\Eloquent\\{$model}Repository";

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
