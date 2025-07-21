<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Trait ForeignKeyConstraints
 * 
 * Simple trait to enable/disable foreign key constraints
 */
trait ForeignKeyConstraints
{
    /**
     * Disable foreign key constraints
     *
     * @return void
     */
    protected function disableForeignKeyChecks(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    }

    /**
     * Enable foreign key constraints
     *
     * @return void
     */
    protected function enableForeignKeyChecks(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}