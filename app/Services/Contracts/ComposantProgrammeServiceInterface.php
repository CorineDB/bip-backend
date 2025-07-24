<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface ComposantProgrammeServiceInterface extends AbstractServiceInterface
{
    // Define contract methods here

    /**
     * Les axes du pag
     */
    public function axes_pag(): JsonResponse;

    /**
     * Les piliers du pag
     */
    public function piliers_pag(): JsonResponse;

    /**
     * Les actions du pag
     */
    public function actions_pag(): JsonResponse;

    /**
     * Liste des orientations strategique du PND
     */
    public function orientations_strategiques_pnd(): JsonResponse;

    /**
     * Liste des objectifs strategique du PND
     */
    public function objectifs_strategiques_pnd(): JsonResponse;

    /**
     * Liste des resultats strategique du PND
     */
    public function resultats_strategiques_pnd(): JsonResponse;
}
