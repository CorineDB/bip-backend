<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sources_financement')) {
            Schema::create('sources_financement', function (Blueprint $table) {
                $table->id();
                $table->longText('source_financement')->unique();
                $table->string('slug')->unique()->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sources_financement');
    }
};
