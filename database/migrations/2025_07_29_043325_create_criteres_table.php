<?php

use App\Services\Traits\HelperTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HelperTrait;

    public function up(): void
    {
        if (!Schema::hasTable('criteres')) {
            Schema::create('criteres', function (Blueprint $table) {
                $table->id();
                $table->longText('intitule');
                $table->float('ponderation')->default(0);
                $table->longText('commentaire')->nullable();
                $table->boolean('is_mandatory')->default(false);

                $table->bigInteger('categorie_critere_id')->nullable()->unsigned();
                $table->foreign('categorie_critere_id')->references('id')->on('categories_critere')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();

                // Suppression de l'ancienne contrainte unique si elle existe (à adapter selon ton cas)
                $this->dropUniqueIfExists(table: 'criteres', constraint: 'unique_critere_nom_per_categorie');

                // Contrainte unique composée
                $table->unique(['intitule', 'categorie_critere_id'], 'unique_critere_nom_per_categorie');

            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('criteres');
    }
};
