<?php

namespace App\Console\Commands\Generators;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

//#[\Illuminate\Console\Attributes\AsCommand(name: 'generate:migration')]
class MakeMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:migration {name} {--table=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a migration and associated test';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = Str::snake($this->argument('name'));
        $tableOption = $this->option('table');
        $force = $this->option('force');
        $tableName = $tableOption ? $tableOption : Str::snake(Str::plural($name));
        $className = Str::studly("Create_{$tableName}_table");
        $timestamp = Carbon::now()->format('Y_m_d_His');

        if ($this->migrationExists($tableName) && ! $force) {
            $this->warn("❗ Migration for table '{$tableName}' already exists. Use --force to overwrite.");
            //return self::FAILURE;
        }
        else{
            $migrationFilename = "{$timestamp}_create_{$tableName}_table.php";
            $migrationPath = database_path("migrations/{$migrationFilename}");

            // ✅ Create migration file
            $migrationStub = File::get(app_path('stubs/migration.stub'));
            $migrationContent = str_replace('{{ table }}', $tableName, $migrationStub);

            File::put($migrationPath, $migrationContent);
            $this->info("✅ Migration created: {$migrationPath}");
        }


        $testClassName = Str::studly("{$name}Migration");
        $testPath = base_path("tests/Integration/Migrations/{$className}Test.php");

        // ✅ Check if test file already exists
        if (File::exists($testPath) && ! $force) {
            $this->warn("❗ Test already exists: {$className}Test.php. Use --force to overwrite.");
            return self::FAILURE;
        }else{
            // ✅ Create migration test file
            $testStub = File::get(app_path('stubs/tests/migration.test.stub'));
            $testContent = str_replace(
                ['{{ class }}', '{{ table }}', '{{ migrationPath }}'],
                [$testClassName, $tableName, "database/migrations/{$migrationFilename}"],
                $testStub
            );

            File::ensureDirectoryExists(dirname($testPath));
            File::put($testPath, $testContent);
            $this->info("🧪 Test created: {$testPath}");
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
