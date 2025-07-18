<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('types_programme')) {
            Schema::create('types_programme', function (Blueprint $table) {
                $table->id();
                $table->longText('type_programme')->unique();
                $table->string('slug')->unique()->index();
                $table->bigInteger('typeId')->nullable()->unsigned();
                $table->foreign('typeId')->references('id')->on('types_programme')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('types_programme');
    }
};
