<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('roleId')->unsigned();
                $table->bigInteger('permissionId')->unsigned();
                $table->foreign('roleId')->references('id')->on('roles')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->foreign('permissionId')->references('id')->on('permissions')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
