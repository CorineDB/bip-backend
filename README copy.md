# BIP - Système de Gestion des Idées de Projets

## 📋 Vue d'ensemble

Le **BIP (Gestion des Idées de Projets)** est un système de gestion des idées de projets développé en Laravel pour le gouvernement de la République Démocratique du Congo. Il permet de gérer le cycle de vie complet des idées de projets, depuis leur création jusqu'à leur validation finale.

## 🏗️ Architecture

### Structure des Couches
```
┌─────────────────────────────────────────────────────────────┐
│                  COUCHE PRÉSENTATION                       │
│  Controllers → Resources → Form Requests → Middleware       │
└─────────────────────────────────────────────────────────────┘
                                │
┌─────────────────────────────────────────────────────────────┐
│                   COUCHE MÉTIER                            │
│  Services → Contracts → Enums → Business Logic             │
└─────────────────────────────────────────────────────────────┘
                                │
┌─────────────────────────────────────────────────────────────┐
│                   COUCHE DONNÉES                           │
│  Repositories → Models → Migrations → Seeders              │
└─────────────────────────────────────────────────────────────┘
```

### Patterns Utilisés
- **Service-Repository Pattern** pour la séparation des responsabilités
- **Dependency Injection** pour l'inversion de contrôle
- **RESTful API** pour les endpoints
- **Enum Pattern** pour les constantes métier

## 🧩 Architecture des Classes de Base

### Service-Repository Pattern

Le système utilise une architecture en couches avec des classes de base abstraites qui définissent les contrats et implémentations communes.

#### BaseRepositoryInterface
```php
interface BaseRepositoryInterface
{
    public function all();
    public function find($id);
    public function findOrFail($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
```

#### BaseRepository (Implémentation)
```php
abstract class BaseRepository implements BaseRepositoryInterface
{
    protected $model;

    public function __construct()
    {
        $this->model = $this->getModel();
    }

    abstract protected function getModel();

    public function all()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        return $this->model->find($id)->update($data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }
}
```

#### AbstractServiceInterface
```php
interface AbstractServiceInterface
{
    public function all(): JsonResponse;
    public function find(int|string $id): JsonResponse;
    public function create(array $data): JsonResponse;
    public function update(int|string $id, array $data): JsonResponse;
    public function delete(int|string $id): JsonResponse;
}
```

#### BaseService (Implémentation)
```php
abstract class BaseService implements AbstractServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected ApiResourceInterface $resource;

    public function __construct(
        BaseRepositoryInterface $repository,
        ApiResourceInterface $resource
    ) {
        $this->repository = $repository;
        $this->resource = $resource;
    }

    public function all(): JsonResponse
    {
        try {
            $data = $this->repository->all();
            return $this->resource::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function find(int|string $id): JsonResponse
    {
        try {
            $item = $this->repository->findOrFail($id);
            return (new $this->resource($item))->response();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
            ], 404);
        }
    }

    public function create(array $data): JsonResponse
    {
        try {
            $item = $this->repository->create($data);
            return (new $this->resource($item))
                ->additional(['message' => 'Resource created successfully.'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    // ... autres méthodes
}
```

### Exemple d'Utilisation : RoleService

#### 1. Repository Spécifique
```php
interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug);
}

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    protected function getModel()
    {
        return Role::class;
    }

    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->first();
    }
}
```

#### 2. Service Spécifique
```php
interface RoleServiceInterface extends AbstractServiceInterface
{
    public function findBySlug(string $slug): JsonResponse;
}

class RoleService extends BaseService implements RoleServiceInterface
{
    public function findBySlug(string $slug): JsonResponse
    {
        try {
            $role = $this->repository->findBySlug($slug);
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found.',
                ], 404);
            }
            return (new $this->resource($role))->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}
```

#### 3. Contrôleur
```php
class RoleController extends Controller
{
    protected RoleServiceInterface $service;

    public function __construct(RoleServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        return $this->service->all(); // Utilise BaseService::all()
    }

    public function show($id): JsonResponse
    {
        return $this->service->find($id); // Utilise BaseService::find()
    }

    public function findBySlug(string $slug): JsonResponse
    {
        return $this->service->findBySlug($slug); // Méthode spécifique
    }
}
```

