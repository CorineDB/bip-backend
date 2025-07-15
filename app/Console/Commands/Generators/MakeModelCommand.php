<?php

namespace App\Console\Commands\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModelCommand extends Command
{
    protected $signature = 'generate:model {name} {--table=} {--force}';

    protected $description = 'Generate a Laravel model and its unit test with optional custom table name and force overwrite';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $tableOption = $this->option('table');
        $force = $this->option('force');

        $tableName = $tableOption ? $tableOption : Str::snake(Str::plural($name));

        $modelFilename = app_path("Models/{$name}.php");
        $testClassName = Str::studly("{$name}Test");
        $testFilename = base_path("tests/Unit/Models/{$name}Test.php");

        if (File::exists($modelFilename) && ! $force) {
            $this->warn("❗ Model already exists: {$modelFilename}. Use --force to overwrite.");
            //return self::FAILURE;
        }
        else{
            $modelStub = File::get(app_path('stubs/model.stub'));
            $modelContent = str_replace(['{{ class }}', '{{ table }}'], [$name, $tableName], $modelStub);
            File::put($modelFilename, $modelContent);
            $this->info("✅ Model created: {$modelFilename}");
        }

        if (File::exists($testFilename) && ! $force) {
            $this->warn("❗ Test already exists: {$testFilename}. Use --force to overwrite.");
            //return self::FAILURE;
        }
        else{
            $testStub = File::get(app_path('stubs/tests/model.test.stub'));
            $testContent = str_replace('{{ class }}', $name, $testStub);
            File::ensureDirectoryExists(dirname($testFilename));
            File::put($testFilename, $testContent);
            $this->info("🧪 Test created: {$testFilename}");
        }


        if (!$this->migrationExists($tableName)) {
            $this->warn("❗La Migration for table '{$tableName}' not exists. ");

            // Exécute la commande de génération de migration
            $this->call('generate:migration', [
                'name' => $tableName
            ]);
        }
        else{
            // Soit la migration existe, soit --force est spécifié, on régénère avec force
            $this->call('generate:migration', [
                'name' => $tableName,
                '--force' => true
            ]);
        }

        /* $this->call('test', [
            '--filter' => $testClassName,
        ]); */

        return self::SUCCESS;
    }

    protected function migrationExists(string $tableName): bool
    {
        $migrationFiles = File::files(database_path('migrations'));

        foreach ($migrationFiles as $file) {
            if (Str::contains($file->getFilename(), "create_{$tableName}_table")) {
                return true;
            }
        }

        return false;
    }
}