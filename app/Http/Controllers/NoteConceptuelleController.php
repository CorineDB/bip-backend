<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\notes_conceptuelle\StoreNoteConceptuelleRequest;
use App\Http\Requests\notes_conceptuelle\UpdateNoteConceptuelleRequest;
use App\Http\Requests\notes_conceptuelle\ConfigurerOptionsEvaluationRequest;
use App\Http\Requests\notes_conceptuelle\AppreciationNoteConceptuelleRequest;
use App\Http\Requests\notes_conceptuelle\ConfirmerResultatRequest;
use App\Http\Requests\notes_conceptuelle\ValiderEtudeProfilRequest;
use App\Http\Requests\notes_conceptuelle\SoumettreRapportFaisabilitePreliminaireRequest;
use App\Services\Contracts\NoteConceptuelleServiceInterface;
use App\Models\NoteConceptuelle;
use Illuminate\Http\JsonResponse;

class NoteConceptuelleController extends Controller
{
    protected NoteConceptuelleServiceInterface $service;

    public function __construct(NoteConceptuelleServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        return $this->service->all();
    }

    public function show($id): JsonResponse
    {
        return $this->service->find($id);
    }

    public function store(StoreNoteConceptuelleRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateNoteConceptuelleRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * Créer une note conceptuelle pour un projet
     */
    public function createForProject($projetId, StoreNoteConceptuelleRequest $request): JsonResponse
    {
        // Ajouter projetId aux données de la requête
        $data = array_merge($request->all(), ['projetId' => $projetId]);

        return $this->service->create($data);
    }

    /**
     * Mettre à jour une note conceptuelle d'un projet
     */
    public function updateForProject($projetId, $noteId, UpdateNoteConceptuelleRequest $request): JsonResponse
    {
        // Vérifier que la note conceptuelle appartient au projet
        $noteConceptuelle = NoteConceptuelle::where('id', $noteId)
            ->where('projetId', $projetId)
            ->first();

        if (!$noteConceptuelle) {
            return response()->json([
                'success' => false,
                'message' => 'Note conceptuelle non trouvée pour ce projet.'
            ], 404);
        }

        return $this->service->update($noteId, $request->all());
    }

    /**
     * Récupérer une note conceptuelle d'un projet
     */
    public function getForProject($projetId): JsonResponse
    {
        return $this->service->getForProject($projetId);
    }

    /**
     * Supprimer une note conceptuelle d'un projet
     */
    public function deleteForProject($projetId, $noteId): JsonResponse
    {
        // Vérifier que la note conceptuelle appartient au projet
        $noteConceptuelle = NoteConceptuelle::where('id', $noteId)
            ->where('projetId', $projetId)
            ->first();

        if (!$noteConceptuelle) {
            return response()->json([
                'success' => false,
                'message' => 'Note conceptuelle non trouvée pour ce projet.'
            ], 404);
        }

        return $this->service->delete($noteId);
    }

    /**
     * Valider une note conceptuelle
     */
    public function validateNote(StoreNoteConceptuelleRequest $request, $projetId, $noteId): JsonResponse
    {
        return $this->service->validateNote($projetId, $noteId, $request->all());
    }

    /**
     * Récupérer les détails de validation d'une note conceptuelle
     */
    public function getValidationDetails($projetId, $noteId): JsonResponse
    {
        return $this->service->getValidationDetails($projetId, $noteId);
    }

    /**
     * Récupérer une note conceptuelle avec sa configuration d'évaluation
     */
    public function getWithEvaluationConfig($noteId): JsonResponse
    {
        try {
            $noteConceptuelle = NoteConceptuelle::findOrFail($noteId);
            $grille = $this->service->creerGrilleEvaluation($noteConceptuelle);

            return response()->json([
                'success' => true,
                'data' => [
                    'note_conceptuelle' => $noteConceptuelle,
                    'grille_evaluation' => $grille
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une évaluation pour une note conceptuelle
     */
    public function creerEvaluation(AppreciationNoteConceptuelleRequest $request, $noteId): JsonResponse
    {
        return $this->service->creerEvaluation($noteId, $request->all());
    }

    /**
     * Mettre à jour une évaluation
     */
    public function mettreAJourEvaluation($evaluationId): JsonResponse
    {
        $data = request()->all();
        return $this->service->mettreAJourEvaluation($evaluationId, $data);
    }

    /**
     * Récupérer l'évaluation d'une note conceptuelle
     */
    public function getEvaluation($noteId): JsonResponse
    {
        return $this->service->getEvaluation($noteId);
    }

    /**
     * Configurer les options de notation pour l'évaluation des notes conceptuelles
     */
    public function configurerOptionsNotation(ConfigurerOptionsEvaluationRequest $request): JsonResponse
    {
        return $this->service->configurerOptionsNotation($request->all());
    }

    /**
     * Récupérer la configuration actuelle des options de notation
     */
    public function getOptionsNotationConfig(): JsonResponse
    {
        return $this->service->getOptionsNotationConfig();
    }

    /**
     * Confirmer le résultat de l'évaluation avec commentaires
     */
    public function confirmerResultat(ConfirmerResultatRequest $request, $projetId, $noteId): JsonResponse
    {
        return $this->service->confirmerResultatParNote($noteId, $request->all());
    }

    /**
     * Récupérer les détails de validation de l'étude de profil pour un projet
     */
    public function getDetailsEtudeProfil($projetId): JsonResponse
    {
        return $this->service->getDetailsEtudeProfil($projetId);
    }

    /**
     * Valider le projet à l'étape étude de profil
     */
    public function validerEtudeProfil(ValiderEtudeProfilRequest $request, $projetId): JsonResponse
    {
        return $this->service->validerEtudeDeProfil($projetId, $request->all());
    }

    /**
     * Soumettre ou resoumettre un rapport de faisabilité préliminaire (SFD-009)
     */
    public function soumettreRapportFaisabilitePreliminaire(SoumettreRapportFaisabilitePreliminaireRequest $request, $projetId): JsonResponse
    {
        return $this->service->soumettreRapportFaisabilitePreliminaire($projetId, $request->all());
    }
}
