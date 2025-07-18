# Rapport de Structure du Code - Système de Gestion des Idées de Projets (GDIZ)

## Vue d'ensemble
Ce rapport présente la structure complète du système de gestion des idées de projets développé en Laravel, incluant les schémas de base de données, l'architecture du code, les relations entre les composants et les scénarios de test par couche.

## Table des Matières
1. [Architecture Générale](#architecture-générale)
2. [Schémas de Base de Données](#schémas-de-base-de-données)
3. [Structure des Contrôleurs](#structure-des-contrôleurs)
4. [Services et Repositories](#services-et-repositories)
5. [Système d'Authentification et d'Autorisation](#système-dauthentification-et-dautorisation)
6. [Énumérations (Enums)](#énumérations-enums)
7. [Routes API](#routes-api)
8. [Seeders](#seeders)
9. [Scénarios de Test par Couche](#scénarios-de-test-par-couche)

---

## Architecture Générale

### Pattern Architectural
Le système suit le pattern **Service-Repository** avec une architecture en couches :

```
┌─────────────────────────────────────────────────────────────┐
│                    COUCHE PRÉSENTATION                     │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │   Controllers   │  │   Resources     │  │   Requests      │ │
│  │   (HTTP Logic)  │  │   (Transform)   │  │   (Validation)  │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                                │
┌─────────────────────────────────────────────────────────────┐
│                    COUCHE MÉTIER                           │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │    Services     │  │   Contracts     │  │      Enums      │ │
│  │ (Business Logic)│  │  (Interfaces)   │  │   (Constants)   │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                                │
┌─────────────────────────────────────────────────────────────┐
│                    COUCHE DONNÉES                          │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │  Repositories   │  │     Models      │  │   Migrations    │ │
│  │ (Data Access)   │  │   (Eloquent)    │  │   (Schema)      │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

---

## Schémas de Base de Données

### 1. Table `users`
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    provider VARCHAR(255) DEFAULT 'keycloack',
    provider_user_id VARCHAR(255),
    username VARCHAR(255) UNIQUE,
    email VARCHAR(255) UNIQUE,
    status ENUM('actif', 'suspendu', 'invité') DEFAULT 'actif',
    is_email_verified BOOLEAN DEFAULT false,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255),
    personneId BIGINT UNSIGNED,
    roleId BIGINT UNSIGNED,
    last_connection TIMESTAMP NULL,
    ip_address VARCHAR(255) NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (personneId) REFERENCES personnes(id),
    FOREIGN KEY (roleId) REFERENCES roles(id)
);
```

### 2. Table `personnes`
```sql
CREATE TABLE personnes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255),
    prenom VARCHAR(255),
    poste VARCHAR(255) NULL,
    organismeId BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (organismeId) REFERENCES organisations(id)
);
```

### 3. Table `organisations`
```sql
CREATE TABLE organisations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    nom TEXT(255) UNIQUE,
    slug VARCHAR(255) UNIQUE,
    description LONGTEXT NULL,
    type ENUM('etatique', 'partenaire', 'ong') DEFAULT 'etatique',
    parentId BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (parentId) REFERENCES organisations(id)
);
```

**Relations :**
- Auto-référence hiérarchique (parentId → organisations.id)
- One-to-Many vers personnes
- Types définis par EnumTypeOrganisation

### 4. Table `roles`
```sql
CREATE TABLE roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) UNIQUE,
    slug VARCHAR(255) UNIQUE,
    description MEDIUMTEXT NULL,
    roleable_type VARCHAR(255) NULL,
    roleable_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

### 5. Table `permissions`
```sql
CREATE TABLE permissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) UNIQUE,
    slug VARCHAR(255) UNIQUE,
    description MEDIUMTEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

### 6. Table `role_permissions`
```sql
CREATE TABLE role_permissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    role_id BIGINT UNSIGNED,
    permission_id BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id)
);
```

### 7. Table `idees_projet`
```sql
CREATE TABLE idees_projet (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    secteur_id BIGINT UNSIGNED,
    ministere_id BIGINT UNSIGNED,
    categorie_id BIGINT UNSIGNED,
    responsable_id BIGINT UNSIGNED,
    demandeur_id BIGINT UNSIGNED,
    
    -- Identifiants uniques
    identifiant_bip VARCHAR(255) UNIQUE NULL,
    identifiant_sigfp VARCHAR(255) UNIQUE NULL,
    
    -- Champs de statut
    est_coherent BOOLEAN DEFAULT false,
    statut ENUM(...) DEFAULT '00_brouillon',
    phase ENUM(...) DEFAULT 'identification',
    sous_phase ENUM(...) DEFAULT 'redaction',
    decision JSON NULL,
    
    -- Informations de base
    titre_projet VARCHAR(255),
    sigle VARCHAR(255),
    type_responsable VARCHAR(255),
    demandeur_type VARCHAR(255),
    type_projet ENUM(...) DEFAULT 'simple',
    duree VARCHAR(255) NULL,
    
    -- Champs de description (LONGTEXT)
    origine LONGTEXT NULL,
    fondement LONGTEXT NULL,
    situation_actuelle LONGTEXT NULL,
    situation_desiree LONGTEXT NULL,
    contraintes LONGTEXT NULL,
    description_projet LONGTEXT NULL,
    -- ... autres champs LONGTEXT
    
    -- Champs numériques
    score_climatique DECIMAL(8,2) DEFAULT 0.0,
    score_amc DECIMAL(8,2) DEFAULT 0.0,
    cout_dollar_americain DECIMAL(15,2) NULL,
    cout_euro DECIMAL(15,2) NULL,
    cout_dollar_canadien DECIMAL(15,2) NULL,
    
    -- Champs de date
    date_debut_etude TIMESTAMP NULL,
    date_fin_etude TIMESTAMP NULL,
    
    -- Champs JSON
    cout_estimatif_projet JSON NULL,
    fiche_idee JSON NOT NULL,
    parties_prenantes JSON NULL,
    objectifs_specifiques JSON NULL,
    resultats_attendus JSON NULL,
    body JSON,
    
    -- Champ de suppression logique
    isdeleted BOOLEAN DEFAULT false,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (secteur_id) REFERENCES secteurs(id),
    FOREIGN KEY (ministere_id) REFERENCES ministeres(id),
    FOREIGN KEY (categorie_id) REFERENCES categories_projet(id),
    FOREIGN KEY (responsable_id) REFERENCES users(id),
    FOREIGN KEY (demandeur_id) REFERENCES users(id)
);
```

---

## Structure des Contrôleurs

### Pattern de Contrôleur Standard
Tous les contrôleurs suivent le même pattern RESTful :

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\{Entity}\Store{Entity}Request;
use App\FormRequest\{Entity}\Update{Entity}Request;
use App\Services\Contracts\{Entity}ServiceInterface;
use Illuminate\Http\JsonResponse;

class {Entity}Controller extends Controller
{
    protected {Entity}ServiceInterface $service;

    public function __construct({Entity}ServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        return $this->service->all();
    }

    public function show($id): JsonResponse
    {
        return $this->service->find($id);
    }

    public function store(Store{Entity}Request $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(Update{Entity}Request $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}
```

### Contrôleurs Disponibles
- **ArrondissementController** - Gestion des arrondissements
- **CanevasController** - Gestion des canevas de projets
- **CategorieCanevasController** - Gestion des catégories de canevas
- **CategorieProjetController** - Gestion des catégories de projets
- **CibleController** - Gestion des cibles
- **CommuneController** - Gestion des communes
- **ComposantProgrammeController** - Gestion des composants de programme
- **DepartementController** - Gestion des départements
- **IdeeProjetController** - Gestion des idées de projets (PRINCIPAL)
- **InstitutionController** - Gestion des institutions
- **MinistereController** - Gestion des ministères
- **NatureFinancementController** - Gestion des natures de financement
- **ODDController** - Gestion des Objectifs de Développement Durable
- **ProjetController** - Gestion des projets
- **RoleController** - Gestion des rôles
- **SecteurController** - Gestion des secteurs
- **SourceFinancementController** - Gestion des sources de financement
- **TypeFinancementController** - Gestion des types de financement
- **TypeInterventionController** - Gestion des types d'intervention
- **TypeProgrammeController** - Gestion des types de programme
- **VillageController** - Gestion des villages
- **WorkflowController** - Gestion des workflows

---

## Services et Repositories

### Architecture Service-Repository

```php
// Interface de Service
interface {Entity}ServiceInterface extends AbstractServiceInterface
{
    // Méthodes métier spécifiques
}

// Implémentation du Service
class {Entity}Service extends BaseService implements {Entity}ServiceInterface
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
    
    // Méthodes héritées du BaseService :
    // - all(): JsonResponse
    // - find($id): JsonResponse
    // - create(array $data): JsonResponse
    // - update($id, array $data): JsonResponse
    // - delete($id): JsonResponse
}

// Repository
class {Entity}Repository extends BaseRepository implements {Entity}RepositoryInterface
{
    // Méthodes d'accès aux données
}
```

---

## Système d'Authentification et d'Autorisation

### Rôles Définis
1. **Responsable Projet (DPAF/Ministère Sectoriel)**
   - Permissions CRUD sur les fiches idées de projet
   - Obtention et transmission du score climatique
   - Soumission des rapports de faisabilité

2. **Responsable Hiérarchique (Ministère)**
   - Permissions CRU (sans Delete)
   - Validation et soumission des fiches d'idées

3. **DPAF**
   - Analyse des fiches d'idées
   - Rédaction des notes conceptuelles
   - Soumission des TDRs et rapports

4. **DPAF/Cellule Technique/Service Etude**
   - Évaluation des notes conceptuelles

5. **Analyste DGPD**
   - Application de l'AMC
   - Évaluation des impacts climatiques
   - Validation des étapes d'analyse

6. **Comité de Validation Ministériel**
   - Validation aux différentes étapes
   - Appréciation des TDRs

7. **DGPD**
   - Supervision du processus global
   - Validation finale des projets

8. **Super Administrateur**
   - Accès complet au système

### Permissions Granulaires
- **29 permissions spécifiques** couvrant toutes les actions du workflow
- **Associations automatiques** rôle-permission
- **Système de droits** basé sur les phases du projet

---

## Énumérations (Enums)

### Enums Disponibles
1. **StatutIdee** - 22 statuts du brouillon à la validation
2. **PhasesIdee** - 3 phases (identification, evaluation_ex_tante, selection)
3. **SousPhaseIdee** - 5 sous-phases (redaction, analyse_idee, etc.)
4. **TypesProjet** - 3 types (simple, complexe1, complex2)
5. **EnumTypeInstitution** - 3 types (etatique, partenaire, ong)
6. **TypesCanevas** - 4 types de canevas
7. **TypesTemplate** - 5 types de templates

### Méthodes Utilitaires
Chaque enum dispose de méthodes statiques :
```php
// Obtenir toutes les valeurs
StatutIdee::values(): array

// Obtenir tous les noms
StatutIdee::names(): array

// Obtenir les paires clé-valeur
StatutIdee::options(): array
```

---

## Routes API

### Structure des Routes
```php
// Routes de ressources (CRUD complet)
Route::apiResource('{resource}', {Entity}Controller::class);

// Routes d'énums (pour les dropdowns frontend)
Route::prefix('enums')->group(function () {
    Route::get('/{enum-name}', function () {
        return response()->json(\App\Enums\{EnumName}::options());
    });
});

// Routes d'authentification
Route::prefix('auth')->group(function () {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'register']);
    Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
});
```

### Endpoints Disponibles
- **22 ressources API** avec CRUD complet
- **7 endpoints d'énums** pour les options frontend
- **3 endpoints d'authentification**

---

## Seeders

### Ordre d'Exécution
```php
1. OrganisationsSeeder::class     // 31 organisations typées
2. PersonnesSeeder::class         // 34 personnes réalistes
3. CategoriesUtilisateursSeeder::class  // 8 rôles + 29 permissions
4. UpdateUsersWithCategoriesSeeder::class  // 13 utilisateurs de test
```

### Données de Test
- **31 organisations** avec hiérarchie complète
- **34 personnes** avec postes réalistes
- **8 rôles** correspondant au workflow
- **29 permissions** granulaires
- **13 utilisateurs** de test avec mots de passe sécurisés

---

## Workflow du Système

### Flux de Validation des Idées de Projets
```
1. Responsable Projet (DPAF) → Crée fiche idée
2. Responsable Hiérarchique → Valide et soumet
3. DPAF → Analyse et rédige note conceptuelle
4. Cellule Technique → Évalue la note
5. Analyste DGPD → Applique AMC et évalue impacts
6. Comité Ministériel → Valide aux différentes étapes
7. DGPD → Supervise et valide le processus final
```

### États des Projets
- **22 statuts** couvrant tout le cycle de vie
- **3 phases** principales
- **5 sous-phases** détaillées
- **Transitions contrôlées** par les permissions

---

## Recommandations

### Bonnes Pratiques Implémentées
✅ **Séparation des responsabilités** (Service-Repository)
✅ **Validation centralisée** (Form Requests)
✅ **Transformation des données** (API Resources)
✅ **Gestion des permissions** granulaire
✅ **Énumérations typées** avec méthodes utilitaires
✅ **Seeders réalistes** pour les tests
✅ **Structure RESTful** consistante

### Améliorations Possibles
- **Tests unitaires** et d'intégration
- **Documentation API** (Swagger/OpenAPI)
- **Middleware de cache** pour les énums
- **Logs d'audit** pour les actions critiques
- **Notifications** pour les changements de statut

---

## Conclusion

Le système présente une architecture robuste et scalable, respectant les bonnes pratiques Laravel et les patterns de développement modernes. La structure modulaire permet une maintenance facilitée et une évolution progressive du système.

La gestion des permissions granulaires et le workflow bien défini assurent un contrôle précis des actions utilisateur, essentiel pour un système de gestion de projets gouvernementaux.