<?php

namespace Tests\Integration;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    protected string $migrationPath = 'database/migrations/2025_07_15_000000_create_generic_table.php';
    protected string $tableName = 'generic_table';

    /**
     * Test that the generic_table migration runs successfully and creates expected columns.
     */
    public function test_generic_table_migration_runs_successfully_and_creates_columns(): void
    {
        // Ensure the table does not exist before running the migration
        Schema::dropIfExists($this->tableName);
        $this->assertFalse(Schema::hasTable($this->tableName));

        // Run the specific migration for generic_table
        Artisan::call('migrate', [
            '--path' => $this->migrationPath,
            '--force' => true
        ]);

        // Assert that the table now exists
        $this->assertTrue(Schema::hasTable($this->tableName));

        // Assert that expected columns exist
        $this->assertTrue(Schema::hasColumn($this->tableName, 'id'));
        $this->assertTrue(Schema::hasColumn($this->tableName, 'name'));
        $this->assertTrue(Schema::hasColumn($this->tableName, 'description'));
        $this->assertTrue(Schema::hasColumn($this->tableName, 'created_at'));
        $this->assertTrue(Schema::hasColumn($this->tableName, 'updated_at'));

        // Rollback the migration
        Artisan::call('migrate:rollback', [
            '--path' => $this->migrationPath,
            '--force' => true
        ]);

        // Assert that the table no longer exists after rollback
        $this->assertFalse(Schema::hasTable($this->tableName));
    }

    /**
     * Test that the migration does not create the table if it already exists.
     */
    public function test_generic_table_migration_does_not_create_if_exists(): void
    {
        // Create the table manually before running the migration
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
        });
        $this->assertTrue(Schema::hasTable($this->tableName));

        // Run the specific migration (it should not re-create or error)
        Artisan::call('migrate', [
            '--path' => $this->migrationPath,
            '--force' => true
        ]);

        // Assert that the table still exists (and no error occurred)
        $this->assertTrue(Schema::hasTable($this->tableName));

        // Clean up: drop the table after the test
        Schema::dropIfExists($this->tableName);
    }

    /**
     * Test that rolling back the migration removes the table and its columns.
     */
    public function test_generic_table_migration_rollback_removes_table_and_columns(): void
    {
        // Ensure the table does not exist before running the migration
        Schema::dropIfExists($this->tableName);

        // Run the migration
        Artisan::call('migrate', [
            '--path' => $this->migrationPath,
            '--force' => true
        ]);
        $this->assertTrue(Schema::hasTable($this->tableName));

        // Assert columns exist before rollback
        $this->assertTrue(Schema::hasColumn($this->tableName, 'name'));
        $this->assertTrue(Schema::hasColumn($this->tableName, 'description'));

        // Rollback the migration
        Artisan::call('migrate:rollback', [
            '--path' => $this->migrationPath,
            '--force' => true
        ]);

        // Assert that the table no longer exists
        $this->assertFalse(Schema::hasTable($this->tableName));

        // Assert that columns do not exist after rollback (implicitly, as table is gone)
        $this->assertFalse(Schema::hasColumn($this->tableName, 'name'));
        $this->assertFalse(Schema::hasColumn($this->tableName, 'description'));
    }
}