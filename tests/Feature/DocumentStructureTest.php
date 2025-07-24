<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Document;
use App\Models\CategorieDocument;
use App\Models\ChampSection;
use App\Models\Champ;
use App\Services\DocumentStructureService;

class DocumentStructureTest extends TestCase
{
    use RefreshDatabase;

    private DocumentStructureService $structureService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->structureService = app(DocumentStructureService::class);
    }

    /** @test */
    public function it_generates_and_saves_document_structure_on_creation()
    {
        // Créer une catégorie de document
        $categorie = CategorieDocument::create([
            'nom' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test description',
            'format' => 'formulaire'
        ]);

        // Données pour créer un document avec sections et champs
        $documentData = [
            'nom' => 'Document Test Structure',
            'slug' => 'document-test-structure',
            'description' => 'Test de génération de structure',
            'categorieId' => $categorie->id,
            'type' => 'formulaire',
            'metadata' => ['test' => 'value'],
            'sections' => [
                [
                    'intitule' => 'Section 1',
                    'description' => 'Première section',
                    'ordre_affichage' => 1,
                    'type' => 'standard',
                    'champs' => [
                        [
                            'label' => 'Nom du projet',
                            'attribut' => 'nom_projet',
                            'type_champ' => 'text',
                            'is_required' => true,
                            'ordre_affichage' => 1,
                            'champ_standard' => true
                        ]
                    ]
                ]
            ],
            'champs' => [
                [
                    'label' => 'Email contact',
                    'attribut' => 'email_contact',
                    'type_champ' => 'email',
                    'is_required' => false,
                    'ordre_affichage' => 2,
                    'champ_standard' => false
                ]
            ]
        ];

        // Envoyer une requête POST pour créer le document
        $response = $this->postJson('/api/documents', $documentData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'nom',
                'description',
                'type',
                'structure', // Vérifier que la structure est présente
                'sections',
                'champs'
            ]
        ]);

        // Vérifier que le document a été créé avec une structure
        $document = Document::where('nom', 'Document Test Structure')->first();
        $this->assertNotNull($document);
        $this->assertNotNull($document->structure);
        $this->assertIsArray($document->structure);

        // Vérifier la structure générée
        $structure = $document->structure;
        $this->assertArrayHasKey('document_info', $structure);
        $this->assertArrayHasKey('categorie', $structure);
        $this->assertArrayHasKey('sections_structure', $structure);
        $this->assertArrayHasKey('champs_structure', $structure);
        $this->assertArrayHasKey('generated_at', $structure);
        $this->assertArrayHasKey('version', $structure);

        // Vérifier les détails de la structure
        $this->assertEquals($document->id, $structure['document_info']['id']);
        $this->assertEquals('Document Test Structure', $structure['document_info']['nom']);
        $this->assertEquals('formulaire', $structure['document_info']['type']);
        $this->assertEquals($categorie->id, $structure['categorie']['id']);
        $this->assertCount(1, $structure['sections_structure']);
        $this->assertCount(1, $structure['champs_structure']);
    }

    /** @test */
    public function it_updates_document_structure_on_modification()
    {
        // Créer un document initial
        $categorie = CategorieDocument::create([
            'nom' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test description',
            'format' => 'formulaire'
        ]);

        $document = Document::create([
            'nom' => 'Document Test Update',
            'slug' => 'document-test-update',
            'description' => 'Test initial',
            'categorieId' => $categorie->id,
            'type' => 'formulaire'
        ]);

        // Ajouter une section et un champ
        $section = $document->sections()->create([
            'intitule' => 'Section Initiale',
            'description' => 'Section de test',
            'ordre_affichage' => 1,
            'type' => 'standard'
        ]);

        $section->champs()->create([
            'label' => 'Champ Initial',
            'attribut' => 'champ_initial',
            'type_champ' => 'text',
            'is_required' => true,
            'ordre_affichage' => 1,
            'documentId' => $document->id
        ]);

        // Générer la structure initiale
        $this->structureService->generateAndSaveStructure($document->fresh(['sections.champs', 'champs', 'categorie']));

        $initialStructure = $document->fresh()->structure;
        $this->assertNotNull($initialStructure);
        $this->assertCount(1, $initialStructure['sections_structure']);

        // Modifier le document en ajoutant une nouvelle section
        $updateData = [
            'nom' => 'Document Test Update Modifié',
            'description' => 'Description mise à jour',
            'sections' => [
                [
                    'id' => $section->id,
                    'intitule' => 'Section Modifiée',
                    'description' => 'Section mise à jour',
                    'ordre_affichage' => 1,
                    'type' => 'standard',
                    'champs' => []
                ],
                [
                    'intitule' => 'Nouvelle Section',
                    'description' => 'Section ajoutée',
                    'ordre_affichage' => 2,
                    'type' => 'standard',
                    'champs' => [
                        [
                            'label' => 'Nouveau Champ',
                            'attribut' => 'nouveau_champ',
                            'type_champ' => 'textarea',
                            'is_required' => false,
                            'ordre_affichage' => 1
                        ]
                    ]
                ]
            ]
        ];

        // Mettre à jour via l'API
        $response = $this->putJson("/api/documents/{$document->id}", $updateData);
        $response->assertStatus(200);

        // Vérifier que la structure a été mise à jour
        $updatedDocument = $document->fresh(['sections.champs', 'champs']);
        $updatedStructure = $updatedDocument->structure;

        $this->assertNotNull($updatedStructure);
        $this->assertEquals('Document Test Update Modifié', $updatedStructure['document_info']['nom']);
        $this->assertCount(2, $updatedStructure['sections_structure']); // Deux sections maintenant
    }

    /** @test */
    public function it_detects_structure_changes()
    {
        $categorie = CategorieDocument::create([
            'nom' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test description',
            'format' => 'formulaire'
        ]);

        $document = Document::create([
            'nom' => 'Document Test Changes',
            'slug' => 'document-test-changes',
            'categorieId' => $categorie->id,
            'type' => 'formulaire'
        ]);

        // Générer la structure initiale
        $this->structureService->generateAndSaveStructure($document->load(['sections.champs', 'champs', 'categorie']));

        // La structure ne devrait pas avoir changé
        $this->assertFalse($this->structureService->hasStructureChanged($document));

        // Ajouter un champ pour simuler un changement
        $document->champs()->create([
            'label' => 'Nouveau Champ',
            'attribut' => 'nouveau_champ',
            'type_champ' => 'text',
            'is_required' => false,
            'ordre_affichage' => 1,
            'documentId' => $document->id
        ]);

        // Maintenant la structure devrait avoir changé
        $this->assertTrue($this->structureService->hasStructureChanged($document->fresh(['sections.champs', 'champs'])));
    }

    /** @test */
    public function it_stores_correct_structure_format()
    {
        $categorie = CategorieDocument::create([
            'nom' => 'Test Category',
            'slug' => 'test-category',
            'format' => 'grille'
        ]);

        $document = Document::create([
            'nom' => 'Document Format Test',
            'slug' => 'document-format-test',
            'categorieId' => $categorie->id,
            'type' => 'grille',
            'metadata' => ['custom' => 'metadata']
        ]);

        $structure = $this->structureService->generateAndSaveStructure($document->load(['categorie']));

        // Vérifier le format exact de la structure stockée
        $expectedKeys = ['document_info', 'categorie', 'sections_structure', 'champs_structure', 'metadata', 'generated_at', 'version'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $structure);
        }

        $this->assertEquals('1.0', $structure['version']);
        $this->assertEquals('grille', $structure['document_info']['type']);
        $this->assertEquals('grille', $structure['categorie']['format']);
        $this->assertEquals(['custom' => 'metadata'], $structure['metadata']);
        $this->assertIsString($structure['generated_at']);
    }
}