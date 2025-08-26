<?php

namespace App\Enums;

enum TypesCanevas: string
{
    case canevas_suivi_evaluation = 'canevas_suivi_evaluation';
    case canevas_analyse = 'canevas_analyse';
    case canevas_etude = 'canevas_etude';
    case canevas_guide = 'canevas_guide';
    case canevas_checklist_redaction = 'canevas_checklist_redaction';

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