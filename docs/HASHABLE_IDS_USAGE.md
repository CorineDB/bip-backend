# Guide d'utilisation du système de Hashage des IDs

Ce guide explique comment utiliser le système de hashage/déhashage des IDs dans l'application.

## Table des matières

1. [Introduction](#introduction)
2. [Configuration](#configuration)
3. [Utilisation du trait HashableId](#utilisation-du-trait-hashableid)
4. [Règles de validation](#règles-de-validation)
5. [Middleware](#middleware)
6. [Exemples pratiques](#exemples-pratiques)

---

## Introduction

Le système de hashage des IDs permet de:
- **Masquer les IDs numériques** dans les URLs et les API
- **Améliorer la sécurité** en rendant difficile la prédiction des IDs
- **Garder les IDs courts et lisibles** grâce à Hashids
- **Déhasher automatiquement** les IDs dans les routes et validations

**Package utilisé:** `vinkla/hashids` v13.0

---

## Configuration

### Variables d'environnement

Ajoutez ces variables dans votre fichier `.env`:

```env
# Salt pour le hashage (obligatoire, doit être unique et secret)
HASHIDS_SALT="votre-clé-secrète-unique"

# Longueur minimale des IDs hashés (optionnel, par défaut 8)
HASHIDS_MIN_LENGTH=64
```

⚠️ **Important:** Ne changez jamais le `HASHIDS_SALT` en production, sinon tous les IDs hashés existants deviendront invalides.

### Configuration dans `config/app.php`

La configuration est déjà ajoutée:

```php
'hashids_salt' => env('HASHIDS_SALT', env('APP_KEY')),
'hashids_min_length' => env('HASHIDS_MIN_LENGTH', 64),
```

---

## Utilisation du trait HashableId

### 1. Ajouter le trait à un modèle

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HashableId;

class Village extends Model
{
    use HashableId;

    // Votre code de modèle...
}
```

### 2. Méthodes disponibles

#### a) Hasher un ID

```php
// Méthode d'instance (attribut virtuel)
$village = Village::find(1);
$hashedId = $village->hashed_id; // Ex: "abc123XY"

// Méthode statique
$hashedId = Village::hashId(1); // Ex: "abc123XY"

// Hasher plusieurs IDs
$hashedIds = Village::hashIds([1, 2, 3]);
// ['abc123XY', 'def456ZW', 'ghi789QR']
```

#### b) Déhasher un ID

```php
// Déhasher un ID
$id = Village::unhashId('abc123XY'); // 1

// Déhasher plusieurs IDs
$ids = Village::unhashIds(['abc123XY', 'def456ZW']);
// [1, 2]

// Vérifier si un ID hashé est valide
$isValid = Village::isValidHashedId('abc123XY'); // true/false
```

#### c) Trouver un modèle par ID hashé

```php
// Retourne le modèle ou null
$village = Village::findByHashedId('abc123XY');

// Retourne le modèle ou lance une exception
$village = Village::findByHashedIdOrFail('abc123XY');
```

#### d) Scopes pour les requêtes

```php
// Utiliser dans une requête Eloquent
$village = Village::whereHashedId('abc123XY')->first();

// Avec plusieurs IDs
$villages = Village::whereHashedIdIn(['abc123XY', 'def456ZW'])->get();
```

### 3. Sérialisation JSON automatique

Le trait ajoute automatiquement `hashed_id` dans le JSON:

```php
$village = Village::find(1);
return $village->toArray();

// Résultat:
[
    'id' => 1,
    'nom' => 'Village Test',
    'hashed_id' => 'abc123XY', // Ajouté automatiquement
    // autres attributs...
]
```

---

## Règles de validation

### 1. HashedExists - Valider un ID hashé unique

```php
use App\Rules\HashedExists;
use App\Models\Arrondissement;

class UpdateVillageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Validation simple avec nom de modèle
            'arrondissement_id' => [
                'required',
                new HashedExists(Arrondissement::class)
            ],

            // Ou avec nom de table
            'arrondissement_id' => [
                'required',
                new HashedExists('arrondissements', 'id')
            ],

            // Avec conditions supplémentaires
            'arrondissement_id' => [
                'required',
                HashedExists::make(Arrondissement::class)->where(function($query) {
                    $query->where('is_active', true);
                })
            ],
        ];
    }
}
```

### 2. HashedExistsMultiple - Valider un tableau d'IDs hashés

```php
use App\Rules\HashedExistsMultiple;
use App\Models\User;

class ShareFileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_ids' => [
                'required',
                'array',
                new HashedExistsMultiple(User::class)
            ],
        ];
    }
}
```

### 3. Déhashage dans prepareForValidation

```php
class UpdateVillageRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // Déhasher avant la validation
        if ($this->has('arrondissement_hashed_id')) {
            $this->merge([
                'arrondissementId' => Arrondissement::unhashId(
                    $this->arrondissement_hashed_id
                )
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'arrondissementId' => ['required', 'integer', 'exists:arrondissements,id']
        ];
    }
}
```

---

## Middleware

Le middleware `UnhashRouteParameters` déhash automatiquement les IDs dans les paramètres de route.

### Configuration

Le middleware est déjà enregistré dans `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->api(append: [
        \App\Http\Middleware\UnhashRouteParameters::class,
    ]);
})
```

### Paramètres déhashés automatiquement

Par défaut, ces paramètres sont déhashés:

- `id`
- `user_id`
- `fichier_id`
- `dossier_id`
- `projet_id`

### Personnalisation

Pour ajouter d'autres paramètres, modifiez le middleware:

```php
// app/Http/Middleware/UnhashRouteParameters.php

