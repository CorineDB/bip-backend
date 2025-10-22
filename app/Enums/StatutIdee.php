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
    case RAPPORT = '09_Rapport';
    case MATURITE = '08_Maturite';
    case TDR_FAISABILITE = '05a_TDR_faisabilite';
    case R_TDR_PREFAISABILITE = '04a_R_TDR_Prefaisabilite';
    case EVALUATION_TDR_F = '05b_Evaluation_TDR_F';
    case R_TDR_FAISABILITE = '05a_R_TDR_faisabilite';

    case SOUMISSION_RAPPORT_F = '05b_SoumissionRapportF';
    case VALIDATION_F = '05c_ValidationF';
    case NOTE_CONCEPTUEL = '03a_NoteConceptuel';
    case VALIDATION_NOTE_AMELIORER = '03cx_ValidationNoteAameliorer';
    case R_VALIDATION_NOTE_AMELIORER = '03c_R_ValidationNoteAameliorer';
    case EVALUATION_NOTE = '03b_EvaluationNote';
    case VALIDATION_PROFIL = '03c_ValidationProfil';
    case R_VALIDATION_PROFIL_NOTE_AMELIORER = '03c_R_ValidationProfilNoteAameliorer';
    case TDR_PREFAISABILITE = '04a_TDR_Prefaisabilite';
    case EVALUATION_TDR_PF = '04b_Evaluation_TDR_PF';
    case SOUMISSION_RAPPORT_PF = '04b_SoumissionRapportPF';
    case VALIDATION_PF = '04c_ValidationPF';

    case ADMISSIBLE = '00_Admissible';
    case REJETE = '00_Rejete';
    case SELECTION = '01_Selection';
    case HIERARCHISE = '02_Hierarchise';
    case EN_ATTENTE_DE_VALIDATION_FINALE = '03_En_attente_de_validation_finale';
    case R_REJETE = '03_Rejete';
    case PRIORISE = '04_Priorise';
    case CLOTURE = 'Cloture';

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
