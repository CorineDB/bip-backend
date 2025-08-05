<?php

use App\Services\Traits\HelperTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HelperTrait;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'type')) {
                    $table->string('type')->nullable();
                }
                if (!Schema::hasColumn('users', 'profilable_id')) {
                    $table->bigInteger('profilable_id')->nullable()->unsigned();
                }
                if (!Schema::hasColumn('users', 'profilable_type')) {
                    $table->string('profilable_type')->nullable();
                }
                if (!Schema::hasColumn('users', 'account_verification_request_sent_at')) {
                    $table->timestamp('account_verification_request_sent_at')->nullable();
                }
                if (!Schema::hasColumn('users', 'password_update_at')) {
                    $table->timestamp('password_update_at')->nullable();
                }

                if (!Schema::hasColumn('users', 'last_password_remember')) {
                    $table->string('last_password_remember')->nullable();
                }

                if (!Schema::hasColumn('users', 'token')) {
                    $table->string('token')->nullable();
                }
                if (!Schema::hasColumn('users', 'link_is_valide')) {
                    $table->boolean('link_is_valide')->default(0);
                }

                if (!Schema::hasColumn('users', 'lastRequest')) {
                    $table->datetime('lastRequest')->nullable();
                }
                if (Schema::hasColumn('users', 'roleId')) {
                    $table->bigInteger('roleId')->nullable()->unsigned()->change();
                }
            });
        }

        if (Schema::hasTable('personnes')) {
            Schema::table('personnes', function (Blueprint $table) {
                if (Schema::hasColumn('personnes', 'organismeId')) {
                    $table->bigInteger('organismeId')->nullable()->unsigned()->change();
                }
                //
            });
        }

        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {

                // Suppression des index/unique si ils existent
                // Attention Laravel ne fournit pas de méthode pour vérifier l'existence d'un index,
                // donc il faudra gérer les erreurs ou utiliser DB::statement si besoin.

                if (Schema::hasColumn('roles', 'nom')) {
                    $this->dropUniqueIfExists(table: 'roles', column: 'nom');
                    $table->string('nom')->change();
                }

                if (Schema::hasColumn('users', 'slug')) {

                    $this->dropUniqueIfExists(table: 'roles', column: 'slug');

                    $table->string('slug')->change();
                }

                // Suppression de l'ancienne contrainte unique si elle existe (à adapter selon ton cas)
                $this->dropUniqueIfExists(table: 'roles', constraint: 'unique_role_nom_per_roleable');

                // Ajouter contrainte unique (nom + roleable_type + roleable_id)
                $table->unique(['nom', 'slug', 'roleable_type', 'roleable_id'], 'unique_role_nom_per_roleable');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'profilable_id')) {
                    $table->dropColumn('profilable_id');
                }
                if (Schema::hasColumn('users', 'profilable_type')) {
                    $table->dropColumn('profilable_type');
                }
                if (Schema::hasColumn('users', 'account_verification_request_sent_at')) {
                    $table->dropColumn('account_verification_request_sent_at');
                }
                if (Schema::hasColumn('users', 'token')) {
                    $table->dropColumn('token');
                }
                if (Schema::hasColumn('users', 'link_is_valide')) {
                    $table->dropColumn('link_is_valide');
                }
                if (Schema::hasColumn('users', 'password_update_at')) {
                    $table->dropColumn('password_update_at');
                }
                if (Schema::hasColumn('users', 'last_password_remember')) {
                    $table->dropColumn('last_password_remember');
                }
                if (Schema::hasColumn('users', 'roleId')) {
                    $table->bigInteger('roleId')->unsigned()->change();
                }
            });
        }

        if (Schema::hasTable('personnes')) {
            Schema::table('personnes', function (Blueprint $table) {
                if (Schema::hasColumn('personnes', 'organismeId')) {
                    $table->bigInteger('organismeId')->unsigned()->nullable()->change();
                }
                //
            });
        }

        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                // Supprime la contrainte unique créée dans up()
                $this->dropUniqueIfExists(table: 'roles', constraint: 'unique_role_nom_per_roleable');
                if (Schema::hasColumn('users', 'nom')) {
                    $table->string('nom')->unique()->change();
                }
                if (Schema::hasColumn('users', 'slug')) {
                    $table->string('slug')->unique()->index()->change();
                }
                // Tu peux aussi remettre les index/unique précédents si besoin ici
            });
        }
    }
};
