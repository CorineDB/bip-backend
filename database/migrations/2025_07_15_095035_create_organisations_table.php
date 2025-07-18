<?php

use App\Enums\EnumTypeOrganisation;
use App\Enums\EnumTypeSecteur;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('organisations')) {
            Schema::create('organisations', function (Blueprint $table) {
                $table->id();
                $table->text('nom', 255)->unique();
                $table->string('slug', 255)->unique();
                $table->longText('description')->nullable();
                $table->enum('type', EnumTypeOrganisation::values())->default(EnumTypeOrganisation::ETATIQUE);
                $table->bigInteger('parentId')->nullable()->unsigned();
                $table->foreign('parentId')->references('id')->on('organisations')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('organisations');
    }
};
