<?php

namespace App\Enums;

enum TypesTemplate: string
{
    case evaluation = 'evaluation';
    case checklist = 'checklist';
    case document = 'document';
    case formulaire = 'formulaire';
    case tableau = 'tableau';

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