<?php

namespace App\Console\Commands\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeResourceCommand extends Command
{
    protected $signature = 'generate:resource {name} {--type=single : The resource type (single, collection, external)} {--force}';

    protected $description = 'Generate an API resource class';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $type = $this->option('type');
        
        if (!in_array($type, ['single', 'collection', 'external'])) {
            $this->error("❗ Invalid resource type: {$type}. Valid types are: single, collection, external");
            return self::FAILURE;
        }

        $resourceClass = $this->getResourceClassName($name, $type);
        $modelName = $name;
        $modelLower = Str::lower($name);

        $resourcePath = app_path("Http/Resources/{$resourceClass}.php");

        if (File::exists($resourcePath) && ! $this->option('force')) {
            $this->warn("❗ Resource already exists: {$resourcePath}. Use --force to overwrite.");
            return self::FAILURE;
        }

        // Créer le dossier si nécessaire
        File::ensureDirectoryExists(dirname($resourcePath));

        // Générer resource
        $stubFile = $this->getStubFile($type);
        $resourceStub = File::get(app_path("stubs/{$stubFile}"));
        $resourceContent = str_replace(
            ['{{ class }}', '{{ model }}', '{{ modelLower }}', '{{ type }}'],
            [$resourceClass, $modelName, $modelLower, $type],
            $resourceStub
        );
        File::put($resourcePath, $resourceContent);
        $this->info("✅ {$type} resource created: {$resourcePath}");

        // Suggestions pour l'utilisation
        $this->displayUsageExamples($resourceClass, $type);

        return self::SUCCESS;
    }

    private function getResourceClassName(string $name, string $type): string
    {
        return match ($type) {
            'single' => "{$name}Resource",
            'collection' => "{$name}Collection",
            'external' => "{$name}ExternalResource",
        };
    }

    private function getStubFile(string $type): string
    {
        return match ($type) {
            'single' => 'resource.stub',
            'collection' => 'resource-collection.stub',
            'external' => 'resource-external.stub',
        };
    }

    private function displayUsageExamples(string $resourceClass, string $type): void
    {
        $this->line('');
        $this->info('💡 Usage examples:');
        
        match ($type) {
            'single' => $this->displaySingleResourceExamples($resourceClass),
            'collection' => $this->displayCollectionResourceExamples($resourceClass),
            'external' => $this->displayExternalResourceExamples($resourceClass),
        };
        
        $this->line('');
        $this->info('📝 Don\'t forget to:');
        $this->line("   - Update the toArray() method with your model attributes");
        $this->line("   - Configure your service to use this resource via IoC");
    }

    private function displaySingleResourceExamples(string $resourceClass): void
    {
        $this->line("   // Single resource");
        $this->line("   return new {$resourceClass}(\$model);");
        $this->line('');
        $this->line("   // Collection (consider using collection resource instead)");
        $this->line("   return {$resourceClass}::collection(\$models);");
    }

    private function displayCollectionResourceExamples(string $resourceClass): void
    {
        $this->line("   // Collection resource");
        $this->line("   return new {$resourceClass}(\$models);");
        $this->line('');
        $this->line("   // With pagination");
        $this->line("   return new {$resourceClass}(\$paginatedModels);");
    }

    private function displayExternalResourceExamples(string $resourceClass): void
    {
        $this->line("   // External resource");
        $this->line("   return new {$resourceClass}(\$model);");
        $this->line('');
        $this->line("   // For relationships");
        $this->line("   return {$resourceClass}::collection(\$models);");
        $this->line('');
        $this->line("   // External array (no wrappers)");
        $this->line("   return \$resource->resolveExternal();");
    }

}