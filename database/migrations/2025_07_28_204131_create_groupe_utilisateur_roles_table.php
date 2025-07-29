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
        Schema::create('groupe_utilisateur_roles', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('roleId')->unsigned();
            $table->bigInteger('groupeUtilisateurId')->unsigned();

            $table->foreign('roleId')->references('id')->on('roles')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('groupeUtilisateurId')->references('id')->on('groupes_utilisateur')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groupe_utilisateur_roles');
    }
};
