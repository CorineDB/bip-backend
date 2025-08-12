<?php

namespace Tests\Integration\Repositories;

use Tests\TestCase;
use App\Repositories\IdeeProjetRepository;
use App\Models\IdeeProjet;
use App\Models\User;
use App\Models\Secteur;
use App\Models\CategorieProjet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class IdeeProjetRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    protected $ideeProjetRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ideeProjetRepository = app(IdeeProjetRepository::class);
    }

    public function test_repository_peut_creer_une_idee_projet()
    {
        // Arrange
        $user = User::factory()->create();
        $secteur = Secteur::factory()->create();
        $categorie = CategorieProjet::factory()->create();

        $ideeData = [
            'titre_projet' => 'Test Idée Projet',
            'description' => 'Description du projet de test',
            'responsableId' => $user->id,
            'secteurId' => $secteur->id,
            'categorieId' => $categorie->id,
            'statut' => 'BROUILLON',
            'est_soumise' => false
        ];

        // Act
        $idee = $this->ideeProjetRepository->create($ideeData);

        // Assert
        $this->assertInstanceOf(IdeeProjet::class, $idee);
        $this->assertEquals('Test Idée Projet', $idee->titre_projet);
        $this->assertDatabaseHas('idees_projet', [
            'titre_projet' => 'Test Idée Projet',
            'responsableId' => $user->id
        ]);
    }

    public function test_repository_peut_recuperer_une_idee_projet_par_id()
    {
        // Arrange
        $user = User::factory()->create();
        $idee = IdeeProjet::factory()->create([
            'titre_projet' => 'Find Test',
            'responsableId' => $user->id
        ]);

        // Act
        $foundIdee = $this->ideeProjetRepository->find($idee->id);

        // Assert
        $this->assertInstanceOf(IdeeProjet::class, $foundIdee);
        $this->assertEquals($idee->id, $foundIdee->id);
        $this->assertEquals('Find Test', $foundIdee->titre_projet);
    }

    public function test_repository_peut_mettre_a_jour_une_idee_projet()
    {
        // Arrange
        $user = User::factory()->create();
        $idee = IdeeProjet::factory()->create([
            'titre_projet' => 'Ancien Titre',
            'responsableId' => $user->id
        ]);

        $updateData = [
            'titre_projet' => 'Nouveau Titre',
            'description' => 'Nouvelle description'
        ];

        // Act
        $result = $this->ideeProjetRepository->update($idee->id, $updateData);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('idees_projet', [
            'id' => $idee->id,
            'titre_projet' => 'Nouveau Titre',
            'description' => 'Nouvelle description'
        ]);
    }

    public function test_repository_peut_supprimer_une_idee_projet()
    {
        // Arrange
        $user = User::factory()->create();
        $idee = IdeeProjet::factory()->create([
            'titre_projet' => 'À Supprimer',
            'responsableId' => $user->id
        ]);

        // Act
        $result = $this->ideeProjetRepository->delete($idee->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('idees_projet', [
            'id' => $idee->id,
            'titre_projet' => 'À Supprimer'
        ]);
    }

    public function test_repository_peut_recuperer_toutes_les_idees_projet()
    {
        // Arrange
        $user = User::factory()->create();
        IdeeProjet::factory()->count(3)->create([
            'responsableId' => $user->id
        ]);

        // Act
        $idees = $this->ideeProjetRepository->all();

        // Assert
        $this->assertGreaterThanOrEqual(3, $idees->count());
    }

    public function test_repository_peut_paginer_les_idees_projet()
    {
        // Arrange
        $user = User::factory()->create();
        IdeeProjet::factory()->count(15)->create([
            'responsableId' => $user->id
        ]);

        // Act
        $paginatedIdees = $this->ideeProjetRepository->paginate(10);

        // Assert
        $this->assertEquals(10, $paginatedIdees->perPage());
        $this->assertGreaterThanOrEqual(15, $paginatedIdees->total());
    }

    public function test_find_by_attribute_peut_trouver_par_titre()
    {
        // Arrange
        $user = User::factory()->create();
        $idee = IdeeProjet::factory()->create([
            'titre_projet' => 'Titre Unique Test',
            'responsableId' => $user->id
        ]);

        // Act
        $foundIdee = $this->ideeProjetRepository->findByAttribute('titre_projet', 'Titre Unique Test');

        // Assert
        $this->assertInstanceOf(IdeeProjet::class, $foundIdee);
        $this->assertEquals($idee->id, $foundIdee->id);
        $this->assertEquals('Titre Unique Test', $foundIdee->titre_projet);
    }

    public function test_find_by_attribute_peut_trouver_par_statut()
    {
        // Arrange
        $user = User::factory()->create();
        $idee = IdeeProjet::factory()->create([
            'statut' => 'ANALYSE',
            'responsableId' => $user->id
        ]);

        // Act
        $foundIdee = $this->ideeProjetRepository->findByAttribute('statut', 'ANALYSE');

        // Assert
        $this->assertInstanceOf(IdeeProjet::class, $foundIdee);
        $this->assertEquals('ANALYSE', $foundIdee->statut);
    }

    public function test_find_or_fail_lance_exception_pour_idee_inexistante()
    {
        // Assert
        $this->expectException(ModelNotFoundException::class);

        // Act
        $this->ideeProjetRepository->findOrFail(99999);
    }

    public function test_repository_peut_compter_les_idees_projet()
    {
        // Arrange
        $initialCount = $this->ideeProjetRepository->getCount();
        $user = User::factory()->create();
        IdeeProjet::factory()->count(2)->create([
            'responsableId' => $user->id
        ]);

        // Act
        $newCount = $this->ideeProjetRepository->getCount();

        // Assert
        $this->assertEquals($initialCount + 2, $newCount);
    }

    public function test_new_instance_retourne_nouvelle_instance_idee_projet()
    {
        // Act
        $instance = $this->ideeProjetRepository->newInstance();

        // Assert
        $this->assertInstanceOf(IdeeProjet::class, $instance);
        $this->assertTrue($instance->exists === false);
    }

    public function test_get_model_retourne_modele_idee_projet()
    {
        // Act
        $model = $this->ideeProjetRepository->getModel();

        // Assert
        $this->assertInstanceOf(IdeeProjet::class, $model);
    }

    public function test_fill_remplit_idee_projet_avec_donnees()
    {
        // Arrange
        $ideeData = [
            'titre_projet' => 'Projet Fill Test',
            'description' => 'Description fill test'
        ];

        // Act
        $idee = $this->ideeProjetRepository->fill($ideeData);

        // Assert
        $this->assertInstanceOf(IdeeProjet::class, $idee);
        $this->assertEquals('Projet Fill Test', $idee->titre_projet);
        $this->assertEquals('Description fill test', $idee->description);
    }

    public function test_first_retourne_premiere_idee_projet()
    {
        // Arrange
        $user = User::factory()->create();
        IdeeProjet::factory()->create([
            'titre_projet' => 'Premier Projet',
            'responsableId' => $user->id
        ]);

        // Act
        $idee = $this->ideeProjetRepository->first();

        // Assert
        $this->assertInstanceOf(IdeeProjet::class, $idee);
    }

    public function test_find_by_id_avec_objet_retourne_objet()
    {
        // Arrange
        $user = User::factory()->create();
        $idee = IdeeProjet::factory()->create([
            'responsableId' => $user->id
        ]);

        // Act
        $foundIdee = $this->ideeProjetRepository->findById($idee);

        // Assert
        $this->assertInstanceOf(IdeeProjet::class, $foundIdee);
        $this->assertEquals($idee->id, $foundIdee->id);
    }

    public function test_repository_retourne_null_pour_idee_inexistante()
    {
        // Act
        $foundIdee = $this->ideeProjetRepository->find(99999);

        // Assert
        $this->assertNull($foundIdee);
    }

    public function test_update_retourne_false_pour_idee_inexistante()
    {
        // Act
        $result = $this->ideeProjetRepository->update(99999, ['titre_projet' => 'Test']);

        // Assert
        $this->assertFalse($result);
    }

    public function test_delete_retourne_false_pour_idee_inexistante()
    {
        // Act
        $result = $this->ideeProjetRepository->delete(99999);

        // Assert
        $this->assertFalse($result);
    }

    public function test_find_by_attribute_retourne_null_pour_idee_inexistante()
    {
        // Act
        $foundIdee = $this->ideeProjetRepository->findByAttribute('titre_projet', 'Inexistant');

        // Assert
        $this->assertNull($foundIdee);
    }

    public function test_creation_avec_donnees_json()
    {
        // Arrange
        $user = User::factory()->create();
        $secteur = Secteur::factory()->create();
        $categorie = CategorieProjet::factory()->create();

        $ideeData = [
            'titre_projet' => 'Projet avec JSON',
            'responsableId' => $user->id,
            'secteurId' => $secteur->id,
            'categorieId' => $categorie->id,
            'cout_estimatif_projet' => ['montant' => 1000000, 'devise' => 'FCFA'],
            'parties_prenantes' => ['Ministère', 'ONG', 'Communauté'],
            'objectifs_specifiques' => ['Objectif 1', 'Objectif 2']
        ];

        // Act
        $idee = $this->ideeProjetRepository->create($ideeData);

        // Assert
        $this->assertInstanceOf(IdeeProjet::class, $idee);
        $this->assertEquals('Projet avec JSON', $idee->titre_projet);
        $this->assertIsArray($idee->cout_estimatif_projet);
        $this->assertIsArray($idee->parties_prenantes);
        $this->assertEquals(1000000, $idee->cout_estimatif_projet['montant']);
    }
}