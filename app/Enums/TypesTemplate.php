<?php

namespace App\Enums;

enum TypesTemplate: string
{
    case evaluation = 'evaluation';
    case checklist = 'checklist';
    case document = 'document';
    case formulaire = 'formulaire';
    case tableau = 'tableau';
}