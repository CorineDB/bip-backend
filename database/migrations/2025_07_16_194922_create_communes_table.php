<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('communes')) {
            Schema::create('communes', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->text('nom');
                $table->string('slug')->unique()->index();
                $table->bigInteger('departementId')->unsigned();
                $table->foreign('departementId')->references('id')->on('departements')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('communes');
    }
};
