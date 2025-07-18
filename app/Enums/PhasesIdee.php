<?php

namespace App\Enums;

enum PhasesIdee: string
{
    case identification = 'identification';
    case evaluation_ex_tante = 'evaluation_ex_tante';
    case selection = 'selection';

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