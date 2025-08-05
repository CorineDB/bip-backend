<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Carbon\Carbon;

trait GenerateUniqueId
{
    /**
     * Générer un identifiant BIP unique.
     * Format: BIP-YYYY-XXXXXX (ex: BIP-2025-ABC123)
     */
    public function generateIdentifiantBip(string $prefix = 'BIP'): string
    {
        $year = Carbon::now()->year;
        $uniqueCode = strtoupper(Str::random(6));
        
        return "{$prefix}-{$year}-{$uniqueCode}";
    }

    /**
     * Générer un identifiant avec format personnalisé.
     */
    public function generateCustomId(string $prefix, int $length = 8): string
    {
        $uniqueCode = strtoupper(Str::random($length));
        return "{$prefix}-{$uniqueCode}";
    }

    /**
     * Générer un identifiant numérique séquentiel.
     */
    public function generateSequentialId(string $prefix, int $lastId = 0): string
    {
        $nextId = str_pad($lastId + 1, 6, '0', STR_PAD_LEFT);
        return "{$prefix}-{$nextId}";
    }

    /**
     * Vérifier l'unicité d'un identifiant dans une table.
     */
    public function ensureUniqueId(string $table, string $column, string $id): string
    {
        $originalId = $id;
        $counter = 1;

        while (\DB::table($table)->where($column, $id)->exists()) {
            $id = $originalId . '-' . $counter;
            $counter++;
        }

        return $id;
    }
}