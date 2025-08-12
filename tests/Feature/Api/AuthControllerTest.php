<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $authServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->authServiceMock = Mockery::mock(AuthService::class);
        $this->app->instance(AuthService::class, $this->authServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_login_retourne_url_de_connexion_keycloak()
    {
        // Arrange
        $expectedUrl = 'https://keycloak.example.com/auth/realms/test/protocol/openid-connect/auth';
        
        $this->authServiceMock
            ->shouldReceive('getLoginUrl')
            ->with(Mockery::any())
            ->andReturn($expectedUrl);

        // Act
        $response = $this->getJson('/api/auth/login');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'login_url' => $expectedUrl,
                'message' => 'URL de connexion Keycloak générée'
            ]);
    }

    public function test_login_avec_redirect_uri_personnalise()
    {
        // Arrange
        $customRedirectUri = 'https://myapp.com/callback';
        $expectedUrl = 'https://keycloak.example.com/auth/realms/test/protocol/openid-connect/auth';
        
        $this->authServiceMock
            ->shouldReceive('getLoginUrl')
            ->with($customRedirectUri)
            ->andReturn($expectedUrl);

        // Act
        $response = $this->getJson('/api/auth/login?redirect_uri=' . urlencode($customRedirectUri));

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'login_url' => $expectedUrl
            ]);
    }

    public function test_login_gere_les_erreurs()
    {
        // Arrange
        $this->authServiceMock
            ->shouldReceive('getLoginUrl')
            ->andThrow(new \Exception('Erreur Keycloak'));

        // Act
        $response = $this->getJson('/api/auth/login');

        // Assert
        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Erreur lors de la génération de l\'URL de connexion'
            ]);
    }

    public function test_callback_avec_code_valide()
    {
        // Arrange
        $code = 'valid_authorization_code';
        $tokenData = [
            'access_token' => 'valid_access_token',
            'refresh_token' => 'valid_refresh_token',
            'expires_in' => 3600
        ];
        $userInfo = [
            'sub' => 'keycloak-user-id',
            'email' => 'user@example.com',
            'name' => 'Test User'
        ];
        $user = (object) ['id' => 1, 'email' => 'user@example.com'];

        $this->authServiceMock
            ->shouldReceive('exchangeCodeForToken')
            ->with($code, Mockery::any())
            ->andReturn($tokenData);

        $this->authServiceMock
            ->shouldReceive('validateToken')
            ->with($tokenData['access_token'])
            ->andReturn($userInfo);

        $this->authServiceMock
            ->shouldReceive('getOrCreateUser')
            ->with($userInfo)
            ->andReturn($user);

        // Act
        $response = $this->postJson('/api/auth/callback', ['code' => $code]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Connexion réussie'
            ])
            ->assertJsonStructure([
                'success',
                'data',
                'message'
            ]);
    }

    public function test_callback_sans_code_retourne_erreur()
    {
        // Act
        $response = $this->postJson('/api/auth/callback', []);

        // Assert
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Code d\'autorisation manquant'
            ]);
    }

    public function test_callback_avec_code_invalide()
    {
        // Arrange
        $code = 'invalid_code';
        
        $this->authServiceMock
            ->shouldReceive('exchangeCodeForToken')
            ->with($code, Mockery::any())
            ->andReturn(null);

        // Act
        $response = $this->postJson('/api/auth/callback', ['code' => $code]);

        // Assert
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Échec de l\'échange du code d\'autorisation'
            ]);
    }

    public function test_callback_avec_token_invalide()
    {
        // Arrange
        $code = 'valid_code';
        $tokenData = ['access_token' => 'invalid_token'];
        
        $this->authServiceMock
            ->shouldReceive('exchangeCodeForToken')
            ->with($code, Mockery::any())
            ->andReturn($tokenData);

        $this->authServiceMock
            ->shouldReceive('validateToken')
            ->with($tokenData['access_token'])
            ->andReturn(null);

        // Act
        $response = $this->postJson('/api/auth/callback', ['code' => $code]);

        // Assert
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Token invalide'
            ]);
    }

    public function test_refresh_avec_token_valide()
    {
        // Arrange
        $refreshToken = 'valid_refresh_token';
        $newTokenData = [
            'access_token' => 'new_access_token',
            'refresh_token' => 'new_refresh_token',
            'expires_in' => 3600
        ];

        $this->authServiceMock
            ->shouldReceive('refreshToken')
            ->with($refreshToken)
            ->andReturn($newTokenData);

        // Act
        $response = $this->postJson('/api/auth/refresh', ['refresh_token' => $refreshToken]);

        // Assert
        $response->assertStatus(200);
    }

    public function test_refresh_sans_token_retourne_erreur()
    {
        // Act
        $response = $this->postJson('/api/auth/refresh', []);

        // Assert
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Token de rafraîchissement requis'
            ]);
    }

    public function test_refresh_avec_token_invalide()
    {
        // Arrange
        $refreshToken = 'invalid_refresh_token';
        
        $this->authServiceMock
            ->shouldReceive('refreshToken')
            ->with($refreshToken)
            ->andReturn(null);

        // Act
        $response = $this->postJson('/api/auth/refresh', ['refresh_token' => $refreshToken]);

        // Assert
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Token de rafraîchissement invalide'
            ]);
    }

    public function test_logout_avec_token()
    {
        // Arrange
        $token = 'valid_token';
        
        $this->authServiceMock
            ->shouldReceive('logout')
            ->with($token)
            ->once();

        // Act
        $response = $this->postJson('/api/auth/logout', ['keycloak_token' => $token]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);
    }

    public function test_logout_sans_token()
    {
        // Act
        $response = $this->postJson('/api/auth/logout');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);
    }

    public function test_logout_gere_les_erreurs()
    {
        // Arrange
        $token = 'valid_token';
        
        $this->authServiceMock
            ->shouldReceive('logout')
            ->with($token)
            ->andThrow(new \Exception('Erreur lors de la déconnexion'));

        // Act
        $response = $this->postJson('/api/auth/logout', ['keycloak_token' => $token]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);
    }

    public function test_introspect_avec_token_valide()
    {
        // Arrange
        $token = 'valid_token';
        $introspectionData = [
            'active' => true,
            'sub' => 'user-id',
            'exp' => time() + 3600
        ];

        $this->authServiceMock
            ->shouldReceive('introspectToken')
            ->with($token)
            ->andReturn($introspectionData);

        // Act
        $response = $this->postJson('/api/auth/introspect', ['keycloak_token' => $token]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'token_info' => $introspectionData,
                'message' => 'Introspection du token réussie'
            ]);
    }

    public function test_introspect_sans_token()
    {
        // Act
        $response = $this->postJson('/api/auth/introspect');

        // Assert
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Token requis'
            ]);
    }

    public function test_introspect_avec_token_invalide()
    {
        // Arrange
        $token = 'invalid_token';
        
        $this->authServiceMock
            ->shouldReceive('introspectToken')
            ->with($token)
            ->andReturn(null);

        // Act
        $response = $this->postJson('/api/auth/introspect', ['keycloak_token' => $token]);

        // Assert
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Token invalide ou inactif'
            ]);
    }

    public function test_profile_retourne_informations_utilisateur()
    {
        // Arrange
        $user = (object) [
            'id' => 1,
            'email' => 'user@example.com',
            'name' => 'Test User'
        ];

        // Mock l'utilisateur authentifié
        $this->actingAs($user);

        // Act
        $response = $this->getJson('/api/auth/profile');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profil utilisateur récupéré'
            ])
            ->assertJsonStructure([
                'success',
                'data',
                'message'
            ]);
    }

    public function test_update_profile_avec_donnees_valides()
    {
        // Arrange
        $user = \App\Models\User::factory()->create([
            'email' => 'user@example.com'
        ]);

        $updateData = [
            'name' => 'Nouveau Nom',
            'settings' => ['theme' => 'dark']
        ];

        $this->actingAs($user);

        // Act
        $response = $this->putJson('/api/auth/profile', $updateData);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profil mis à jour avec succès'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nouveau Nom'
        ]);
    }

    public function test_update_profile_avec_donnees_invalides()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $invalidData = [
            'name' => str_repeat('a', 300) // Nom trop long
        ];

        // Act
        $response = $this->putJson('/api/auth/profile', $invalidData);

        // Assert
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Données de requête invalides'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ]);
    }
}