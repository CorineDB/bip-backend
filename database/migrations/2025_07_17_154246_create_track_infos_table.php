<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('track_infos')) {
            Schema::create('track_infos', function (Blueprint $table) {
                $table->id();
                $table->jsonb('update_data')->nullable();
                $table->morphs('track_info');
                $table->longText('description')->nullable();
                $table->timestamp('createdAt');
                $table->bigInteger('createdBy')->nullable()->unsigned();
                $table->foreign('createdBy')->references('id')->on('users')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamp('updatedAt')->nullable();
                $table->bigInteger('updateBy')->nullable()->unsigned();
                $table->foreign('updateBy')->references('id')->on('users')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('track_infos');
    }
};
