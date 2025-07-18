<?php

use App\Enums\PhasesIdee;
use App\Enums\SousPhaseIdee;
use App\Enums\StatutIdee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('workflows')) {
            Schema::create('workflows', function (Blueprint $table) {
                $table->id();
                $table->enum('statut', StatutIdee::values())->default(StatutIdee::BROUILLON->value);
                $table->enum('phase', PhasesIdee::values())->default(PhasesIdee::identification->value);
                $table->enum('sous_phase', SousPhaseIdee::values())->default(SousPhaseIdee::redaction->value);
                $table->timestamp('date')->nullable();
                $table->morphs('projetable');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
