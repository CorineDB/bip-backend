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
        Schema::table('composants_programme', function (Blueprint $table) {
            if(Schema::hasColumn('composants_programme', 'code')){
                $table->dropColumn('code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('composants_programme', function (Blueprint $table) {
            if(!Schema::hasColumn('composants_programme', 'code')){
                $table->string('code')->default(0)->unique()->index();
            }
        });
    }
};
