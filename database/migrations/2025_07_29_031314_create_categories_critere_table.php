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
        if (!Schema::hasTable('categories_critere')) {
            Schema::create('categories_critere', function (Blueprint $table) {
                $table->id();
                $table->text('type')->unique();
                $table->text('slug')->unique();
                $table->boolean('is_mandatory')->default(false);
                $table->json("criteres_ajustable")->nullable();
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
        Schema::dropIfExists('categories_critere');
    }
};
