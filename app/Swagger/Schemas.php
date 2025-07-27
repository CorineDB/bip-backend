<?php

/**
 * @OA\Schema(
 *     schema="Commune",
 *     title="Commune",
 *     type="object",
 *     required={"id", "nom"},
 *     @OA\Property(property="id", type="integer", example=5),
 *     @OA\Property(property="nom", type="string", example="Cotonou")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Arrondissement",
 *     title="Arrondissement",
 *     description="Représente un arrondissement d'une commune",
 *     type="object",
 *     required={"id", "code", "nom", "slug", "communeId"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="ARR-001"),
 *     @OA\Property(property="nom", type="string", example="Abomey-Calavi"),
 *     @OA\Property(property="slug", type="string", example="abomey-calavi"),
 *     @OA\Property(
 *         property="communeId",
 *         type="integer",
 *         description="ID de la commune liée (référence à Commune)",
 *         example=5
 *     ),
 *     @OA\Property(
 *         property="commune",
 *         ref="#/components/schemas/Commune"
 *     )
 * )
 */