# Guide de mise à jour du formData enrichi

Cette commande permet de mettre à jour le `ficheIdee["formData"]` de toutes les IdeeProjet existantes avec les données enrichies (objets `{id, nom}` pour les relations).

## 🎯 Qu'est-ce qui est enrichi ?

La commande met à jour **uniquement** le `formData` dans `ficheIdee`. Le `form` existant n'est **jamais écrasé** (seulement créé s'il est vide).

**Important** : Si une IdeeProjet a un **Projet lié** (relation `projet`), la commande mettra également à jour automatiquement le `formData` du Projet.

Les champs relationnels dans `formData` sont enrichis pour contenir des objets complets au lieu de simples IDs :

### Financements (hiérarchie à 3 niveaux)
- `types_financement` → Financements où `type='type'`
- `natures_financement` → Financements où `type='nature'`
- `sources_financement` → Financements où `type='source'`

### Secteurs (hiérarchie à 3 niveaux)
- `grand_secteur` → Secteurs où `type='grand-secteur'`
- `secteur` → Secteurs où `type='secteur'`
- `secteurId` → Secteur où `type='sous-secteur'`

### Autres relations
- `cibles` → Objets Cible
- `odds` → Objets ODD
- `categorieId` → Objet CategorieProjet
- `departements`, `communes`, `arrondissements`, `villages` → Via lieuxIntervention
- Et toutes les autres relations (PND, PAG, etc.)

## 📋 Utilisation

### 1. Mode dry-run (recommandé en premier)

Testez la commande sans modifier la base de données :

```bash
php artisan idees:update-formdata --dry-run
```

### 2. Mise à jour de toutes les idées

```bash
php artisan idees:update-formdata
```

### 3. Limiter le nombre d'idées traitées

Utile pour tester sur un échantillon :

```bash
php artisan idees:update-formdata --limit=10
```

### 4. Mettre à jour des IDs spécifiques

```bash
php artisan idees:update-formdata --ids=1,5,10,25
```

### 5. Forcer la mise à jour même si ficheIdee existe

Par défaut, la commande ignore les idées qui ont déjà des données enrichies. Pour forcer :

```bash
php artisan idees:update-formdata --force
```

### 6. Combiner les options

```bash
# Dry-run avec limite
php artisan idees:update-formdata --dry-run --limit=5

# Forcer la mise à jour de 20 idées
php artisan idees:update-formdata --force --limit=20

# Mettre à jour des IDs spécifiques en dry-run
php artisan idees:update-formdata --ids=1,2,3 --dry-run
```

## 📊 Exemple de sortie

```
📊 Total d'idées de projet à traiter: 150

 150/150 [████████████████████████████] 100% - Terminé !

✅ Résumé de l'opération:
+---------------------------+--------+
| Statut                    | Nombre |
+---------------------------+--------+
| ✅ Mises à jour réussies  | 142    |
| ⏭️  Ignorées (déjà enrichies) | 5      |
| ❌ Erreurs                | 3      |
+---------------------------+--------+

✨ Mise à jour terminée avec succès !
```

## 🔍 Détection automatique des données déjà enrichies

La commande détecte automatiquement si le `formData` contient déjà des objets enrichis (`{id, nom}`) et ignore ces idées pour éviter les doublons.

Pour forcer la mise à jour quand même, utilisez l'option `--force`.

## 🛡️ Préservation du form existant

**Important** : La commande préserve TOUJOURS le `ficheIdee["form"]` existant. Elle ne le crée que s'il est vide ou inexistant.

```php
// Comportement de la commande
if (empty($ficheIdee["form"])) {
    // Créer le form SEULEMENT s'il n'existe pas
    $ficheIdee["form"] = ...;
}

// Toujours mettre à jour formData
$ficheIdee["formData"] = $idee->getFormDataWithRelations();
```

Cela garantit que :
- ✅ Aucune donnée `form` existante n'est perdue
- ✅ Seul le `formData` est enrichi
- ✅ La structure `form` personnalisée est préservée

## 🔗 Mise à jour automatique des Projets liés

Lorsqu'une IdeeProjet a un Projet associé (relation `ideeProjetId`), la commande met automatiquement à jour **aussi** le `formData` du Projet.

### Fonctionnement

```php
// Pour chaque IdeeProjet mise à jour
if ($idee->projet) {
    // Mise à jour automatique du Projet lié
    $projet->ficheIdee["formData"] = $projet->getFormDataWithRelations();
}
```

