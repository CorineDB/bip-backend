<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('statuts')) {
            Schema::create('statuts', function (Blueprint $table) {
                $table->id();
                $table->longText('statut');
                $table->timestamp('date')->nullable();
                $table->morphs('statutable');
                $table->longText('avis')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('statuts');
    }
};
