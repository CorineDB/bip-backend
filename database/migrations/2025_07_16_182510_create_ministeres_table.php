<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ministeres')) {
            Schema::create('ministeres', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->longText('libelle');
                $table->string('slug')->unique()->index();
                $table->longText('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ministeres');
    }
};
