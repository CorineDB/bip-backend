<?php

namespace App\Enums;

enum StatutEvaluationNoteConceptuelle: string
{
    case PASSE = 'passe';
    case RETOUR = 'retour';
    case NON_ACCEPTE = 'non_accepte';

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
     * Get key-value array with labels
     */
    public static function options(): array
    {
        return [
            self::PASSE->value => 'Passé',
            self::RETOUR->value => 'Retour',
            self::NON_ACCEPTE->value => 'Non accepté',
        ];
    }

    /**
     * Get label for a status
     */
    public function label(): string
    {
        return match($this) {
            self::PASSE => 'Passé',
            self::RETOUR => 'Retour',
            self::NON_ACCEPTE => 'Non accepté',
        };
    }

    /**
     * Get color for frontend display
     */
    public function color(): string
    {
        return match($this) {
            self::PASSE => 'success',
            self::RETOUR => 'warning',
            self::NON_ACCEPTE => 'danger',
        };
    }
}