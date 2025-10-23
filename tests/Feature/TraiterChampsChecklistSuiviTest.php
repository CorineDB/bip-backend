<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TraiterChampsChecklistSuiviTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function il_met_a_jour_les_champs_et_la_checklist_suivi()
    {
        // 1️⃣ Créer un rapport factice
        $rapport = Rapport::factory()->create(['type' => 'prefaisabilite']);

        // 2️⃣ Créer quelques champs factices reliés au rapport
        $champs = Champ::factory()->count(2)->create();

        // 3️⃣ Simuler les données de checklist reçues
        $checklistData = [
            [
                'checkpoint_id' => $champs[0]->id,
                'remarque' => 'Remarque A',
                'explication' => 'Explication A',
            ],
            [
                'checkpoint_id' => $champs[1]->id,
                'remarque' => 'Remarque B',
                'explication' => 'Explication B',
            ],
        ];

        // 4️⃣ Mock du DocumentRepository
        $mockRepository = $this->mock(DocumentRepository::class, function ($mock) use ($champs) {
            $mock->shouldReceive('getCanevasChecklistSuiviRapportPrefaisabilite')
                ->andReturn((object)[
                    'all_champs' => $champs->map(function ($champ) {
                        return [
                            'id' => $champ->id,
                            'hashed_id' => $champ->id,
                            'label' => $champ->label,
                            'attribut' => $champ->attribut,
                            'ordre_affichage' => 1,
                            'type_champ' => 'text',
                        ];
                    })->toArray()
                ]);
        });

        // 5️⃣ Instancier la classe contenant la méthode à tester
        $service = new class($mockRepository) {
            public $documentRepository;
            public function __construct($repo) { $this->documentRepository = $repo; }

            use \App\Services\TdrPrefaisabiliteService; // ou la classe contenant ta méthode
        };

        // 6️⃣ Exécuter la méthode
        $this->invokeMethod($service, 'traiterChampsChecklistSuivi', [$rapport, $checklistData, false]);

        // 7️⃣ Vérifier la relation champs()
        $this->assertDatabaseHas('champ_rapport', [
            'rapport_id' => $rapport->id,
            'champ_id' => $champs[0]->id,
            'valeur' => 'Remarque A',
            'commentaire' => 'Explication A',
        ]);

        // 8️⃣ Vérifier la sauvegarde du canevas de suivi
        $rapport->refresh();

        $this->assertNotEmpty($rapport->checklist_suivi);
        $this->assertNotEmpty($rapport->checklist_suivi_rapport_prefaisabilite);

        $this->assertEquals('Remarque A', $rapport->checklist_suivi[0]['valeur']);
        $this->assertEquals('text', $rapport->checklist_suivi[0]['type_champ']);
    }

    /**
     * Helper pour invoquer une méthode privée
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
