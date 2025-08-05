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
        if (!Schema::hasTable('evaluation_evaluateurs')) {
            Schema::create('evaluation_evaluateurs', function (Blueprint $table) {
                $table->id();
                $table->enum('statut', [-1, 0, 1])->default(-1);
                $table->timestamp("demarrer_le");
                $table->timestamp("terminer_le")->nullable();
                $table->boolean("est_valider")->nullable();
                $table->bigInteger('evaluateur_id')->unsigned();
                $table->foreign('evaluateur_id')->references('id')->on('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->bigInteger('evaluation_id')->unsigned();
                $table->foreign('evaluation_id')->references('id')->on('evaluations')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_evaluateurs');
    }
};
