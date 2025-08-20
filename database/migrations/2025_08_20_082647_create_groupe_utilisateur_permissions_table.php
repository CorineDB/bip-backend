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
        Schema::create('groupe_utilisateur_permissions', function (Blueprint $table) {
            $table->id();

            $table->boolean("actif");
            $table->unsignedBigInteger('groupeUtilisateurId')->index();
            $table->unsignedBigInteger('permissionId')->index();

            $table->foreign('groupeUtilisateurId')->references('id')->on('groupes_utilisateur')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('permissionId')->references('id')->on('permissions')
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
        Schema::dropIfExists('groupe_utilisateur_permissions');
    }
};