#### 4. Configuration IoC (ServiceProvider)
```php
public function register(): void
{
    $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
    $this->app->bind(RoleServiceInterface::class, RoleService::class);
    
    $this->app->when(RoleService::class)
        ->needs(ApiResourceInterface::class)
        ->give(RoleResource::class);
}
```

### Avantages de cette Architecture

#### ✅ **Réutilisabilité**
- Code CRUD commun dans les classes de base
- Méthodes spécifiques dans les classes dérivées
- Réduction de la duplication de code

#### ✅ **Maintenabilité**
- Modifications centralisées dans les classes de base
- Séparation claire des responsabilités
- Tests facilitées par l'injection de dépendances

#### ✅ **Extensibilité**
- Ajout facile de nouvelles entités
- Respect du principe Open/Closed
- Polymorphisme via les interfaces

#### ✅ **Consistance**
- API uniforme pour toutes les entités
- Gestion d'erreurs standardisée
- Réponses JSON formatées de manière cohérente

## 🚀 Installation

### Prérequis
- PHP 8.2+
- Composer
- POSTGRES 8.0+
- Laravel 10.x

### Étapes d'installation

1. **Cloner le repository**
```bash
git clone <repository-url>
cd backend_api
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configuration de l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurer la base de données**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gdiz_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Exécuter les migrations et seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **Lancer le serveur**
```bash
php artisan serve
```

## 🗄️ Structure de la Base de Données

### Architecture Générale
```
Organisations → Personnes → Utilisateurs
            ↓
    Rôles ↔ Permissions
            ↓
    Idées de Projets (centrale)
            ↓
    Tables de Référence
```

### Principe des Relations
- **Hiérarchie organisationnelle** : Organisations contiennent personnes contiennent utilisateurs
- **Système de permissions** : Rôles liés aux permissions via table pivot
- **Projets centralisés** : Idées de projets référencent utilisateurs et organisations
- **Données de référence** : Tables support pour secteurs, catégories, localisation...

## 🎯 Système de Rôles et Permissions

### Rôles Définis

| Rôle | Slug | Description |
|------|------|-------------|
| **Responsable Projet** | `responsable_projet_dpaf` | CRUD sur fiches idées, score climatique |
| **Responsable Hiérarchique** | `responsable_hierarchique_ministere` | Validation et soumission des fiches |
| **DPAF** | `dpaf` | Analyse des fiches, notes conceptuelles |
| **Cellule Technique** | `dpaf_cellule_technique` | Évaluation des notes conceptuelles |
| **Analyste DGPD** | `analyste_dgpd` | AMC, impacts climatiques |
| **Comité Validation** | `comite_validation_ministeriel` | Validation aux différentes étapes |
| **DGPD** | `dgpd` | Supervision et validation finale |
| **Super Admin** | `super_admin` | Accès complet |

### Workflow de Validation
```
1. Responsable Projet → Crée fiche idée
2. Responsable Hiérarchique → Valide et soumet
3. DPAF → Analyse et note conceptuelle
4. Cellule Technique → Évalue la note
5. Analyste DGPD → AMC et impacts climatiques
6. Comité Ministériel → Validation par étapes
7. DGPD → Validation finale
```

## 🔧 Énumérations (Enums)

### Enums Disponibles

| Enum | Valeurs | Usage |
|------|---------|--------|
| `StatutIdee` | 22 statuts (brouillon → validation) | Statut des idées de projets |
| `PhasesIdee` | identification, evaluation_ex_tante, selection | Phase du projet |
| `SousPhaseIdee` | redaction, analyse_idee, etude_de_profil... | Sous-phase détaillée |
| `TypesProjet` | simple, complexe1, complex2 | Complexité du projet |
| `EnumTypeOrganisation` | etatique, partenaire, ong | Type d'organisation |
| `TypesCanevas` | 4 types de canevas | Templates de projets |
| `TypesTemplate` | 5 types de templates | Modèles de documents |

### Méthodes Utilitaires
```php
// Toutes les valeurs
StatutIdee::values(); // ['00_brouillon', '01_idee_de_projet', ...]

