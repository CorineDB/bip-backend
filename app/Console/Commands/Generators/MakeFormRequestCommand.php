<?php

namespace App\Console\Commands\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeFormRequestCommand extends Command
{
    protected $signature = 'generate:form-request {name} {--module=?} {--force}';

    protected $description = 'Generate a custom FormRequest class';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $module = strtolower($this->option('module'));
        $force = $this->option('force');

        $filename = app_path("Http/Requests/{$module}/{$name}.php");

        if (File::exists($filename) && !$force) {
            $this->warn("❗ FormRequest already exists: {$filename}. Use --force to overwrite.");
            return self::FAILURE;
        }

        $stub = File::get(app_path('stubs/form-request.stub'));
        $content = str_replace(['{{ class }}', '{{ module }}'], [$name, $module], $stub);

        File::ensureDirectoryExists(dirname($filename));
        File::put($filename, $content);

        $this->info("✅ FormRequest created: {$filename}");

        return self::SUCCESS;
    }
}