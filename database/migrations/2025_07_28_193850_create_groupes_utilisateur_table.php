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
        Schema::create('groupes_utilisateur', function (Blueprint $table) {
            $table->id();
            $table->text('nom', 255);
            $table->string('slug', 255);
            $table->longText('description')->nullable();
            $table->nullableMorphs('profilable');
            $table->timestamps();
            $table->softDeletes();

            // Suppression de l'ancienne contrainte unique si elle existe (à adapter selon ton cas)
            $this->dropUniqueIfExists(table: 'roles', constraint: 'unique_groupe_nom_per_profilable');

            // Contrainte unique composée
            $table->unique(['nom', 'slug', 'profilable_type', 'profilable_id'], 'unique_groupe_nom_per_profilable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groupes_utilisateur');
    }
};
