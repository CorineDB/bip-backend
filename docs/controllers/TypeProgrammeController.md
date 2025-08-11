# TypeProgrammeController

## Description
Le `TypeProgrammeController` gère les opérations CRUD pour les types de programmes et fournit des fonctionnalités pour récupérer les programmes et leurs composants.

## Namespace
`App\Http\Controllers\TypeProgrammeController`

## Dépendances
- `TypeProgrammeServiceInterface` : Interface de service pour la logique métier
- `StoreTypeProgrammeRequest` : Validation des données pour la création
- `UpdateTypeProgrammeRequest` : Validation des données pour la mise à jour

## Méthodes

### `index(): JsonResponse`
Récupère la liste complète des types de programmes.

**Endpoint**: `GET /api/types-programme`
**Authentification**: Requise (`auth:api`)

**Réponse**:
```json
{
    "status": "success",
    "message": "Liste des types de programmes",
    "data": [
        {
            "id": 1,
            "nom": "Programme National",
            "description": "Description du programme national",
            "created_at": "2024-01-01T00:00:00Z",
            "updated_at": "2024-01-01T00:00:00Z"
        }
    ]
}
```

### `show($id): JsonResponse`
Récupère un type de programme spécifique par son ID.

**Endpoint**: `GET /api/types-programme/{id}`
**Authentification**: Requise (`auth:api`)

**Paramètres**:
- `id` (integer) : L'identifiant du type de programme

**Réponse**:
```json
{
    "status": "success",
    "message": "Type de programme trouvé",
    "data": {
        "id": 1,
        "nom": "Programme National",
        "description": "Description du programme national",
        "created_at": "2024-01-01T00:00:00Z",
        "updated_at": "2024-01-01T00:00:00Z"
    }
}
```

### `store(StoreTypeProgrammeRequest $request): JsonResponse`
Crée un nouveau type de programme.

**Endpoint**: `POST /api/types-programme`
**Authentification**: Requise (`auth:api`)

**Body de la requête**:
```json
{
    "nom": "Nouveau Programme",
    "description": "Description du nouveau programme"
}
```

**Réponse**:
```json
{
    "status": "success",
    "message": "Type de programme créé avec succès",
    "data": {
        "id": 2,
        "nom": "Nouveau Programme",
        "description": "Description du nouveau programme",
        "created_at": "2024-01-01T00:00:00Z",
        "updated_at": "2024-01-01T00:00:00Z"
    }
}
```

### `update(UpdateTypeProgrammeRequest $request, $id): JsonResponse`
Met à jour un type de programme existant.

**Endpoint**: `PUT/PATCH /api/types-programme/{id}`
**Authentification**: Requise (`auth:api`)

**Paramètres**:
- `id` (integer) : L'identifiant du type de programme à modifier

**Body de la requête**:
```json
{
    "nom": "Programme Modifié",
    "description": "Description modifiée"
}
```

**Réponse**:
```json
{
    "status": "success",
    "message": "Type de programme mis à jour avec succès",
    "data": {
        "id": 1,
        "nom": "Programme Modifié",
        "description": "Description modifiée",
        "created_at": "2024-01-01T00:00:00Z",
        "updated_at": "2024-01-01T12:00:00Z"
    }
}
```

### `destroy($id): JsonResponse`
Supprime un type de programme.

**Endpoint**: `DELETE /api/types-programme/{id}`
**Authentification**: Requise (`auth:api`)

**Paramètres**:
- `id` (integer) : L'identifiant du type de programme à supprimer

**Réponse**:
```json
{
    "status": "success",
    "message": "Type de programme supprimé avec succès"
}
```

### `programmes(): JsonResponse`
Récupère la liste des programmes disponibles.

**Endpoint**: `GET /api/programmes`
**Authentification**: Requise (`auth:api`)

**Réponse**:
```json
{
    "status": "success",
    "message": "Liste des programmes",
    "data": [
        {
            "id": 1,
            "nom": "Programme de Développement",
            "type_programme_id": 1,
            "statut": "actif"
        }
    ]
}
```

### `composants_de_programme($idProgramme): JsonResponse`
Récupère les composants associés à un programme spécifique.

**Endpoint**: `GET /api/programmes/{id}/composants-programme`
**Authentification**: Requise (`auth:api`)

**Paramètres**:
- `idProgramme` (integer) : L'identifiant du programme

**Réponse**:
```json
{
    "status": "success",
    "message": "Composants du programme",
    "data": [
        {
            "id": 1,
            "nom": "Composant Infrastructure",
            "programme_id": 1,
            "budget": 1000000
        }
    ]
}
```

## Routes associées

```php
// Routes API Resource
Route::apiResource('types-programme', TypeProgrammeController::class)
    ->parameters(['types-programme' => 'type_programme']);

// Routes spécifiques pour les programmes
Route::prefix('programmes')->name('programmes.')->controller(TypeProgrammeController::class)->group(function () {
    Route::get("{id}/composants-programme", "composants_de_programme");
    Route::get("/", "programmes");
});
```

## Gestion des erreurs

Les erreurs courantes retournées par ce contrôleur :

- **404 Not Found** : Type de programme ou programme non trouvé
- **422 Unprocessable Entity** : Erreurs de validation des données
- **401 Unauthorized** : Token d'authentification manquant ou invalide
- **500 Internal Server Error** : Erreur serveur interne

## Notes
- Toutes les méthodes nécessitent une authentification via l'API (`auth:api` middleware)
- La logique métier est déléguée au service `TypeProgrammeServiceInterface`
- Les validations sont gérées par les Form Requests spécifiques
- Il y a une annotation Swagger incorrecte dans la méthode `programmes()` qui fait référence à la grille d'analyse multicritères - cela devrait être corrigé