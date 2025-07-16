<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ApiResourceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        /*$this->app->bind(
            \App\Http\Resources\Contracts\ApiResourceInterface::class,
            \App\Http\Resources\BaseApiResource::class
        );*/
        $resourcePath = app_path('Http/Resources');
        $contractPath = app_path('Http/Resources/Contracts');

        if (!File::exists($resourcePath) || !File::exists($contractPath)) {
            return;
        }

        $contractFiles = File::files($contractPath);

        foreach ($contractFiles as $file) {
            $interfaceName = $file->getFilenameWithoutExtension();

            // Skip if it's not an interface file
            if (!Str::endsWith($interfaceName, 'Interface')) {
                continue;
            }

            $resourceName = Str::replaceLast('Interface', '', $interfaceName);
            $model = Str::replaceLast('Resource', '', $resourceName);

            $interface = "App\\Http\\Resources\\Contracts\\{$interfaceName}";
            $implementation = "App\\Http\\Resources\\{$model}Resource";

            if (interface_exists($interface) && class_exists($implementation)) {
                $this->app->bind($interface, $implementation);
            }
        }

        // Bind specific resources for services
        $this->bindResourcesForServices();
    }

    /**
     * Bind specific resources for services based on naming convention
     */
    private function bindResourcesForServices(): void
    {
        $servicesPath = app_path('Services');

        if (!File::exists($servicesPath)) {
            return;
        }

        $serviceFiles = File::files($servicesPath);

        foreach ($serviceFiles as $file) {
            $serviceName = $file->getFilenameWithoutExtension();

            // Skip BaseService and other non-model services
            if ($serviceName === 'BaseService' || !Str::endsWith($serviceName, 'Service')) {
                continue;
            }

            $model = Str::replaceLast('Service', '', $serviceName);
            $resourceClass = "App\\Http\\Resources\\{$model}Resource";

            if (class_exists($resourceClass)) {
                $this->app->when("App\\Services\\{$serviceName}")
                    ->needs(\App\Http\Resources\Contracts\ApiResourceInterface::class)
                    ->give($resourceClass);
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