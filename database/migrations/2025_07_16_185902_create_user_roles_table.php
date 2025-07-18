<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('roleId')->unsigned();
                $table->foreign('roleId')->references('id')->on('roles')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->bigInteger('userId')->unsigned();
                $table->foreign('userId')->references('id')->on('users')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
