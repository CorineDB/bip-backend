# Guide d'utilisation - Export des fichiers IdeeProjet

Cette commande permet d'exporter automatiquement les fichiers (fiche, pertinence, climatique, AMC) pour les idées de projet existantes en dispatchant les jobs appropriés.

**Important**: Par défaut, seules les IdeeProjet qui ont un **Projet associé** et dont le **statut est '03a_NoteConceptuel'** sont exportées.

## Commande

```bash
php artisan idees:export-files [options]
```

## Options disponibles

### `--limit=N`
Limiter le nombre d'idées de projet à traiter.

**Exemple:**
```bash
php artisan idees:export-files --limit=10
```

### `--ids=`
Spécifier des IDs spécifiques séparés par des virgules.

**Exemple:**
```bash
php artisan idees:export-files --ids=120,121,122
```

### `--statut=`
Filtrer par statut (ex: 01_Analyse, 02_Validation, 03a_NoteConceptuel).

**Par défaut**: Si cette option n'est pas spécifiée, seules les IdeeProjet avec le statut '03a_NoteConceptuel' sont exportées.

**Exemple:**
```bash
# Exporter uniquement les IdeeProjet en statut validation
php artisan idees:export-files --statut=02_Validation

# Sans option --statut, seules les IdeeProjet avec statut '03a_NoteConceptuel' sont exportées
php artisan idees:export-files --limit=10
```

### `--dry-run`
Mode test sans dispatcher les jobs réellement. Utile pour voir ce qui sera fait.

**Exemple:**
```bash
php artisan idees:export-files --dry-run --limit=5
```

### `--types=`
Spécifier les types d'exports à effectuer. Par défaut: tous (fiche, pertinence, climatique, amc).

**Valeurs possibles:**
- `fiche` : Export PDF de la fiche idée projet
- `pertinence` : Export Excel de l'évaluation de pertinence
- `climatique` : Export Excel de l'évaluation climatique
- `amc` : Export Excel de l'évaluation AMC

**Exemple:**
```bash
# Exporter uniquement les fiches et pertinence
php artisan idees:export-files --types=fiche --types=pertinence

# Exporter uniquement les évaluations climatiques
php artisan idees:export-files --types=climatique
```

### `--force`
Forcer l'export même si les fichiers existent déjà (option future).

**Exemple:**
```bash
php artisan idees:export-files --force
```

## Exemples d'utilisation

### 1. Tester sur 5 idées sans dispatcher les jobs

```bash
php artisan idees:export-files --dry-run --limit=5
```

**Résultat:**
- Affiche ce qui sera fait
- Aucun job dispatché
- Montre les statistiques

### 2. Exporter tous les fichiers pour une idée spécifique

```bash
php artisan idees:export-files --ids=120
```

**Résultat:**
- Export de la fiche PDF
- Export de l'évaluation de pertinence (si elle existe et est terminée)
- Export de l'évaluation climatique (si elle existe et est terminée)
- Export de l'évaluation AMC (si elle existe et est terminée)

### 3. Exporter uniquement les fiches pour les 20 premières idées

```bash
php artisan idees:export-files --limit=20 --types=fiche
```

**Résultat:**
- Dispatch de 20 jobs ExportProjectPdfJob
- Pas d'export des évaluations

### 4. Exporter les évaluations pour les idées en validation

```bash
php artisan idees:export-files --statut=02_Validation --types=pertinence --types=climatique
```

**Résultat:**
- Exporte uniquement pertinence et climatique
- Pour toutes les idées au statut "02_Validation"

### 5. Exporter toutes les évaluations pour toutes les idées avec statut '03a_NoteConceptuel'

```bash
php artisan idees:export-files
```

**Résultat:**
- Exporte fiche + pertinence + climatique + AMC
- Pour TOUTES les IdeeProjet qui ont un Projet associé et statut '03a_NoteConceptuel'
- ⚠️ Attention: peut générer beaucoup de jobs!

### 6. Exporter pour plusieurs IDs spécifiques

```bash
php artisan idees:export-files --ids=120,121,122,123 --types=pertinence --types=climatique
```

