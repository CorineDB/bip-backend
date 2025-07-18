<?php

use App\Enums\EnumTypeChamp;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('champs')) {
            Schema::create('champs', function (Blueprint $table) {
                $table->id();
                $table->text('label');
                $table->text('info')->nullable();
                $table->string('attribut');
                $table->text('placeholder')->nullable();
                $table->boolean('is_required');
                $table->string('default_value')->nullable();
                $table->boolean('isEvaluated')->default(false);
                $table->longText('commentaire')->nullable();
                $table->integer('ordre_affichage')->default(0);
                $table->enum('type_champ', EnumTypeChamp::values())->default(EnumTypeChamp::TEXT);
                $table->bigInteger('secteurId')->nullable()->unsigned();
                $table->foreign('secteurId')->references('id')->on('champs_sections')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->bigInteger('documentId')->nullable()->unsigned();
                $table->foreign('documentId')->references('id')->on('documents')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');

                $table->unique(['attribut', 'secteurId', 'documentId']);

                $table->jsonb('meta_options')->nullable();
                $table->jsonb('champ_config')->nullable();
                $table->jsonb('valeur_config')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('champs');
    }
};
