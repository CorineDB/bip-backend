# 📄 Système d'Exportation des Fiches de Projet

## 🎯 Vue d'ensemble

Ce système complet permet d'exporter les fiches de projet au format PDF et Word avec table des matières automatique, conforme aux standards gouvernementaux du Bénin.

## 📦 Installation

### 1. Installer les dépendances

```bash
composer require barryvdh/laravel-dompdf
composer require phpoffice/phpword
```

### 2. Publier les configurations

```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

### 3. Configurer DomPDF

Dans `config/dompdf.php`, activez le support PHP :

```php
'enable_php' => true,
'enable_javascript' => true,
'enable_html5_parser' => true,
```

### 4. Enregistrer la commande

Dans `app/Console/Kernel.php` :

```php
protected $commands = [
    \App\Console\Commands\ExportProjectCommand::class,
];
```

### 5. Créer les répertoires nécessaires

```bash
mkdir -p storage/app/exports
mkdir -p storage/app/temp
mkdir -p resources/views/exports
chmod 755 storage/app/exports
chmod 755 storage/app/temp
```

### 6. Copier les fichiers

- Copier `ProjectExportService.php` dans `app/Services/`
- Copier `ExportProjectCommand.php` dans `app/Console/Commands/`
- Copier `ExportProjectJob.php` dans `app/Jobs/`
- Copier `project-idea-with-toc.blade.php` dans `resources/views/exports/`
- Copier `export-projects.sh` à la racine du projet et rendre exécutable :

```bash
chmod +x export-projects.sh
```

## 🚀 Utilisation

### 📝 Commandes Artisan

#### 1. Export d'un seul projet

```bash
# Export PDF simple
php artisan project:export single --id=1

# Export Word
php artisan project:export single --id=1 --format=word

# Export PDF et Word
php artisan project:export single --id=1 --format=both

# Avec compression ZIP
php artisan project:export single --id=1 --format=both --zip

# Envoi par email
php artisan project:export single --id=1 --email=admin@example.com
```

#### 2. Export de plusieurs projets (batch)

```bash
# Export de projets spécifiques
php artisan project:export batch --ids=1 --ids=2 --ids=3 --format=pdf

# Avec archive ZIP
php artisan project:export batch --ids=1 --ids=2 --ids=3 --format=both --zip
```

#### 3. Export de tous les projets

```bash
# Export simple
php artisan project:export all --format=pdf

# En arrière-plan (queue)
php artisan project:export all --format=pdf --queue
```

#### 4. Export par statut

```bash
# Exporter tous les projets approuvés
php artisan project:export by-status --status=approved --format=pdf

# Exporter les projets en cours avec ZIP
php artisan project:export by-status --status=in_progress --format=both --zip
```

#### 5. Export par plage de dates

```bash
# Projets créés en janvier 2025
php artisan project:export by-date --from=2025-01-01 --to=2025-01-31

# Projets du mois en cours
php artisan project:export by-date --from=2025-01-01 --to=2025-01-31 --format=both

# Avec envoi par email
php artisan project:export by-date --from=2025-01-01 --to=2025-01-31 --email=manager@example.com
```

### 🖥️ Script Bash Interactif

Le script `export-projects.sh` offre une interface interactive et des raccourcis :

#### Mode interactif

```bash
./export-projects.sh interactive
```

Ce mode affiche un menu avec toutes les options disponibles.

#### Raccourcis pratiques

```bash
# Export des projets d'aujourd'hui
./export-projects.sh today --format=pdf --zip

# Export de la semaine courante
./export-projects.sh week --format=both

# Export du mois en cours
./export-projects.sh month --format=pdf --email=admin@example.com

# Export d'un projet avec toutes les options
./export-projects.sh single --id=1 --format=both --zip --email=admin@example.com
```

### 🎨 Options disponibles

| Option | Description | Valeurs |
|--------|------------|---------|
| `--format` | Format d'export | `pdf`, `word`, `both` |
| `--zip` | Créer une archive ZIP | Flag sans valeur |
| `--email` | Envoyer par email | Adresse email |
| `--queue` | Exécuter en arrière-plan | Flag sans valeur |
| `--output-dir` | Répertoire de sortie | Chemin personnalisé |
| `--with-toc` | Table des matières | `true` (défaut), `false` |
| `--language` | Langue du document | `fr` (défaut), `en` |
| `--template` | Template personnalisé | Nom du template |

## 📊 Export en arrière-plan (Queue)

Pour les exports volumineux, utilisez l'option `--queue` :

```bash
# Lancer l'export en arrière-plan
php artisan project:export all --format=both --queue

