<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('resource_permissions')) {
            Schema::create('resource_permissions', function (Blueprint $table) {
                $table->id();
                $table->morphs('permissionable'); // permissionable_id, permissionable_type
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->enum('permission_type', ['view', 'edit', 'download', 'share', 'delete', 'upload']);
                $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('inherit_to_children')->default(false); // pour les dossiers
                $table->timestamps();
                $table->softDeletes();

                // Index pour optimiser les requêtes
                $table->index(['permissionable_type', 'permissionable_id'], 'resource_permissions_perm_type_id_index');
                $table->index(['user_id', 'permission_type']);
                $table->index(['is_active', 'expires_at']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('resource_permissions');
    }
};
