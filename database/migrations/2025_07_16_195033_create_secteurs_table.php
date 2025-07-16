<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('secteurs')) {
            Schema::create('secteurs', function (Blueprint $table) {
                $table->id();
                $table->boolean('est_grand_secteur');
                $table->longText('secteur');
                $table->string('slug')->unique()->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }
        
        if (Schema::hasTable('secteurs')) {
            Schema::table('secteurs', function (Blueprint $table) {
                $table->bigInteger('secteurId')->unsigned();
                $table->foreign('secteurId')->references('id')->on('secteurs')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('secteurs');
    }
};
