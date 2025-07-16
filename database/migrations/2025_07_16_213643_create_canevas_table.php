<?php

use App\Enums\TypesTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('canevas')) {
            Schema::create('canevas', function (Blueprint $table) {
                $table->id();
                $table->longText('intitule')->unique();
                $table->string('slug')->unique()->index();
                $table->longText('description')->nullable();
                $table->longText('objectif')->nullable();
                $table->enum('type', TypesTemplate::cases())->default(TypesTemplate::formulaire);
                $table->jsonb('metadata')->nullable();
                $table->jsonb('structure')->nullable();

                $table->bigInteger('categorieId')->nullable()->unsigned();
                $table->foreign('categorieId')->references('id')->on('categories_canevas')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('canevas');
    }
};
