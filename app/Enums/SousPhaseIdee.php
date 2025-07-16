<?php

namespace App\Enums;

enum SousPhaseIdee: string
{
    case redaction = 'redaction';
    case analyse_idee = 'analyse_idee';
    case etude_de_profil = 'etude_de_profil';
    case etude_de_prefaisabilite = 'etude_de_prefaisabilite';
    case faisabilite = 'etude_de_faisabilite';
}