<?php

namespace App\Console\Commands\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeServiceCommand extends Command
{
    protected $signature = 'generate:service {name} {--force}';

    protected $description = 'Generate a service class, interface and test';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $serviceClass = "{$name}Service";
        $interfaceName = "{$serviceClass}Interface";
        $repositoryInterface = "\\App\\Repositories\\Contracts\\{$name}RepositoryInterface";

        $servicePath = app_path("Services/{$serviceClass}.php");
        $interfacePath = app_path("Services/Contracts/{$interfaceName}.php");
        $testPath = base_path("tests/Unit/Services/{$serviceClass}Test.php");

        if (File::exists($servicePath) && ! $this->option('force')) {
            $this->warn("❗ Service already exists: {$servicePath}. Use --force to overwrite.");
            return self::FAILURE;
        }

        if (File::exists($interfacePath) && ! $this->option('force')) {
            $this->warn("❗ Interface already exists: {$interfacePath}. Use --force to overwrite.");
            return self::FAILURE;
        }

        if (File::exists($testPath) && ! $this->option('force')) {
            $this->warn("❗ Test already exists: {$testPath}. Use --force to overwrite.");
            return self::FAILURE;
        }

        // Créer les dossiers si nécessaire
        File::ensureDirectoryExists(dirname($servicePath));
        File::ensureDirectoryExists(dirname($interfacePath));
        File::ensureDirectoryExists(dirname($testPath));

        // Générer service
        $serviceStub = File::get(app_path('stubs/service.stub'));
        $serviceContent = str_replace(
            ['{{ class }}', '{{ model }}'],
            [$serviceClass, $name],
            $serviceStub
        );
        File::put($servicePath, $serviceContent);
        $this->info("✅ Service created: {$servicePath}");

        // Générer interface
        $interfaceStub = File::get(app_path('stubs/i_service.stub'));
        $interfaceContent = str_replace('{{ class }}', $interfaceName, $interfaceStub);
        File::put($interfacePath, $interfaceContent);
        $this->info("✅ Interface created: {$interfacePath}");

        // Générer test
        $testStub = File::get(app_path('stubs/tests/service.test.stub'));
        $testContent = str_replace(
            ['{{ class }}', '{{ repositoryInterface }}'],
            [$serviceClass, $repositoryInterface],
            $testStub
        );
        File::put($testPath, $testContent);
        $this->info("🧪 Test created: {$testPath}");

        $repositoryClass = "{$name}Repository";
        $repositoryPath = app_path("Repositories/{$repositoryClass}.php");

        if (!File::exists(app_path("$repositoryPath"))) {
            $this->warn("❗Repository not exists: {$repositoryClass}.");
            $this->call('generate:repository', [
                'name' => $name
            ]);
        }
        else{
            $this->call('generate:repository', [
                'name' => $name,
                '--force' => true
            ]);
        }

        return self::SUCCESS;
    }
}