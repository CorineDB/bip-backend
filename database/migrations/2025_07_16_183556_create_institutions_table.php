<?php

use App\Enums\EnumTypeInstitution;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('institutions')) {
            Schema::create('institutions', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->text('libelle');
                $table->string('slug')->unique()->index();
                $table->mediumText('description')->nullable();
                $table->enum('type', EnumTypeInstitution::cases())->default(EnumTypeInstitution::etatique);
                $table->bigInteger('ministereId')->unsigned();
                $table->foreign('ministereId')->references('id')->on('ministeres')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
