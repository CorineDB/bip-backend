<?php

namespace App\Enums;

enum TypesCanevas: string
{
    case canevas_suivi_evaluation = 'canevas_suivi_evaluation';
    case canevas_analyse = 'canevas_analyse';
    case canevas_etude = 'canevas_etude';
    case canevas_guide = 'canevas_guide';
}