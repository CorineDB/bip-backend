<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('evaluation_champs')->truncate();
        if (!Schema::hasTable('evaluation_champs')) {
            Schema::create('evaluation_champs', function (Blueprint $table) {
                $table->id();
                $table->string('note');
                $table->longText('commentaires')->nullable();
                $table->timestamp('date_note');

                $table->bigInteger('evaluationId')->unsigned();
                $table->foreign('evaluationId')->references('id')->on('evaluations')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');

                $table->bigInteger('champId')->unsigned();
                $table->foreign('champId')->references('id')->on('champs')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_champs');
    }
};