// Tous les noms
StatutIdee::names(); // ['BROUILLON', 'IDEE_DE_PROJET', ...]

// Paires clé-valeur
StatutIdee::options(); // ['BROUILLON' => '00_brouillon', ...]
```

## 🛣️ Routes API

### Endpoints Principaux

#### Ressources CRUD
```
GET|POST      /api/{resource}              # Liste/Créer
GET|PUT|DELETE /api/{resource}/{id}        # Voir/Modifier/Supprimer
```

#### Ressources Disponibles
- `arrondissements`, `categories-projet`, `cibles`, `communes`
- `composants-programme`, `departements`, `idees-projet` (principal)
- `odds`, `projets`, `roles`, `secteurs`, `types-intervention`
- `types-programme`, `villages`, `workflows`

#### Endpoints Spéciaux
```
GET /api/user                           # Utilisateur connecté
GET /api/enums/{enum-name}             # Options pour dropdowns
```

#### Enums Disponibles
- `/api/enums/statut-idee`
- `/api/enums/phases-idee`
- `/api/enums/sous-phase-idee`
- `/api/enums/types-projet`
- `/api/enums/types-canevas`
- `/api/enums/types-template`

## 👥 Comptes de Test

### Utilisateurs Prédéfinis

| Username | Password | Rôle |
|----------|----------|------|
| `superadmin` | `SuperAdmin123!` | Super Administrateur |
| `resp.projet.sante` | `ResponsableProjet123!` | Responsable Projet |
| `ministre.sante` | `ResponsableHier123!` | Responsable Hiérarchique |
| `dpaf.plan` | `DPAF123!` | DPAF |
| `analyste.dgpd.1` | `AnalysteDGPD123!` | Analyste DGPD |
| `comite.validation.plan` | `ComiteValidation123!` | Comité Validation |
| `dgpd.coordinateur` | `DGPD123!` | DGPD |

## 📊 Données de Test

### Seeders Exécutés
1. **OrganisationsSeeder** - 31 organisations avec hiérarchie
2. **PersonnesSeeder** - 34 personnes avec postes réalistes
3. **CategoriesUtilisateursSeeder** - 8 rôles + 29 permissions
4. **UpdateUsersWithCategoriesSeeder** - 13 utilisateurs de test

### Données Générées
- **31 organisations** (Ministères, DPAF, DGPD, DGB)
- **34 personnes** (Ministres, Directeurs, Coordinateurs, etc.)
- **8 rôles** avec permissions granulaires
- **29 permissions** couvrant tout le workflow
- **13 utilisateurs** avec mots de passe sécurisés

## 🏃‍♂️ Commandes Utiles

### Développement
```bash
# Réinitialiser la base de données
php artisan migrate:fresh --seed

# Créer un nouveau contrôleur
php artisan make:controller {Entity}Controller --api

# Créer un nouveau service
php artisan make:class Services/{Entity}Service

# Créer une nouvelle migration
php artisan make:migration create_{table}_table

# Créer un nouveau seeder
php artisan make:seeder {Entity}Seeder
```

### Tests
```bash
# Exécuter les tests
php artisan test

# Tests spécifiques
php artisan test --filter {TestClass}

