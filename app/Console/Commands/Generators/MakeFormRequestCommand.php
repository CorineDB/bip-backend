<?php

namespace App\Console\Commands\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeFormRequestCommand extends Command
{
    protected $signature = 'generate:form-request {name} {--module=} {--force}';

    protected $description = 'Generate both Create and Update FormRequest classes';

    public function handle(): int
    {
        $baseName = Str::studly($this->argument('name'));
        $module = strtolower($this->option('module') ?? '');
        $force = $this->option('force');

        $requestTypes = ['Store', 'Update'];

        foreach ($requestTypes as $type) {
            $className = "{$type}{$baseName}Request";
            $stubFile = app_path("stubs/" . strtolower($type) . "-form-request.stub");
            $filePath = app_path("Http/Requests/" . ($module ? "{$module}/" : "") . "{$className}.php");

            if (!File::exists($stubFile)) {
                $this->error("❌ Missing stub: {$stubFile}");
                continue;
            }

            if (File::exists($filePath) && !$force) {
                $this->warn("⚠️ {$className} already exists at {$filePath}. Use --force to overwrite.");
                continue;
            }

            $stub = File::get($stubFile);
            $namespaceSuffix = $module ? '\\' . ucfirst($module) : '';

            $content = str_replace(
                ['{{ class }}', '{{ module_namespace }}'],
                [$className, $namespaceSuffix],
                $stub
            );

            File::ensureDirectoryExists(dirname($filePath));
            File::put($filePath, $content);

            $this->info("✅ {$className} generated at {$filePath}");
        }

        return self::SUCCESS;
    }
}