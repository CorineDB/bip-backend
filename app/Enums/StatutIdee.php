<?php

namespace App\Enums;

enum StatutIdee: string
{
    case BROUILLON = '00_brouillon';
    case IDEE_DE_PROJET = '01_idee_de_projet';
    case ANALYSE = '02a_analyse';
    case AMC = '02b_amc';
    case VALIDATION = '02c_validation';
    case PROFIL = '03a_profil';
    case ABANDON = '99_abandon';
    case PREFAISABILITE = '1a_prefaisabilite';
    case PRET = '10_pret';
    case TDR_FAISABILITE = '05a_TDR_faisabilité';
    case R_TDR_PREFAISABILITE = '04a_R_TDR_Préfaisabilité';
    case EVALUATION_TDR_F = '05b_Evaluation_TDR_F';
    case SOUMISSION_RAPPORT_F = '05b_SoumissionRapportF';
    case VALIDATION_F = '05c_ValidationF';
    case NOTE_CONCEPTUEL = '03a_NoteConceptuel';
    case VALIDATION_NOTE_AMELIORER = '03cx_ValidationNoteAameliorer';
    case R_VALIDATION_NOTE_AMELIORER = '03c_R_ValidationNoteAameliorer';
    case EVALUATION_NOTE = '03b_EvaluationNote';
    case VALIDATION_PROFIL = '03c_ValidationProfil';
    case R_VALIDATION_PROFIL_NOTE_AMELIORER = '03c_R_ValidationProfilNoteAameliorer';
    case TDR_PREFAISABILITE = '04a_TDR_Prefaisabilité';

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