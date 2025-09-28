<?php

namespace Database\Seeders;

use App\Http\Resources\CanevasAppreciationTdrResource;
use Illuminate\Database\Seeder;
use App\Models\NoteConceptuelle;
use App\Repositories\DocumentRepository;
use App\Http\Resources\CanevasNoteConceptuelleResource;
use Illuminate\Support\Facades\Log;

class UpdateNotesConceptuellesCanevasAppreciationSeeder extends Seeder
{
    protected $documentRepository;

    public function __construct(DocumentRepository $documentRepository)
    {
        $this->documentRepository = $documentRepository;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $this->command->info('🚀 Début de la mise à jour des canevas d\'appréciation des notes conceptuelles...');

            // Récupérer le canevas d'appréciation des notes conceptuelles
            $canevasAppreciation = $this->documentRepository->getCanevasAppreciationNoteConceptuelle();

            if (!$canevasAppreciation) {
                $this->command->error('❌ Aucun canevas d\'appréciation des notes conceptuelles trouvé.');
                return;
            }
            $this->command->info("✅ Canevas d'appréciation trouvé: {$canevasAppreciation->titre}");


            $canevasRedactionNC = $this->documentRepository->getCanevasRedactionNoteConceptuelle();

            if (!$canevasRedactionNC) {
                $this->command->error('❌ Aucun canevas de redaction des notes conceptuelles trouvé.');
                return;
            }

            $canevasRedactionNCnResource = $canevasRedactionNC ? (new CanevasAppreciationTdrResource($canevasRedactionNC)) : null;

            $canevasAppreciationResource = $canevasAppreciation ? new CanevasNoteConceptuelleResource($canevasAppreciation) : null;

            $this->command->info("✅ Canevas de rédaction trouvé: {$canevasAppreciation->titre}");


            //$canevasStructure = $canevasResource->toArray(request());

            /**
             * Query multiple records update
             *
             */
            NoteConceptuelle::query()->update([
                'canevas_redaction_note_conceptuelle' => $canevasRedactionNCnResource->toArray(request()),
                'canevas_appreciation_note_conceptuelle' => $canevasAppreciationResource->toArray(request())
            ]);

        } catch (\Exception $e) {
            $this->command->error('❌ Erreur générale lors de la mise à jour: ' . $e->getMessage());
            Log::error('Erreur UpdateNotesConceptuellesCanevasAppreciationSeeder::run', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
