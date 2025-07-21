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
        Schema::table('financements', function (Blueprint $table) {
            if(Schema::hasColumn('financements', 'financementId')){
                $table->bigInteger('financementId')->nullable()->unsigned()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financements', function (Blueprint $table) {
            if(Schema::hasColumn('financements', 'financementId')){
                $table->bigInteger('financementId')->nullable(false)->unsigned()->change();
            }
        });
    }
};