# Démarrer le worker pour traiter la queue
php artisan queue:work
```

### Configuration des Jobs

Dans `.env` :

```env
QUEUE_CONNECTION=database
```

Créer les tables de queue :

```bash
php artisan queue:table
php artisan migrate
```

## 📧 Configuration Email

Pour l'envoi par email, configurez dans `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

## 🎯 Cas d'usage pratiques

### Export mensuel automatique

Créez une tâche planifiée dans `app/Console/Kernel.php` :

```php
protected function schedule(Schedule $schedule)
{
    // Export mensuel le 1er de chaque mois à 2h du matin
    $schedule->command('project:export all --format=pdf --zip --email=director@example.com')
        ->monthlyOn(1, '02:00');
    
    // Export hebdomadaire des projets approuvés
    $schedule->command('project:export by-status --status=approved --format=both --zip')
        ->weekly()
        ->sundays()
        ->at('18:00');
}
```

### Export avec filtres personnalisés

Dans votre contrôleur :

```php
use Artisan;

public function exportCustom(Request $request)
{
    $exitCode = Artisan::call('project:export', [
        'action' => 'by-date',
        '--from' => $request->date_from,
        '--to' => $request->date_to,
        '--format' => $request->format ?? 'pdf',
        '--zip' => $request->has('compress'),
        '--email' => $request->email
    ]);
    
    if ($exitCode === 0) {
        return response()->json(['message' => 'Export réussi']);
    }
    
    return response()->json(['message' => 'Erreur lors de l\'export'], 500);
}
```

## 📈 Monitoring et Logs

Les exports sont automatiquement loggés dans :
- `storage/logs/project_exports.log` - Log détaillé des exports
- `storage/logs/laravel.log` - Logs généraux Laravel

Chaque export génère aussi un rapport CSV dans le répertoire d'export.

## 🔧 Personnalisation

### Modifier le template PDF

Éditez `resources/views/exports/project-idea-with-toc.blade.php` pour personnaliser :
- Les styles CSS
- La structure du document
- Les couleurs et polices
- Le format de la table des matières

### Ajouter des sections

Dans `ProjectExportService.php`, ajoutez vos sections personnalisées :

```php
private function addCustomSection($section, $project)
{
    $section->addTitle('Ma Section Personnalisée', 1);
    // Ajouter le contenu...
}
```

## 🐛 Dépannage

### Problème de mémoire

Pour les exports volumineux, augmentez la limite de mémoire :

```php
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);
```

### Caractères spéciaux dans PDF

Assurez-vous d'avoir les polices DejaVu installées :

```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

### Queue qui ne se traite pas

Vérifiez que le worker est en cours d'exécution :

```bash
php artisan queue:work --timeout=3600
```

Pour un déploiement en production, utilisez Supervisor :

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --timeout=3600
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/worker.log
```

## 📚 Exemples complets

### Export complet avec toutes les options

```bash
php artisan project:export all \
    --format=both \
    --zip \
    --email=director@ministry.bj \
    --output-dir=/var/exports/2025-01 \
    --with-toc=true \
    --language=fr \
    --queue
```

### Script d'export quotidien

```bash
#!/bin/bash
# daily-export.sh

DATE=$(date +%Y-%m-%d)
OUTPUT_DIR="/var/exports/daily/$DATE"

# Export des projets du jour
php artisan project:export by-date \
    --from="$DATE" \
    --to="$DATE" \
    --format=pdf \
    --output-dir="$OUTPUT_DIR" \
    --zip

# Envoyer le rapport par email
if [ $? -eq 0 ]; then
    echo "Export quotidien réussi" | mail -s "Export $DATE" admin@example.com
else
    echo "Échec de l'export quotidien" | mail -s "ERREUR Export $DATE" admin@example.com
fi
```

## 🤝 Support

Pour toute question ou problème :
1. Vérifiez les logs dans `storage/logs/`
2. Consultez le rapport CSV généré
3. Utilisez le mode verbeux : `php artisan project:export all -vvv`

## 📄 Licence

Ce système d'exportation est fourni sous licence MIT.
