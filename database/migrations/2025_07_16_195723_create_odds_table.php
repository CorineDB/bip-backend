<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('odds')) {
            Schema::create('odds', function (Blueprint $table) {
                $table->id();
                $table->longText('odd')->unique();
                $table->string('slug')->unique()->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('odds');
    }
};
