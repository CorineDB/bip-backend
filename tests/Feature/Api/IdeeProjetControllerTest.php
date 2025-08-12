<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\IdeeProjet;
use App\Models\User;
use App\Models\Secteur;
use App\Models\CategorieProjet;
use App\Services\Contracts\IdeeProjetServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery;

class IdeeProjetControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $ideeProjetServiceMock;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->ideeProjetServiceMock = Mockery::mock(IdeeProjetServiceInterface::class);
        $this->app->instance(IdeeProjetServiceInterface::class, $this->ideeProjetServiceMock);
        
        $this->user = User::factory()->create();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_retourne_toutes_les_idees_projets()
    {
        // Arrange
        $expectedResponse = response()->json([
            'data' => [],
            'message' => 'Success'
        ]);

        $this->ideeProjetServiceMock
            ->shouldReceive('all')
            ->once()
            ->andReturn($expectedResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->getJson('/api/idees-projet');

        // Assert
        $response->assertStatus(200);
    }

    public function test_index_avec_filtre_statut()
    {
        // Arrange
        $statut = 'BROUILLON';
        $expectedResponse = response()->json([
            'data' => [],
            'message' => 'Success'
        ]);

        $this->ideeProjetServiceMock
            ->shouldReceive('filterBy')
            ->with([$statut])
            ->once()
            ->andReturn($expectedResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->getJson('/api/idees-projet?statut=' . $statut);

        // Assert
        $response->assertStatus(200);
    }

    public function test_index_avec_filtres_statuts_multiples()
    {
        // Arrange
        $statuts = ['BROUILLON', 'SOUMISE'];
        $expectedResponse = response()->json([
            'data' => [],
            'message' => 'Success'
        ]);

        $this->ideeProjetServiceMock
            ->shouldReceive('filterBy')
            ->with($statuts)
            ->once()
            ->andReturn($expectedResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->getJson('/api/idees-projet?' . http_build_query(['statut' => $statuts]));

        // Assert
        $response->assertStatus(200);
    }

    public function test_show_retourne_idee_projet_existante()
    {
        // Arrange
        $id = 1;
        $expectedResponse = response()->json([
            'data' => ['id' => $id, 'titre_projet' => 'Test Projet'],
            'message' => 'Success'
        ]);

        $this->ideeProjetServiceMock
            ->shouldReceive('find')
            ->with($id)
            ->once()
            ->andReturn($expectedResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->getJson('/api/idees-projet/' . $id);

        // Assert
        $response->assertStatus(200);
    }

    public function test_show_avec_id_inexistant()
    {
        // Arrange
        $id = 999;
        $expectedResponse = response()->json([
            'success' => false,
            'message' => 'Idée de projet inconnue'
        ], 404);

        $this->ideeProjetServiceMock
            ->shouldReceive('find')
            ->with($id)
            ->once()
            ->andReturn($expectedResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->getJson('/api/idees-projet/' . $id);

        // Assert
        $response->assertStatus(404);
    }

    public function test_store_cree_nouvelle_idee_projet()
    {
        // Arrange
        $requestData = [
            'champs' => [
                'titre_projet' => 'Nouveau Projet',
                'description' => 'Description du nouveau projet',
                'secteurId' => 1,
                'categorieId' => 1
            ],
            'est_soumise' => false
        ];

        $expectedResponse = response()->json([
            'data' => array_merge($requestData, ['id' => 1]),
            'message' => 'Idée de projet sauvegardée avec succès.'
        ], 201);

        $this->ideeProjetServiceMock
            ->shouldReceive('create')
            ->with($requestData)
            ->once()
            ->andReturn($expectedResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->postJson('/api/idees-projet', $requestData);

        // Assert
        $response->assertStatus(201);
    }

    public function test_store_avec_donnees_invalides()
    {
        // Arrange
        $requestData = [
            'champs' => [
                'titre_projet' => '', // Titre vide
                'description' => 'Description'
            ]
        ];

        $this->actingAs($this->user);

        // Act
        $response = $this->postJson('/api/idees-projet', $requestData);

        // Assert
        $response->assertStatus(422);
    }

    public function test_update_modifie_idee_projet_existante()
    {
        // Arrange
        $id = 1;
        $requestData = [
            'champs' => [
                'titre_projet' => 'Projet Modifié',
                'description' => 'Description modifiée'
            ],
            'est_soumise' => true
        ];

        $expectedResponse = response()->json([
            'data' => array_merge($requestData, ['id' => $id]),
            'message' => 'Idée de projet sauvegardée avec succès.'
        ]);

        $this->ideeProjetServiceMock
            ->shouldReceive('update')
            ->with($id, $requestData)
            ->once()
            ->andReturn($expectedResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->putJson('/api/idees-projet/' . $id, $requestData);

        // Assert
        $response->assertStatus(200);
    }

    public function test_update_avec_id_inexistant()
    {
        // Arrange
        $id = 999;
        $requestData = [
            'champs' => [
                'titre_projet' => 'Projet Modifié'
            ]
        ];

        $expectedResponse = response()->json([
            'success' => false,
            'message' => 'Idée de projet inconnue'
        ], 404);

        $this->ideeProjetServiceMock
            ->shouldReceive('update')
            ->with($id, $requestData)
            ->once()
            ->andReturn($expectedResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->putJson('/api/idees-projet/' . $id, $requestData);

        // Assert
        $response->assertStatus(404);
    }

    public function test_destroy_supprime_idee_projet()
    {
        // Arrange
        $id = 1;
        
        $expectedResponse = response()->json([
            'success' => true,
            'message' => 'Idée de projet supprimée avec succès'
        ]);

        $this->ideeProjetServiceMock
            ->shouldReceive('delete')
            ->with($id)
            ->once()
            ->andReturn($expectedResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->deleteJson('/api/idees-projet/' . $id);

        // Assert
        $response->assertStatus(200);
    }

    public function test_destroy_avec_id_inexistant()
    {
        // Arrange
        $id = 999;
        
        $expectedResponse = response()->json([
            'success' => false,
            'message' => 'Idée de projet inconnue'
        ], 404);

        $this->ideeProjetServiceMock
            ->shouldReceive('delete')
            ->with($id)
            ->once()
            ->andReturn($expectedResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->deleteJson('/api/idees-projet/' . $id);

        // Assert
        $response->assertStatus(404);
    }

    public function test_filter_by_statut_filtre_par_statut()
    {
        // Arrange
        $filterData = ['statut' => 'ANALYSE'];
        
        $expectedResponse = response()->json([
            'data' => [],
            'message' => 'Success'
        ]);

        $this->ideeProjetServiceMock
            ->shouldReceive('filterBy')
            ->with($filterData)
            ->once()
            ->andReturn($expectedResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->postJson('/api/idees-projet/filter', $filterData);

        // Assert
        $response->assertStatus(200);
    }

    public function test_demandeurs_retourne_liste_demandeurs()
    {
        // Arrange
        $expectedResponse = response()->json([
            'success' => true,
            'data' => [
                ['id' => 1, 'nom_complet' => 'Organisation Test'],
                ['id' => 2, 'nom_complet' => 'John Doe']
            ],
            'message' => ''
        ]);

        $this->ideeProjetServiceMock
            ->shouldReceive('demandeurs')
            ->once()
            ->andReturn($expectedResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->getJson('/api/idees-projet/demandeurs');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'nom_complet']
                ]
            ]);
    }

    public function test_acces_non_autorise_sans_authentification()
    {
        // Act
        $response = $this->getJson('/api/idees-projet');

        // Assert
        $response->assertStatus(401);
    }

    public function test_store_avec_relations()
    {
        // Arrange
        $requestData = [
            'champs' => [
                'titre_projet' => 'Projet avec Relations',
                'description' => 'Description',
                'cibles' => [1, 2],
                'odds' => [1],
                'sources_financement' => [1],
                'departements' => [1],
                'communes' => [1],
                'arrondissements' => [1],
                'villages' => [1]
            ],
            'est_soumise' => false
        ];

        $expectedResponse = response()->json([
            'data' => array_merge($requestData, ['id' => 1]),
            'message' => 'Idée de projet sauvegardée avec succès.'
        ], 201);

        $this->ideeProjetServiceMock
            ->shouldReceive('create')
            ->with($requestData)
            ->once()
            ->andReturn($expectedResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->postJson('/api/idees-projet', $requestData);

        // Assert
        $response->assertStatus(201);
    }

    public function test_update_avec_soumission()
    {
        // Arrange
        $id = 1;
        $requestData = [
            'champs' => [
                'titre_projet' => 'Projet À Soumettre'
            ],
            'est_soumise' => true
        ];

        $expectedResponse = response()->json([
            'data' => array_merge($requestData, ['id' => $id]),
            'message' => 'Idée de projet sauvegardée avec succès.'
        ]);

        $this->ideeProjetServiceMock
            ->shouldReceive('update')
            ->with($id, $requestData)
            ->once()
            ->andReturn($expectedResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->putJson('/api/idees-projet/' . $id, $requestData);

        // Assert
        $response->assertStatus(200);
    }

    public function test_index_gere_les_erreurs_du_service()
    {
        // Arrange
        $errorResponse = response()->json([
            'statut' => 'error',
            'message' => 'Erreur interne du service'
        ], 500);

        $this->ideeProjetServiceMock
            ->shouldReceive('all')
            ->once()
            ->andReturn($errorResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->getJson('/api/idees-projet');

        // Assert
        $response->assertStatus(500);
    }

    public function test_store_gere_les_erreurs_de_creation()
    {
        // Arrange
        $requestData = [
            'champs' => [
                'titre_projet' => 'Projet avec Erreur'
            ]
        ];

        $errorResponse = response()->json([
            'statut' => 'error',
            'message' => 'Erreur lors de la création'
        ], 500);

        $this->ideeProjetServiceMock
            ->shouldReceive('create')
            ->with($requestData)
            ->once()
            ->andReturn($errorResponse);

        $this->actingAs($this->user);

        // Act
        $response = $this->postJson('/api/idees-projet', $requestData);

        // Assert
        $response->assertStatus(500);
    }
}