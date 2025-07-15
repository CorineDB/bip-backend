<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ServiceProviderProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\Contracts\AbstractServiceInterface::class,
            \App\Services\BaseService::class
        );


        $contractPath = app_path('Services/Contracts');
        $implementationPath = app_path('Services');

         if (!File::exists($contractPath)) {
            return;
        }
        if (!File::exists($contractPath) || !File::isDirectory($contractPath)) {
            return;
        }

        $contractFiles = File::files($contractPath);

        foreach ($contractFiles as $file) {
            $interfaceName = $file->getFilenameWithoutExtension(); // e.g., UserServiceInterface
            $model = Str::replaceLast('ServiceInterface', '', $interfaceName); // e.g., User

            $interface = "App\\Services\\Contracts\\{$interfaceName}";
            $implementation = "App\\Services\\{$model}Service";

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
