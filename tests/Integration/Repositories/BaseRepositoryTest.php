<?php

namespace Tests\Integration\Repositories;

use Tests\TestCase;
use App\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BaseRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    protected $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = app(UserRepository::class);
    }

    public function test_repository_peut_creer_un_utilisateur()
    {
        // Arrange
        $userData = [
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'statut' => 1
        ];

        // Act
        $user = $this->userRepository->create($userData);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_repository_peut_recuperer_un_utilisateur_par_id()
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'find@example.com'
        ]);

        // Act
        $foundUser = $this->userRepository->find($user->id);

        // Assert
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertEquals('find@example.com', $foundUser->email);
    }

    public function test_repository_retourne_null_pour_utilisateur_inexistant()
    {
        // Act
        $foundUser = $this->userRepository->find(99999);

        // Assert
        $this->assertNull($foundUser);
    }

    public function test_find_or_fail_lance_exception_pour_utilisateur_inexistant()
    {
        // Assert
        $this->expectException(ModelNotFoundException::class);

        // Act
        $this->userRepository->findOrFail(99999);
    }

    public function test_repository_peut_mettre_a_jour_un_utilisateur()
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'old@example.com'
        ]);

        $updateData = [
            'email' => 'new@example.com'
        ];

        // Act
        $result = $this->userRepository->update($user->id, $updateData);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new@example.com'
        ]);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'email' => 'old@example.com'
        ]);
    }

    public function test_repository_peut_supprimer_un_utilisateur()
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'delete@example.com'
        ]);

        // Act
        $result = $this->userRepository->delete($user->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'email' => 'delete@example.com'
        ]);
    }

    public function test_repository_peut_recuperer_tous_les_utilisateurs()
    {
        // Arrange
        User::factory()->count(3)->create();

        // Act
        $users = $this->userRepository->all();

        // Assert
        $this->assertGreaterThanOrEqual(3, $users->count());
    }

    public function test_repository_peut_compter_les_utilisateurs()
    {
        // Arrange
        $initialCount = $this->userRepository->getCount();
        User::factory()->count(2)->create();

        // Act
        $newCount = $this->userRepository->getCount();

        // Assert
        $this->assertEquals($initialCount + 2, $newCount);
    }

    public function test_repository_peut_paginer_les_utilisateurs()
    {
        // Arrange
        User::factory()->count(20)->create();

        // Act
        $paginatedUsers = $this->userRepository->paginate(10);

        // Assert
        $this->assertEquals(10, $paginatedUsers->perPage());
        $this->assertGreaterThanOrEqual(20, $paginatedUsers->total());
    }

    public function test_find_by_attribute_retourne_utilisateur_correct()
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'attribute@example.com'
        ]);

        // Act
        $foundUser = $this->userRepository->findByAttribute('email', 'attribute@example.com');

        // Assert
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertEquals('attribute@example.com', $foundUser->email);
    }

    public function test_find_by_attribute_retourne_null_pour_utilisateur_inexistant()
    {
        // Act
        $foundUser = $this->userRepository->findByAttribute('email', 'inexistant@example.com');

        // Assert
        $this->assertNull($foundUser);
    }

    public function test_find_by_attribute_retourne_null_pour_id()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $foundUser = $this->userRepository->findByAttribute('id', $user->id);

        // Assert
        $this->assertNull($foundUser);
    }

    public function test_new_instance_retourne_nouvelle_instance_du_modele()
    {
        // Act
        $instance = $this->userRepository->newInstance();

        // Assert
        $this->assertInstanceOf(User::class, $instance);
        $this->assertTrue($instance->exists === false);
    }

    public function test_get_instance_retourne_instance_du_modele()
    {
        // Act
        $instance = $this->userRepository->getInstance();

        // Assert
        $this->assertInstanceOf(User::class, $instance);
    }

    public function test_get_model_retourne_modele()
    {
        // Act
        $model = $this->userRepository->getModel();

        // Assert
        $this->assertInstanceOf(User::class, $model);
    }

    public function test_new_cree_nouvelle_instance_avec_donnees()
    {
        // Arrange
        $userData = [
            'email' => 'new@example.com',
            'password' => 'password123'
        ];

        // Act
        $user = $this->userRepository->new($userData);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('new@example.com', $user->email);
        $this->assertEquals('password123', $user->password);
    }

    public function test_fill_remplit_modele_avec_donnees()
    {
        // Arrange
        $userData = [
            'email' => 'fill@example.com',
            'password' => 'password123'
        ];

        // Act
        $user = $this->userRepository->fill($userData);

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('fill@example.com', $user->email);
        $this->assertEquals('password123', $user->password);
    }

    public function test_first_retourne_premier_utilisateur()
    {
        // Arrange
        User::factory()->create(['email' => 'first@example.com']);

        // Act
        $user = $this->userRepository->first();

        // Assert
        $this->assertInstanceOf(User::class, $user);
    }

    public function test_find_by_id_avec_objet_retourne_objet()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $foundUser = $this->userRepository->findById($user);

        // Assert
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }

    public function test_update_retourne_false_pour_utilisateur_inexistant()
    {
        // Act
        $result = $this->userRepository->update(99999, ['email' => 'test@example.com']);

        // Assert
        $this->assertFalse($result);
    }

    public function test_delete_retourne_false_pour_utilisateur_inexistant()
    {
        // Act
        $result = $this->userRepository->delete(99999);

        // Assert
        $this->assertFalse($result);
    }
}