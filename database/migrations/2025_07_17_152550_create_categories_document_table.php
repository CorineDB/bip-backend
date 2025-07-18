<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('categories_document')) {
            Schema::create('categories_document', function (Blueprint $table) {
                $table->id();
                $table->text('nom')->unique();
                $table->string('slug')->unique()->index();
                $table->longText('description')->nullable();
                $table->string('format');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categories_document');
    }
};
