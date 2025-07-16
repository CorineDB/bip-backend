<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('types_financement')) {
            Schema::create('types_financement', function (Blueprint $table) {
                $table->id();
                $table->longText('type_financement')->unique();
                $table->string('slug')->unique()->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('types_financement');
    }
};
