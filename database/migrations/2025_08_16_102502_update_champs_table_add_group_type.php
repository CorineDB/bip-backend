<?php

use App\Enums\EnumTypeChamp;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE champs DROP CONSTRAINT champs_type_champ_check");
        DB::statement("ALTER TABLE champs ADD CONSTRAINT champs_type_champ_check CHECK (type_champ::text = ANY (ARRAY['" . implode("','", EnumTypeChamp::values()) . "']::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $oldValues = array_diff(EnumTypeChamp::values(), ['group']);
        DB::statement("ALTER TABLE champs DROP CONSTRAINT champs_type_champ_check");
        DB::statement("ALTER TABLE champs ADD CONSTRAINT champs_type_champ_check CHECK (type_champ::text = ANY (ARRAY['" . implode("','", $oldValues) . "']::text[]))");
    }
};
