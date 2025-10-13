<?php

namespace App\Enums;

enum SousPhaseIdee: string
{
    case redaction = 'redaction';
    case analyse_idee = 'analyse_idee';
    case etude_de_profil = 'etude_de_profil';
    case etude_de_prefaisabilite = 'etude_de_prefaisabilite';
    case faisabilite = 'etude_de_faisabilite';
    case redaction_rapport_evaluation_ex_ante = 'redaction_rapport_evaluation_ex_ante';
    case selection = 'selection';
    case programmation = 'programmation';
    case financement = 'financement';
    case execution = 'execution';

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
