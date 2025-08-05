<?php

use App\Services\Traits\HelperTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HelperTrait;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        if (Schema::hasTable('evaluation_criteres')) {
            Schema::table('evaluation_criteres', function (Blueprint $table) {
                // Supprimer l'ancienne contrainte unique
                $table->dropUnique('unique_user_evaluations');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('evaluation_criteres')) {
            Schema::table('evaluation_criteres', function (Blueprint $table) {

                // Suppression de l'ancienne contrainte unique si elle existe (à adapter selon ton cas)
                $this->dropUniqueIfExists(table: 'roles', constraint: 'unique_user_evaluations');
            });
        }
    }
};
