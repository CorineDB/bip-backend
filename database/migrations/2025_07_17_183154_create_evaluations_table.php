<?php

use App\Enums\StatutIdee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        DB::table('evaluations')->truncate();

        if (!Schema::hasTable('evaluations')) {
            Schema::create('evaluations', function (Blueprint $table) {
                $table->id();

                $table->string('type_evaluation')->default("climatique");

                $table->timestamps("date_debut_evaluation");
                $table->timestamps("date_fin_evaluation")->nullable();
                $table->timestamps("valider_le")->nullable();

                $table->morphs("projetable");
                $table->bigInteger('evaluateur_id')->nullable()->unsigned();
                $table->foreign('evaluateur_id')->references('id')->on('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->bigInteger('valider_par')->nullable()->unsigned();
                $table->foreign('valider_par')->references('id')->on('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->text('commentaire')->nullable();
                $table->json("evaluation");
                $table->json("resultats_evaluation");
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
