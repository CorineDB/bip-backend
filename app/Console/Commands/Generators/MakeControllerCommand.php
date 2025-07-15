<?php

namespace App\Console\Commands\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeControllerCommand extends Command
{
    protected $signature = 'generate:controller
                            {name : Controller name (without suffix)}
                            {--model=}
                            {--service=}
                            {--force : Overwrite existing files}';

    protected $description = 'Generate a controller and its test with optional model and service binding.';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $controllerClass = "{$name}Controller";
        $controllerPath = app_path("Http/Controllers/{$controllerClass}.php");
        $testClass = "{$controllerClass}Test";
        $testPath = base_path("tests/Feature/Http/Controllers/{$testClass}.php");

        $model = $this->option('model') ?? Str::singular($name);
        $service = $this->option('service') ?? "{$name}Service";

        $table = Str::snake(Str::plural($model));
        $variable = Str::camel($model);
        $routePrefix = Str::kebab(Str::pluralStudly($model));
        $force = $this->option('force');

        // 🔁 Generate Controller
        if (File::exists($controllerPath) && !$force) {
            $this->warn("❗ Controller already exists: {$controllerPath}");
        } else {
            $stub = File::get(app_path('stubs/controller.stub'));
            $content = str_replace(
                ['{{ class }}', '{{ serviceInterface }}', '{{ model }}', '{{ module }}', '{{ service }}', '{{ variable }}', '{{ table }}', '{{ routePrefix }}'],
                [$controllerClass, $service."Interface", $model, strtolower($model.'s'), $service, $variable, $table, $routePrefix],
                $stub
            );

            File::ensureDirectoryExists(dirname($controllerPath));
            File::put($controllerPath, $content);
            $this->info("✅ Controller created: {$controllerPath}");
        }

        // 🧪 Generate Test
        if (File::exists($testPath) && !$force) {
            $this->warn("❗ Test already exists: {$testPath}");
        } else {
            $stub = File::get(app_path('stubs/tests/controller.test.stub'));
            $content = str_replace(
                ['{{ class }}', '{{ model }}', '{{ variable }}', '{{ table }}', '{{ routePrefix }}'],
                [$controllerClass, $model, $variable, $table, $routePrefix],
                $stub
            );

            File::ensureDirectoryExists(dirname($testPath));
            File::put($testPath, $content);
            $this->info("🧪 Test created: {$testPath}");
        }

            $this->call('generate:repository', [
                'name' => "Store{$model}Request",
                '--force' => true
            ]);

            $this->call('generate:repository', [
                'name' => "Update{$model}Request",
                '--force' => true
            ]);
        return self::SUCCESS;
    }
}