<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('natures_financement')) {
            Schema::create('natures_financement', function (Blueprint $table) {
                $table->id();
                $table->longText('nature_financement')->unique();
                $table->string('slug')->unique()->index();
                $table->bigInteger('typeFinancementId')->unsigned();
                $table->foreign('typeFinancementId')->references('id')->on('types_financement')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('natures_financement');
    }
};
