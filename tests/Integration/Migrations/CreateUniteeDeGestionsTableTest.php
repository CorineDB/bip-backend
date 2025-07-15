<?php

namespace Tests\Integration\Migrations;

use Illuminate\Container\Attributes\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UniteeDeGestionsMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected string $migrationPath = 'database/migrations/2025_07_15_213340_create_unitee_de_gestions_table.php';
    protected string $tableName = 'unitee_de_gestions';
    protected string $table = 'unitee_de_gestions';

    protected function setUp(): void
    {
        parent::setUp();

        // Reset database state manually
        Artisan::call('migrate:fresh');
    }

    public function test_unitee_de_gestions_table_exists(): void
    {
        Artisan::call('migrate', ['--path' => $this->migrationPath]);
        $this->assertTrue(Schema::hasTable($this->table));
    }

    /**
     * Test that the generic_table migration runs successfully and creates expected columns.
     */
    public function test_unitee_de_gestions_table_migration_runs_successfully_and_creates_columns(): void
    {
        Artisan::call('migrate', ['--path' => $this->migrationPath]);

        $this->assertTrue(Schema::hasTable($this->table));

        $columns = Schema::getColumnListing($this->table);

        $this->assertContains('id', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
        $this->assertContains('deleted_at', $columns);

        $this->assertTrue(Schema::hasColumn($this->table, 'id'));
        $this->assertTrue(Schema::hasColumn($this->table, 'created_at'));
        $this->assertTrue(Schema::hasColumn($this->table, 'updated_at'));
        $this->assertTrue(Schema::hasColumn($this->table, 'deleted_at'));
    }

    /**
     * Test that the migration does not create the table if it already exists.
     */
    public function test_unitee_de_gestions_table_migration_does_not_create_if_exists(): void
    {
        // Exécuter une première fois
        // Simuler que la table existe déjà, puis relancer
        $this->assertTrue(Schema::hasTable($this->table));
        Artisan::call('migrate', [
            '--path' => $this->migrationPath,
            '--force' => true
        ]);
        $countBefore = DB::table('migrations')->count();

        // Run again
        Artisan::call('migrate', [
            '--path' => $this->migrationPath,
            '--force' => true
        ]);
        $countAfter = DB::table('migrations')->count();

        $this->assertEquals($countBefore, $countAfter);
    }

    public function test_columns_have_correct_types_and_attributes(): void
    {
        Artisan::call('migrate', ['--path' => $this->migrationPath]);

        $columns = DB::select("SELECT column_name, data_type, is_nullable, column_default
                            FROM information_schema.columns
                            WHERE table_name = ?", [$this->table]);

        $columnsMap = collect($columns)->keyBy('column_name');

        $this->assertEquals('integer', $columnsMap['id']->data_type);
        $this->assertEquals('NO', $columnsMap['id']->is_nullable);
        $this->assertStringContainsString('nextval', $columnsMap['id']->column_default); // serial/auto-increment

        $this->assertEquals('timestamp without time zone', $columnsMap['created_at']->data_type);
        $this->assertEquals('YES', $columnsMap['deleted_at']->is_nullable);
    }

    /**
     * Test that rolling back the migration removes the table and its columns.
     */
    public function test_unitee_de_gestions_table_migration_rollback_removes_table_and_columns(): void
    {
        Artisan::call('migrate', ['--path' => $this->migrationPath]);
        $this->assertTrue(Schema::hasTable($this->table));

        Artisan::call('migrate:rollback', ['--path' => $this->migrationPath]);
        $this->assertFalse(Schema::hasTable($this->table));
    }
}