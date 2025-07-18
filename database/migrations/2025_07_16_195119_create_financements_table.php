<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('financements')) {
            Schema::create('financements', function (Blueprint $table) {
                $table->id();
                $table->longText('nom')->unique();
                $table->longText('nom_usuel');
                $table->string('slug')->unique()->index();
                $table->enum('type', ['type', 'nature', 'source'])->default('source');
                $table->bigInteger('financementId')->unsigned();
                $table->foreign('financementId')->references('id')->on('financements')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('financements');
    }
};