# Avec couverture
php artisan test --coverage
```

## 🚀 Commandes de Développement Rapide

Le système inclut des générateurs personnalisés pour accélérer le développement en créant automatiquement les fichiers nécessaires avec la structure appropriée.

### Générateurs Disponibles

#### 🎯 **Générateur de Feature Complète**
```bash
php artisan make:feature {Entity}
```
Génère automatiquement :
- Model + Migration
- Controller avec Service injection
- Service + Interface
- Repository + Interface  
- Form Requests (Store/Update)
- API Resource
- Tests associés
- Routes API

**Exemple :**
```bash
php artisan make:feature Product
# Crée ProductController, ProductService, ProductRepository, etc.
```

#### 🎮 **Générateur de Contrôleur**
```bash
php artisan generate:controller {Name} [--model=] [--service=] [--force]
```
Génère :
- Contrôleur avec injection de service
- Service + Interface (si pas existant)
- Form Requests (Store/Update)
- Repository associé

**Options :**
- `--model` : Spécifier le nom du modèle (par défaut : singulier du nom)
- `--service` : Spécifier le nom du service (par défaut : {Name}Service)
- `--force` : Écraser les fichiers existants

**Exemple :**
```bash
php artisan generate:controller Category --model=Category --force
```

#### ⚙️ **Générateur de Service**
```bash
php artisan generate:service {Name} [--force]
```
Génère :
- Service class étendant BaseService
- Interface étendant AbstractServiceInterface
- Repository associé (si pas existant)
- Resource associée (si pas existante)
- Tests unitaires

**Exemple :**
```bash
php artisan generate:service User --force
```

#### 🗄️ **Générateur de Repository**
```bash
php artisan generate:repository {Name} [--force]
```
Génère :
- Repository class étendant BaseRepository
- Interface étendant BaseRepositoryInterface
- Tests unitaires

#### 📝 **Générateur de Form Request**
```bash
php artisan generate:form-request {Name} [--module=] [--force]
```
Génère :
- StoreRequest avec validation
- UpdateRequest avec validation
- Tests associés

#### 🎨 **Générateur de Resource**
```bash
php artisan generate:resource {Name} [--type=single] [--force]
```
Génère :
- Resource simple, collection ou externe
- Transformation des données automatique

**Types disponibles :**
- `single` : Resource individuelle (défaut)
- `collection` : Resource de collection
- `external` : Resource pour API externe

#### 🗃️ **Générateur de Model**
```bash
php artisan generate:model {Name} [--force]
```
Génère :
- Model avec traits de base
- Migration associée
- Factory pour les tests

#### 🧪 **Générateur de Test**
```bash
php artisan generate:test {Name} [--type=feature] [--force]
```
Génère :
- Tests Feature ou Unit
- Structure de test appropriée

#### 🛣️ **Générateur de Route API**
```bash
php artisan generate:api-route {Name} [--force]
```
Ajoute automatiquement les routes API au fichier routes/api.php

### Workflow de Développement Rapide

#### Créer une nouvelle entité complète :
```bash
# 1. Génération complète d'une feature
php artisan make:feature Order

# 2. Ou génération étape par étape
php artisan generate:controller Order
php artisan generate:service Order  
php artisan generate:repository Order
php artisan generate:form-request Order
php artisan generate:resource Order --type=single
php artisan generate:api-route Order

# 3. Générer les tests
php artisan generate:test Order --type=feature
php artisan generate:test OrderService --type=unit
```

#### Ajouter une fonctionnalité à une entité existante :
```bash
# Ajouter une méthode spécifique au repository
php artisan generate:repository Order --force

# Régénérer le service avec nouvelles méthodes
php artisan generate:service Order --force

# Mettre à jour les tests
php artisan generate:test Order --force
```

### Avantages des Générateurs

#### ✅ **Gain de Temps**
- Création automatique de tous les fichiers nécessaires
- Structure cohérente respectée
- Injection de dépendances configurée

#### ✅ **Consistance**
- Patterns architecturaux respectés
- Conventions de nommage uniformes
- Code template standardisé

#### ✅ **Moins d'Erreurs**
- Namespace automatiquement configurés
- Relations correctement établies
- Imports générés automatiquement

#### ✅ **Productivity**
- Focus sur la logique métier
- Moins de code boilerplate
- Développement accéléré

### Stubs Personnalisés

Les générateurs utilisent des templates (stubs) personnalisés situés dans `app/stubs/` :
```
app/stubs/
├── controller.stub          # Template contrôleur
├── service.stub            # Template service
├── i_service.stub          # Template interface service
├── repository.stub         # Template repository
├── i_repository.stub       # Template interface repository
├── form-request.stub       # Template form request
├── update-form-request.stub # Template update request
├── resource.stub           # Template resource
├── resource-collection.stub # Template collection
├── resource-external.stub   # Template resource externe
├── model.stub              # Template model
├── migration.stub          # Template migration
├── dto.stub                # Template DTO
└── tests/                  # Templates de tests
    ├── controller.test.stub
    ├── service.test.stub
    ├── repository.test.stub
    └── feature-test.stub