### Avantages

- ✅ Synchronisation automatique entre IdeeProjet et Projet
- ✅ Pas besoin de commande séparée pour les Projets
- ✅ Gestion d'erreur indépendante (n'affecte pas l'IdeeProjet si échec)
- ✅ Performance optimisée avec eager loading

### Gestion des erreurs

Si la mise à jour du Projet échoue :
- ⚠️ Un avertissement est affiché
- ✅ L'IdeeProjet reste mise à jour
- ✅ Le traitement continue pour les autres IdeeProjet

## ⚠️ Recommandations

### Avant de lancer en production

1. **Backup de la base de données** :
```bash
php artisan backup:run
# Ou votre commande de backup habituelle
```

2. **Tester avec dry-run** :
```bash
php artisan idees:update-formdata --dry-run --limit=10
```

3. **Tester sur un échantillon** :
```bash
php artisan idees:update-formdata --limit=10
```

4. **Vérifier les résultats** :
   - Vérifier quelques IdeeProjet dans la base de données
   - Tester l'affichage dans le frontend
   - Vérifier les logs d'erreurs

5. **Lancer sur toutes les données** :
```bash
php artisan idees:update-formdata
```

### Performance

Pour de grandes quantités de données (> 1000 idées) :

```bash
# Traiter par lots
php artisan idees:update-formdata --limit=500
# Puis relancer jusqu'à ce que tout soit traité
```

## 🐛 En cas d'erreur

Si des erreurs surviennent, la commande affiche les détails :

```
❌ Détails des erreurs (3):
+----+------------------------------------------+--------------------------------------------------------------+
| ID | Titre                                    | Erreur                                                       |
+----+------------------------------------------+--------------------------------------------------------------+
| 12 | Construction d'un hôpital               | SQLSTATE[23000]: Integrity constraint violation...          |
| 45 | Réhabilitation des routes              | Call to undefined method...                                  |
| 78 | Projet d'électrification               | Undefined array key "champs"                                 |
+----+------------------------------------------+--------------------------------------------------------------+
```

Les transactions sont utilisées, donc chaque erreur ne concerne qu'une seule IdeeProjet. Les autres continuent d'être traitées.

## 🔄 Relancer après correction

Si des erreurs ont été corrigées, vous pouvez relancer la commande sur les IDs en erreur :

```bash
php artisan idees:update-formdata --ids=12,45,78 --force
```

## ✅ Vérification après mise à jour

Pour vérifier qu'une IdeeProjet a bien été mise à jour, vous pouvez utiliser tinker :

```bash
php artisan tinker
```

```php
$idee = \App\Models\IdeeProjet::find(1);
$formData = $idee->ficheIdee['formData'];

// Chercher un champ relationnel
$cibles = collect($formData)->firstWhere('attribut', 'cibles');
dd($cibles['value']); // Devrait afficher un array d'objets {id, nom}
```

## 📝 Structure avant/après

### Avant
```json
{
  "attribut": "cibles",
  "value": "1,2,3"
}
```

### Après
```json
{
  "attribut": "cibles",
  "value": [
    {"id": "hashed_1", "nom": "Femmes"},
    {"id": "hashed_2", "nom": "Jeunes"},
    {"id": "hashed_3", "nom": "Enfants"}
  ]
}
```

## 🔧 Options complètes

| Option | Description | Exemple |
|--------|-------------|---------|
| `--limit` | Limiter le nombre d'idées à traiter | `--limit=50` |
| `--ids` | IDs spécifiques (séparés par virgules) | `--ids=1,5,10` |
| `--dry-run` | Mode test sans modification | `--dry-run` |
| `--force` | Forcer la mise à jour même si déjà enrichi | `--force` |

## 💡 Conseils

- Lancez toujours avec `--dry-run` d'abord
- Pour de grosses bases, utilisez `--limit` pour traiter par lots
- Surveillez les logs en cas d'erreurs
- Faites un backup avant de lancer en production
- Testez le frontend après mise à jour

## 🚀 Automatisation (optionnel)

Pour ajouter cette commande à votre déploiement :

```bash
# Dans votre script de déploiement
php artisan idees:update-formdata --force
```

Ou l'ajouter au scheduler dans `app/Console/Kernel.php` (si besoin de mises à jour régulières) :

```php
protected function schedule(Schedule $schedule)
{
    // Mettre à jour le formData chaque nuit
    $schedule->command('idees:update-formdata')
        ->daily()
        ->at('02:00');
}
```