protected array $hashableParameters = [
    'id' => null,
    'village_id' => \App\Models\Village::class,
    'arrondissement_id' => \App\Models\Arrondissement::class,
    // Ajoutez vos paramètres ici
];
```

### Utilisation dans les routes

```php
// routes/api.php

// L'ID sera automatiquement déhashé par le middleware
Route::get('/villages/{id}', [VillageController::class, 'show']);

// Dans le contrôleur
public function show($id)
{
    // $id est déjà déhashé (valeur numérique)
    $village = Village::findOrFail($id);
    return new VillageResource($village);
}
```

---

## Exemples pratiques

### Exemple 1: API RESTful avec IDs hashés

#### Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Http\Resources\VillageResource;
use Illuminate\Http\Request;

class VillageController extends Controller
{
    /**
     * Liste des villages (retourne hashed_id dans le JSON)
     */
    public function index()
    {
        $villages = Village::all();
        return VillageResource::collection($villages);
    }

    /**
     * Afficher un village par son ID hashé
     * Le middleware déhash automatiquement le paramètre 'id'
     */
    public function show($id)
    {
        $village = Village::findOrFail($id); // $id est déjà déhashé
        return new VillageResource($village);
    }

    /**
     * Mettre à jour un village
     */
    public function update(UpdateVillageRequest $request, $id)
    {
        $village = Village::findOrFail($id); // $id est déjà déhashé
        $village->update($request->validated());

        return new VillageResource($village);
    }
}
```

#### Resource

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VillageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->hashed_id, // Utiliser hashed_id au lieu de id
            'nom' => $this->nom,
            'code' => $this->code,
            'arrondissement' => new ArrondissementResource($this->whenLoaded('arrondissement')),
            'created_at' => $this->created_at,
        ];
    }
}
```

### Exemple 2: Validation avec IDs hashés

```php
<?php

namespace App\Http\Requests\villages;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\HashedExists;
use App\Models\Arrondissement;

class StoreVillageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:villages'],

            // ID hashé de l'arrondissement
            'arrondissement_id' => [
                'required',
                new HashedExists(Arrondissement::class)
            ],

            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * Déhasher l'ID avant de l'utiliser
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('arrondissement_id')) {
            $this->merge([
                'arrondissementId' => Arrondissement::unhashId($this->arrondissement_id)
            ]);
        }
    }
}
```

### Exemple 3: Partage de fichiers avec plusieurs utilisateurs

```php
<?php

namespace App\Http\Requests\fichiers;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\HashedExistsMultiple;
use App\Models\User;

class ShareFichierRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Tableau d'IDs hashés d'utilisateurs
            'user_ids' => [
                'required',
                'array',
                'min:1',
                'max:50',
                new HashedExistsMultiple(User::class)
            ],

            'permissions' => ['array'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    /**
     * Déhasher tous les user_ids
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('user_ids')) {
            $this->merge([
                'user_ids' => User::unhashIds($this->user_ids)
            ]);
        }
    }
}
```

---

## Bonnes pratiques

### ✅ À faire

1. **Toujours utiliser `hashed_id` dans les Resources**
   ```php
   return [
       'id' => $this->hashed_id, // ✅
       // ...
   ];
   ```

2. **Déhasher dans `prepareForValidation`**
   ```php
   protected function prepareForValidation(): void {
       $this->merge([
           'real_id' => Model::unhashId($this->hashed_id)
       ]);
   }
   ```

3. **Utiliser les règles de validation personnalisées**
   ```php
   'model_id' => [new HashedExists(Model::class)]
   ```

### ❌ À éviter

1. **Ne jamais exposer les IDs numériques dans l'API**
   ```php
   return ['id' => $this->id]; // ❌ Mauvais
   ```

2. **Ne pas changer le HASHIDS_SALT en production**
   ```env
   HASHIDS_SALT="old-salt" # ❌ Ne jamais changer
   ```

3. **Ne pas stocker les IDs hashés en base de données**
   ```php
   $village->hashed_id_field = $village->hashed_id; // ❌ Inutile
   ```

---

## Dépannage

### Problème: "Invalid hashed ID"

**Cause:** L'ID hashé ne peut pas être décodé.

**Solutions:**
- Vérifier que `HASHIDS_SALT` est correctement configuré
- Vérifier que l'ID hashé n'a pas été modifié
- Vérifier que le même salt est utilisé pour hasher et déhasher

### Problème: Les IDs ne sont pas déhashés dans les routes

**Cause:** Le middleware n'est pas enregistré ou le paramètre n'est pas dans la liste.

**Solutions:**
- Vérifier que le middleware est enregistré dans `bootstrap/app.php`
- Ajouter le paramètre dans `$hashableParameters` du middleware

### Problème: Validation échoue avec HashedExists

**Cause:** L'ID hashé est invalide ou l'enregistrement n'existe pas.

**Solutions:**
- Utiliser `isValidHashedId()` pour vérifier l'ID avant la validation
- Vérifier que le modèle utilise le trait `HashableId`
- Vérifier les logs pour voir l'erreur exacte

---

## Ressources

- [Documentation Hashids](https://hashids.org/)
- [Package vinkla/hashids](https://github.com/vinkla/hashids)
- Code source du trait: `app/Traits/HashableId.php`
- Règles de validation: `app/Rules/HashedExists.php` et `app/Rules/HashedExistsMultiple.php`
- Middleware: `app/Http/Middleware/UnhashRouteParameters.php`
