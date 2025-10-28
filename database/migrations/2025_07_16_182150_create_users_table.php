<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('provider')->default('keycloack');
                $table->string('provider_user_id');
                $table->string('username')->unique();
                $table->string('email')->unique();
                $table->enum('status', ['actif', 'suspendu', 'invité'])->default('invité');
                $table->boolean('is_email_verified')->default(false);
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->bigInteger('personneId')->unsigned();
                $table->foreign('personneId')->references('id')->on('personnes')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->bigInteger('roleId')->unsigned();
                $table->foreign('roleId')->references('id')->on('roles')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                $table->timestamp('last_connection')->nullable();
                $table->string('ip_address')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
