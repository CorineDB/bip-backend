<?php

namespace App\Enums;

enum EnumTypeSecteur: string
{
    case GRAND_SECTEUR = 'grand-secteur';
    case SECTEUR = 'secteur';
    case SOUS_SECTEUR = 'sous-secteur';

    /**
     * Get all enum values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all enum names
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Get key-value array
     */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'name'),
            array_column(self::cases(), 'value')
        );
    }
}