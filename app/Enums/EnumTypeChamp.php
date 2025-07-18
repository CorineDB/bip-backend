<?php

namespace App\Enums;

enum EnumTypeChamp: string
{

    case    TEXT           = 'text';
    case    TEXTAREA           = 'textarea';
    case    NUMBER         = 'number';
    case    DATE           = 'date';
    case    BOOLEAN        = 'boolean';
    case    SELECT         = 'select';
    case    MULTISELECT    = 'multiselect';
    case    FILE           = 'file';
    case    GEOLOCATION    = 'geolocation';
    case    RATING         = 'rating';
    case    NUMBER_INPUT   = 'number-input';
    case    CURRENCY_INPUT = 'currency-input';
    case    RADIO_RATING         = 'radio-rating';

    case    SLIDER_NUMBER = 'number_input';
    case    MULTISELECT_CHECKBOX    = 'multiselect-checkbox';
    case    MULTISELECT_FILE    = 'multiselect-file';


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