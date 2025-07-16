<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('types_intervention')) {
            Schema::create('types_intervention', function (Blueprint $table) {
                $table->id();
                $table->longText('type_intervention')->unique();
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
        Schema::dropIfExists('types_intervention');
    }
};