```

Ces templates peuvent être personnalisés selon les besoins du projet.

## 📁 Structure des Dossiers

```
app/
├── Console/Commands/Generators/     # Générateurs de code
├── Enums/                          # Énumérations métier
├── Http/
│   ├── Controllers/                # Contrôleurs API
│   ├── Requests/                   # Validation des requêtes
│   └── Resources/                  # Transformation des données
├── Models/                         # Modèles Eloquent
├── Repositories/                   # Couche d'accès aux données
└── Services/                       # Logique métier

database/
├── migrations/                     # Schémas de base de données
└── seeders/                        # Données de test

routes/
└── api.php                         # Routes API
```

## 🔐 Sécurité

### Authentification
- **Laravel Sanctum** pour l'authentification API
- **Mot de passe hashé** avec Bcrypt
- **Tokens d'accès** avec expiration

### Autorisation
- **29 permissions granulaires** par action
- **Middleware de permissions** sur les routes sensibles
- **Validation des rôles** avant chaque action

### Validation
- **Form Requests** pour valider les données d'entrée
- **Règles de validation** personnalisées
- **Nettoyage des données** avant traitement

## 🧪 Tests

### Structure des Tests
```
tests/
├── Feature/                        # Tests d'intégration
│   ├── Http/Controllers/          # Tests des contrôleurs
│   └── Auth/                      # Tests d'authentification
├── Unit/                          # Tests unitaires
│   ├── Services/                  # Tests des services
│   ├── Repositories/              # Tests des repositories
│   └── Models/                    # Tests des modèles
└── Integration/                   # Tests d'intégration
    └── Migrations/                # Tests des migrations
```

### Types de Tests
- **Unit Tests** - Logique métier isolée
- **Feature Tests** - Endpoints API complets
- **Integration Tests** - Interaction entre composants
- **Database Tests** - Migrations et seeders

## 📈 Performance

### Optimisations Implémentées
- **Eager Loading** pour éviter N+1 queries
- **Pagination** sur les listes importantes
- **Cache** pour les enums et données statiques
- **Indexes** sur les champs de recherche fréquents

### Monitoring
- **Logs Laravel** pour le debug
- **Query Logging** en développement
- **Error Tracking** pour la production

## 🔄 Workflow de Développement

### Git Flow
```bash
# Nouvelle fonctionnalité
git checkout -b feature/nouvelle-fonctionnalite
git add .
git commit -m "feat: ajouter nouvelle fonctionnalité"
git push origin feature/nouvelle-fonctionnalite

# Correction de bug
git checkout -b hotfix/correction-bug
git add .
git commit -m "fix: corriger le bug X"
git push origin hotfix/correction-bug
```

### Standards de Code
- **PSR-12** pour le style PHP
- **Conventions Laravel** pour la structure
- **Noms explicites** pour les variables et méthodes
- **Commentaires** pour la logique complexe

## 📝 Documentation

### Ressources Utiles
- **RAPPORT_STRUCTURE_CODE.md** - Documentation technique détaillée
- **API Documentation** - Via Swagger/OpenAPI (à implémenter)
- **Postman Collection** - Tests d'API (à créer)

### Maintenance
- **Migrations** pour les changements de schéma
- **Seeders** pour les données de test
- **Versionning** des API endpoints
- **Changelog** pour les modifications

## 🤝 Contribution

### Processus de Contribution
1. Fork le repository
2. Créer une branche feature
3. Implémenter la fonctionnalité
4. Ajouter des tests
5. Créer une Pull Request

### Standards de Qualité
- **Tests unitaires** obligatoires
- **Documentation** des nouvelles fonctionnalités
- **Respect des patterns** existants
- **Code Review** avant merge