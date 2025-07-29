<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('evaluation_criteres')) {
            Schema::create('evaluation_criteres', function (Blueprint $table) {
                $table->id();
                $table->string('note');
                $table->bigInteger('evaluateur_id')->unsigned();
                $table->foreign('evaluateur_id')->references('id')->on('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->bigInteger('notation_id')->unsigned();
                $table->foreign('notation_id')->references('id')->on('notations')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

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
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_criteres');
    }
};
