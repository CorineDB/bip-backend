<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('composants_programme')) {
            Schema::create('composants_programme', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique()->index();
                $table->integer('indice');
                $table->longText('intitule')->unique();
                $table->string('slug')->unique()->index();
                $table->bigInteger('typeId')->unsigned();
                $table->foreign('typeId')->references('id')->on('types_programme')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('composants_programme');
    }
};
