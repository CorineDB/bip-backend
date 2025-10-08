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
     * Generate a unique slug for a given model and field
     */
    public static function exist(string $text, string $modelClass, string $field = 'slug', ?int $excludeId = null): string
    {
        $baseSlug = self::generate($text);
        $slug = $baseSlug;
        $counter = 1;

        $query = $modelClass::where($field, $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Generate a clean file path for storage, removing accents and special characters
     */
    public static function generateFilePath(string $path): string
    {
        // Remove accents and special characters but preserve path separators
        $cleanPath = self::removeAccents($path);

        // Replace spaces and special characters with underscores, but preserve slashes
        $cleanPath = preg_replace('/[^a-zA-Z0-9\/_\-]/', '_', $cleanPath);

        // Remove multiple consecutive underscores
        $cleanPath = preg_replace('/_+/', '_', $cleanPath);

        // Clean up any trailing underscores in path segments
        $segments = explode('/', $cleanPath);
        $segments = array_map(function($segment) {
            return trim($segment, '_');
        }, $segments);

        return implode('/', array_filter($segments));
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

    /**
     * Remove accents from text
     */
    public static function rmAccents(string $text): string
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
