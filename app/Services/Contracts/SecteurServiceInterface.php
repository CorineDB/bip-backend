<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface SecteurServiceInterface extends AbstractServiceInterface
{
    // Define contract methods here

    public function grands_secteurs(): JsonResponse;

    public function secteurs_grand_secteur($idGrandSecteur): JsonResponse;

    public function secteurs(): JsonResponse;

    public function sous_secteurs(): JsonResponse;

    public function sous_secteurs_secteur($idSecteur): JsonResponse;
}