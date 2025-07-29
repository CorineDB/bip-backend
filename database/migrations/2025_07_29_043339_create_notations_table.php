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
        if (!Schema::hasTable('notations')) {
            Schema::create('notations', function (Blueprint $table) {
                $table->id();
                $table->string('libelle');
                $table->string('valeur');
                $table->longText('commentaire')->nullable();

                $table->bigInteger('critere_id')->nullable()->unsigned();
                $table->foreign('critere_id')->references('id')->on('criteres')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->bigInteger('categorie_critere_id')->unsigned();
                $table->foreign('categorie_critere_id')->references('id')->on('categories_critere')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->timestamps();
                $table->softDeletes();

                // Suppression de l'ancienne contrainte unique si elle existe (à adapter selon ton cas)
                $this->dropUniqueIfExists(table: 'notations', constraint: 'unique_annotation_per_categorie_critere');

                // Contrainte unique composée
                $table->unique(['libelle', 'valeur', 'critere_id', 'categorie_critere_id'], 'unique_annotation_per_categorie_critere');

            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notations');
    }
};
