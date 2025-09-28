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
        if (Schema::hasTable('notes_conceptuelle')) {
            Schema::table('notes_conceptuelle', function (Blueprint $table) {
                if (!Schema::hasColumn('notes_conceptuelle', 'canevas_redaction_note_conceptuelle')) {
                    $table->json('canevas_redaction_note_conceptuelle')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('notes_conceptuelle')) {
            Schema::table('notes_conceptuelle', function (Blueprint $table) {
                if (Schema::hasColumn('notes_conceptuelle', 'canevas_redaction_note_conceptuelle')) {
                    $table->dropColumn('canevas_redaction_note_conceptuelle');
                }
            });
        }
    }
};
