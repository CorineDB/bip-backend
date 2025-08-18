<?php

namespace App\Enums;

enum EnumTypeChamp: string
{

    case    TEXT                    = 'text';
    case    TEXTAREA                = 'textarea';
    case    NUMBER                  = 'number';
    case    NUMERIC                 = 'numeric';
    case    DATE                    = 'date';
    case    BOOLEAN                 = 'boolean';
    case    SELECT                  = 'select';
    case    MULTISELECT             = 'multiselect';
    case    FILE                    = 'file';
    case    GEOLOCATION             = 'geolocation';
    case    RATING                  = 'rating';
    case    NUMBER_INPUT            = 'number_input';
    case    EMAIL                   = 'email';
    case    CURRENCY_INPUT          = 'currency_input';
    case    PHONE_NUMBER_INPUT      = 'phone_number_input';
    case    CHECKBOX                = 'checkbox';
    case    RADIO                   = 'radio';
    case    RADIO_RATING            = 'radio_rating';
    case    SLIDER_NUMBER           = 'slider_number';
    case    MULTISELECT_CHECKBOX    = 'multiselect_checkbox';
    case    MULTISELECT_FILE        = 'multiselect_file';
    case    GROUP                   = 'group';


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