<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('decisions')) {
            Schema::table('decisions', function (Blueprint $table) {
                if (!Schema::hasColumn('decisions', 'objet_decision_id')) {
                    $table->unsignedBigInteger('objet_decision_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('decisions', 'objet_decision_type')) {
                    $table->string('objet_decision_type')->nullable()->after('objet_decision_id');
                }
                
                // Index pour optimiser les requêtes polymorphiques
                if (!Schema::hasIndex('decisions', ['objet_decision_id', 'objet_decision_type'])) {
                    $table->index(['objet_decision_id', 'objet_decision_type'], 'decisions_objet_index');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('decisions')) {
            Schema::table('decisions', function (Blueprint $table) {
                if (Schema::hasIndex('decisions', 'decisions_objet_index')) {
                    $table->dropIndex('decisions_objet_index');
                }
                if (Schema::hasColumn('decisions', 'objet_decision_type')) {
                    $table->dropColumn('objet_decision_type');
                }
                if (Schema::hasColumn('decisions', 'objet_decision_id')) {
                    $table->dropColumn('objet_decision_id');
                }
            });
        }
    }
};
