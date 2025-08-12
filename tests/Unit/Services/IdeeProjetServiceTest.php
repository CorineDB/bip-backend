<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Mockery;
use App\Services\IdeeProjetService;
use App\Repositories\Contracts\IdeeProjetRepositoryInterface;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Models\IdeeProjet;
use App\Models\Document;
use App\Models\User;
use App\Models\Dpaf;
use App\Models\Organisation;
use App\Models\Dgpd;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class IdeeProjetServiceTest extends TestCase
{
    protected $ideeProjetService;
    protected $ideeProjetRepositoryMock;
    protected $documentRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->ideeProjetRepositoryMock = Mockery::mock(IdeeProjetRepositoryInterface::class);
        $this->documentRepositoryMock = Mockery::mock(DocumentRepositoryInterface::class);
        $this->ideeProjetService = new IdeeProjetService(
            $this->ideeProjetRepositoryMock,
            $this->documentRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_all_retourne_idees_projets_filtrees_pour_dpaf()
    {
        // Arrange
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('profilable_type')->andReturn(Dpaf::class);
        
        $profilable = Mockery::mock(Dpaf::class);
        $ministere = Mockery::mock();
        $ministere->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $profilable->shouldReceive('getAttribute')->with('ministere')->andReturn($ministere);
        $user->shouldReceive('getAttribute')->with('profilable')->andReturn($profilable);

        $modelMock = Mockery::mock();
        $modelMock->shouldReceive('when')->andReturnSelf();
        $modelMock->shouldReceive('latest')->andReturnSelf();
        $modelMock->shouldReceive('get')->andReturn(collect([]));

        $this->ideeProjetRepositoryMock
            ->shouldReceive('getModel')
            ->andReturn($modelMock);

        Auth::shouldReceive('user')->andReturn($user);

        // Act
        $result = $this->ideeProjetService->all();

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function test_find_retourne_idee_projet_existante()
    {
        // Arrange
        $id = 1;
        $ideeProjet = Mockery::mock(IdeeProjet::class);

        $this->ideeProjetRepositoryMock
            ->shouldReceive('findOrFail')
            ->with($id)
            ->andReturn($ideeProjet);

        // Act
        $result = $this->ideeProjetService->find($id);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function test_find_retourne_erreur_pour_idee_inexistante()
    {
        // Arrange
        $id = 999;

        $this->ideeProjetRepositoryMock
            ->shouldReceive('findOrFail')
            ->with($id)
            ->andThrow(new ModelNotFoundException());

        // Act
        $result = $this->ideeProjetService->find($id);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(404, $result->getStatusCode());
        
        $data = json_decode($result->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('inconnue', $data['message']);
    }

    public function test_create_avec_donnees_valides()
    {
        // Arrange
        $data = [
            'champs' => [
                'titre_projet' => 'Test Projet',
                'description' => 'Description du projet',
                'cibles' => [1, 2],
                'odds' => [1],
                'sources_financement' => [1]
            ],
            'est_soumise' => false
        ];

        $user = Mockery::mock(User::class);
        $user->shouldReceive('id')->andReturn(1);
        
        $profilable = Mockery::mock();
        $ministere = Mockery::mock();
        $ministere->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $profilable->shouldReceive('getAttribute')->with('ministere')->andReturn($ministere);
        $user->shouldReceive('getAttribute')->with('profilable')->andReturn($profilable);

        $ideeProjet = Mockery::mock(IdeeProjet::class);
        $ideeProjet->shouldReceive('fill');
        $ideeProjet->shouldReceive('setAttribute');
        $ideeProjet->shouldReceive('save');
        $ideeProjet->shouldReceive('refresh');
        $ideeProjet->shouldReceive('cibles')->andReturnSelf();
        $ideeProjet->shouldReceive('odds')->andReturnSelf();
        $ideeProjet->shouldReceive('financements')->andReturnSelf();
        $ideeProjet->shouldReceive('composants')->andReturnSelf();
        $ideeProjet->shouldReceive('champs')->andReturnSelf();
        $ideeProjet->shouldReceive('sync');

        $this->ideeProjetRepositoryMock
            ->shouldReceive('getModel')
            ->andReturn($ideeProjet);

        $ficheIdee = Mockery::mock(Document::class);
        $ficheIdee->shouldReceive('getAttribute')->with('all_champs')->andReturn(collect([]));

        $this->documentRepositoryMock
            ->shouldReceive('getFicheIdee')
            ->andReturn($ficheIdee);

        Auth::shouldReceive('user')->andReturn($user);
        Auth::shouldReceive('id')->andReturn(1);

        DB::shouldReceive('beginTransaction');
        DB::shouldReceive('commit');

        // Act
        $result = $this->ideeProjetService->create($data);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(201, $result->getStatusCode());
    }

    public function test_create_avec_donnees_invalides_rollback()
    {
        // Arrange
        $data = [
            'champs' => [
                'titre_projet' => 'Test Projet'
            ]
        ];

        $this->ideeProjetRepositoryMock
            ->shouldReceive('getModel')
            ->andThrow(new Exception('Erreur de base de données'));

        DB::shouldReceive('beginTransaction');
        DB::shouldReceive('rollBack');

        // Act
        $result = $this->ideeProjetService->create($data);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(500, $result->getStatusCode());
        
        $data = json_decode($result->getContent(), true);
        $this->assertEquals('error', $data['statut']);
    }

    public function test_update_avec_donnees_valides()
    {
        // Arrange
        $id = 1;
        $data = [
            'champs' => [
                'titre_projet' => 'Projet Modifié',
                'description' => 'Description modifiée'
            ],
            'est_soumise' => true
        ];

        $ideeProjet = Mockery::mock(IdeeProjet::class);
        $ideeProjet->shouldReceive('getAttribute')->with('est_soumise')->andReturn(false);
        $ideeProjet->shouldReceive('setAttribute');
        $ideeProjet->shouldReceive('fill');
        $ideeProjet->shouldReceive('save');
        $ideeProjet->shouldReceive('refresh');
        $ideeProjet->shouldReceive('cibles')->andReturnSelf();
        $ideeProjet->shouldReceive('odds')->andReturnSelf();
        $ideeProjet->shouldReceive('financements')->andReturnSelf();
        $ideeProjet->shouldReceive('composants')->andReturnSelf();
        $ideeProjet->shouldReceive('champs')->andReturnSelf();
        $ideeProjet->shouldReceive('sync');

        $this->ideeProjetRepositoryMock
            ->shouldReceive('findOrFail')
            ->with($id)
            ->andReturn($ideeProjet);

        $ficheIdee = Mockery::mock(Document::class);
        $ficheIdee->shouldReceive('getAttribute')->with('all_champs')->andReturn(collect([]));

        $this->documentRepositoryMock
            ->shouldReceive('getFicheIdee')
            ->andReturn($ficheIdee);

        DB::shouldReceive('beginTransaction');
        DB::shouldReceive('commit');

        // Act
        $result = $this->ideeProjetService->update($id, $data);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function test_filter_by_retourne_idees_filtrees()
    {
        // Arrange
        $filterParam = ['BROUILLON'];
        
        $modelMock = Mockery::mock();
        $modelMock->shouldReceive('where')->with('statut', 'BROUILLON')->andReturnSelf();
        $modelMock->shouldReceive('get')->andReturn(collect([]));

        $this->ideeProjetRepositoryMock
            ->shouldReceive('getModel')
            ->andReturn($modelMock);

        // Act
        $result = $this->ideeProjetService->filterBy($filterParam);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
    }

    public function test_demandeurs_retourne_liste_organisations_et_utilisateurs()
    {
        // Arrange
        $organisation = Mockery::mock();
        $organisation->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $organisation->shouldReceive('getAttribute')->with('nom')->andReturn('Organisation Test');

        $user = Mockery::mock();
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $personne = Mockery::mock();
        $personne->shouldReceive('getAttribute')->with('nom')->andReturn('Doe');
        $personne->shouldReceive('getAttribute')->with('prenom')->andReturn('John');
        $user->shouldReceive('getAttribute')->with('personne')->andReturn($personne);

        Organisation::shouldReceive('institutions')->andReturnSelf();
        Organisation::shouldReceive('get')->andReturn(collect([$organisation]));

        User::shouldReceive('all')->andReturn(collect([$user]));

        // Act
        $result = $this->ideeProjetService->demandeurs();

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
        
        $data = json_decode($result->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']);
    }

    public function test_extraction_relations_depuis_champs()
    {
        // Test des méthodes privées via réflexion
        $reflection = new \ReflectionClass($this->ideeProjetService);
        $method = $reflection->getMethod('extractRelationsFromChamps');
        $method->setAccessible(true);

        $champsData = [
            'titre_projet' => 'Test',
            'cibles' => [1, 2],
            'odds' => [1],
            'departements' => [1],
            'autres_donnees' => 'test'
        ];

        $result = $method->invoke($this->ideeProjetService, $champsData);

        $this->assertArrayHasKey('cibles', $result);
        $this->assertArrayHasKey('odds', $result);
        $this->assertArrayHasKey('departements', $result);
        $this->assertArrayNotHasKey('titre_projet', $result);
        $this->assertArrayNotHasKey('autres_donnees', $result);
    }

    public function test_preparation_valeur_json()
    {
        $reflection = new \ReflectionClass($this->ideeProjetService);
        $method = $reflection->getMethod('prepareJsonValue');
        $method->setAccessible(true);

        // Test avec array
        $result = $method->invoke($this->ideeProjetService, ['test', 'value']);
        $this->assertEquals(['test', 'value'], $result);

        // Test avec string vide
        $result = $method->invoke($this->ideeProjetService, '');
        $this->assertNull($result);

        // Test avec string valide
        $result = $method->invoke($this->ideeProjetService, 'test value');
        $this->assertEquals(['test value'], $result);

        // Test avec JSON string
        $result = $method->invoke($this->ideeProjetService, '["test", "value"]');
        $this->assertEquals(['test', 'value'], $result);
    }

    public function test_sanitize_attribute_value()
    {
        $reflection = new \ReflectionClass($this->ideeProjetService);
        $method = $reflection->getMethod('sanitizeAttributeValue');
        $method->setAccessible(true);

        // Test avec string
        $result = $method->invoke($this->ideeProjetService, '  test value  ');
        $this->assertEquals('test value', $result);

        // Test avec string vide
        $result = $method->invoke($this->ideeProjetService, '   ');
        $this->assertNull($result);

        // Test avec number
        $result = $method->invoke($this->ideeProjetService, 123);
        $this->assertEquals(123, $result);

        // Test avec array
        $result = $method->invoke($this->ideeProjetService, ['test', 'value']);
        $this->assertEquals('test, value', $result);
    }
}