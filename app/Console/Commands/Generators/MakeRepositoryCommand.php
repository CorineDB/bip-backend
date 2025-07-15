<?php

namespace App\Console\Commands\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeRepositoryCommand extends Command
{
    protected $signature = 'generate:repository {name} {--model=} {--force}';

    protected $description = 'Generate a repository class and unit test';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $model = $this->option('model') ?? $name;
        $force = $this->option('force');

        $repositoryClass = "{$name}Repository";
        $repositoryPath = app_path("Repositories/{$repositoryClass}.php");
        $testPath = base_path("tests/Unit/Repositories/{$repositoryClass}Test.php");

        // Repository
        if (File::exists($repositoryPath) && !$force) {
        } else {
            $stub = File::get(app_path('stubs/i_repository.stub'));
            $content = str_replace(
                ['{{ class }}'],
                [$repositoryClass],
                $stub
            );
            File::ensureDirectoryExists(dirname(app_path("Repositories/Contracts/{$repositoryClass}Interface.php")));
            File::put(app_path("Repositories/Contracts/{$repositoryClass}Interface.php"), $content);
            $this->info("✅ Repository created: Repositories/Contracts/{$repositoryClass}Interface.php");
        }

        // Repository
        if (File::exists($repositoryPath) && !$force) {
            $this->warn("❗ Repository already exists: {$repositoryPath}");
        } else {
            $stub = File::get(app_path('stubs/repository.stub'));
            $content = str_replace(
                ['{{ class }}', '{{ model }}'],
                [$repositoryClass, $model],
                $stub
            );
            File::ensureDirectoryExists(dirname($repositoryPath));
            File::put($repositoryPath, $content);
            $this->info("✅ Repository created: Repositories/{$repositoryClass}.php");
        }

        // Test
        if (File::exists($testPath) && !$force) {
            $this->warn("❗ Test already exists: {$testPath}");
        } else {
            $stub = File::get(app_path('stubs/tests/repository.test.stub'));
            $content = str_replace(
                ['{{ class }}', '{{ model }}', '{{ model_variable }}'],
                [$repositoryClass, $model, strtolower($model)],
                $stub
            );
            File::ensureDirectoryExists(dirname($testPath));
            File::put($testPath, $content);
            $this->info("🧪 Test created: {$testPath}");
        }

        if (!File::exists(app_path("Models/{$model}.php"))) {
            $this->warn("❗Model not exists: {$model}.");
            $this->call('generate:model', [
                'name' => $model
            ]);
        }
        else{
            $this->call('generate:model', [
                'name' => $model,
                '--force' => true
            ]);
        }

        /* $interface = "\\App\\Repositories\\Contracts\\{$name}RepositoryInterface";
        $implementation = "\\App\\Repositories\\{$name}Repository";

        $this->updateServiceProvider($interface, $implementation); */


        $this->call('test', [
            '--filter' => $repositoryClass . "Test",
        ]);

        return self::SUCCESS;
    }

    protected function updateServiceProvider(string $interface, string $implementation): void
    {

        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        if (!file_exists($providerPath)) {
            $this->error("RepositoryServiceProvider.php not found!");
            return;
        }

        $content = file_get_contents($providerPath);

        $bindLine = "\$this->app->bind({$interface}::class, {$implementation}::class);";

        if (strpos($content, $bindLine) !== false) {
            $this->info("Binding already exists in RepositoryServiceProvider.");
            return;
        }

        $pattern = '/public function register\s*\([^\)]*\)\s*:\s*[^{]*\{(.*?)\}/s';

        if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            $registerBody = $matches[1][0];
            $registerStartPos = $matches[1][1];
            $registerEndPos = $registerStartPos + strlen($registerBody);

            $insertPos = $registerEndPos - 1; // before last }

            $indent = "\n        "; // 8 spaces indent

            $newContent = substr_replace($content, $indent . $bindLine . "\n", $insertPos, 0);

            file_put_contents($providerPath, $newContent);

            $this->info("✅ RepositoryServiceProvider updated with new binding.");
        } else {
            $this->error("Could not find register() method in RepositoryServiceProvider.");
        }

        return;

        $providerPath = app_path('Providers/RepositoryServiceProvider.php');

        if (!file_exists($providerPath)) {
            $this->error("RepositoryServiceProvider.php not found!");
            return;
        }

        $content = file_get_contents($providerPath);

        // Prepare the bind line, e.g.
        // $this->app->bind(\App\Repositories\Contracts\MyModelRepositoryInterface::class, \App\Repositories\MyModelRepository::class);

        $bindLine = "\$this->app->bind({$interface}::class, {$implementation}::class);";

        // Check if the line already exists
        if (strpos($content, $bindLine) !== false) {
            $this->info("Binding already exists in RepositoryServiceProvider.");
            return;
        }

        // Find the register() method body closing brace position to insert before it
        $pattern = '/public function register\(\)\s*\{\s*(.*?)\s*\}/s';

        if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            $registerBody = $matches[1][0];
            $registerStartPos = $matches[1][1];
            $registerEndPos = $registerStartPos + strlen($registerBody);

            // Insert binding before the closing brace of register()
            $insertPos = $registerEndPos - 1; // before last }

            // Add newline + indent + bindLine
            $indent = "\n        "; // 8 spaces indent (2 tabs approx)
            $newContent = substr_replace($content, $indent . $bindLine . "\n", $insertPos, 0);

            file_put_contents($providerPath, $newContent);

            $this->info("✅ RepositoryServiceProvider updated with new binding.");
        } else {
            $this->error("Could not find register() method in RepositoryServiceProvider.");
        }
    }

}