<?php

namespace App\Helpers;

class SlugHelper
{
    /**
     * Generate a clean slug from text, removing accents and special characters
     */
    public static function generate(string $text): string
    {
        // Remove accents and special characters
        $slug = self::removeAccents($text);
        $slug = preg_replace('/[^a-z0-9\-]/', '-', strtolower($slug));
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return $slug;
    }

    /**
     * Generate a unique slug for a given model and field
     */
    public static function generateUnique(string $text, string $modelClass, string $field = 'slug', ?int $excludeId = null): string
    {
        $baseSlug = self::generate($text);
        $slug = $baseSlug;
        $counter = 1;

        $query = $modelClass::where($field, $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
            
            $query = $modelClass::where($field, $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Remove accents from text
     */
    private static function removeAccents(string $text): string
    {
        $accents = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C', 'Ñ' => 'N'
        ];
        
        return strtr($text, $accents);
    }
}