**Résultat:**
- Exporte pertinence et climatique pour les IDs 120, 121, 122, 123

## Surveillance des exports

Une fois les jobs dispatchés, vous pouvez surveiller leur progression:

```bash
# Voir tous les logs d'export en temps réel
tail -f storage/logs/laravel.log | grep "Export"

# Voir uniquement les succès
tail -f storage/logs/laravel.log | grep "✅.*Export"

# Voir uniquement les erreurs
tail -f storage/logs/laravel.log | grep "❌.*Export"

# Surveiller le queue worker
php artisan queue:work
```

## Workflow recommandé

### Phase de test
1. **Tester sur 1 idée:**
   ```bash
   php artisan idees:export-files --dry-run --limit=1
   ```

2. **Vérifier que tout est OK, puis exécuter:**
   ```bash
   php artisan idees:export-files --limit=1
   ```

3. **Surveiller les logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep "Export"
   ```

### Phase de production

1. **Exporter par petits lots:**
   ```bash
   # Lot 1: 50 premières idées
   php artisan idees:export-files --limit=50

   # Attendre que les jobs finissent
   # Puis lot suivant...
   ```

2. **Ou exporter par statut:**
   ```bash
   # D'abord les idées en validation
   php artisan idees:export-files --statut=02_Validation

   # Puis les autres statuts
   php artisan idees:export-files --statut=01_Analyse
   ```

## Notes importantes

### Filtrage par défaut
- **Projet associé**: Seules les IdeeProjet qui ont un Projet associé sont exportées
- **Statut**: Par défaut, seules les IdeeProjet avec statut '03a_NoteConceptuel' sont exportées (sauf si --statut est spécifié)
- Utilisez --statut pour filtrer par un autre statut

### Évaluations exportées uniquement si terminées
- La commande ne dispatche les jobs d'export d'évaluations que si `statut = 1` (terminée)
- Les évaluations non terminées sont ignorées

### Jobs asynchrones
- Les exports sont dispatchés dans la queue
- Ils s'exécutent de manière asynchrone via le queue worker
- Un queue worker doit être actif: `php artisan queue:work`

### Logs détaillés
- Chaque job dispatché est loggé avec:
  - 📄 Fiche idée projet
  - 📊 Évaluation pertinence
  - 🌍 Évaluation climatique
  - 📈 Évaluation AMC

### Fichiers générés
Les fichiers exportés sont stockés dans:
- **Chemin physique:** `storage/app/projets/{hash_identifiant_bip}/identification/`
- **Base de données:** Table `fichiers` avec relation polymorphique vers `IdeeProjet`

## Dépannage

### "Aucune idée de projet trouvée"
- Vérifiez vos critères de filtrage (statut, IDs)
- Vérifiez que des idées existent en base de données

### "Permission denied"
- Vérifiez les permissions sur `storage/`
- Le queue worker doit avoir les bonnes permissions

### Jobs qui échouent
- Vérifiez les logs: `storage/logs/laravel.log`
- Vérifiez que les templates Excel existent:
  - `canevas/O-3_Évaluation de la pertinence_18-06-2025-rev MN.xlsx`
  - `canevas/C-1a_Evaluation_climatique.xlsx`

## Exemple de session complète

```bash
# 1. Tester en dry-run
php artisan idees:export-files --dry-run --limit=5

# 2. Vérifier que c'est OK
# Output:
# 📊 Nombre d'idées de projet trouvées: 5
# Voulez-vous dispatcher les jobs d'export pour 5 idée(s) de projet? (yes/no) [yes]:

# 3. Exécuter réellement
php artisan idees:export-files --limit=5

# 4. Dans un autre terminal, surveiller
tail -f storage/logs/laravel.log | grep "Export"

# 5. Vérifier le queue worker
php artisan queue:work

# Output du worker:
# ✅ [ExportEvaluationJob] Export évaluation réussi
# ✅ [ExportEvaluationJob] Export évaluation réussi
# ...
```

## Support

Pour toute question ou problème, consultez les logs ou contactez l'équipe de développement.
