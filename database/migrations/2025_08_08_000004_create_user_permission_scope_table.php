<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_permission_scope', function (Blueprint $table) {
            $table->id();

            // Relations de base
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');

            // Type d'objet sur lequel s'applique la permission
            $table->string('object_type'); // 'project', 'tdr', 'rapport', 'idee_projet', etc.

            // Étape de workflow (optionnel)
            $table->string('workflow_stage')->nullable(); // 'draft', 'submission', 'validation', 'etude', 'approved'

            // Scopes organisationnels (polymorphe)
            $table->morphs('scopeable'); // scopeable_type, scopeable_id
            // Exemples de scopeable_type:
            // - 'App\Models\Organisation' (ministère, agence, institution)
            // - 'App\Models\Secteur' (secteur, sous-secteur)
            // - 'App\Models\CategorieProjet'
            // - 'App\Models\Projet' (permission sur un projet spécifique)

            // Métadonnées
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index pour optimiser les requêtes de vérification de permissions
            $table->index(['user_id', 'permission_id', 'object_type']);
            $table->index(['user_id', 'object_type', 'workflow_stage']);
            $table->index(['scopeable_type', 'scopeable_id'], 'user_perm_scope_type_id_idx');
            $table->index(['is_active', 'expires_at']);

            // Index composite pour les requêtes complexes
            $table->index(['user_id', 'permission_id', 'object_type', 'workflow_stage'], 'user_perm_obj_workflow_idx');
            $table->index(['user_id', 'scopeable_type', 'scopeable_id'], 'user_scope_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_permission_scope');
    }
};