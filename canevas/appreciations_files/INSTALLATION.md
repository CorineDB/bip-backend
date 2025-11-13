# Guide d'Installation Rapide

## 🚀 Démarrage en 3 étapes

### Étape 1 : Prérequis
Assurez-vous d'avoir PHP installé :
```bash
php --version
# Doit afficher PHP 7.4 ou supérieur
```

### Étape 2 : Installation
```bash
# 1. Créer un nouveau dossier pour votre projet
mkdir evaluation-projet
cd evaluation-projet

# 2. Copier les fichiers du générateur
# - generate_template.php
# - example_simple.php
# - composer.json
# - README.md

# 3. Installer les dépendances
composer install
```

### Étape 3 : Utilisation
```bash
# Option A : Générer le template complet
php generate_template.php

# Option B : Générer un template simplifié
php example_simple.php
```

## 📦 Installation de Composer (si nécessaire)

### Windows
1. Télécharger : https://getcomposer.org/Composer-Setup.exe
2. Exécuter l'installateur
3. Redémarrer le terminal

### Linux / Mac
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

### Vérification
```bash
composer --version
```

## 🐛 Dépannage Courant

### Problème : "composer: command not found"
**Solution** : Installer Composer (voir ci-dessus)

### Problème : "extension zip is missing"
```bash
# Ubuntu/Debian
sudo apt-get install php-zip php-xml php-mbstring

# CentOS/RHEL
sudo yum install php-zip php-xml php-mbstring

# macOS (avec Homebrew)
brew install php
```

### Problème : "Class not found"
```bash
composer dump-autoload
```

### Problème : Erreur mémoire
Éditer `php.ini` et augmenter :
```ini
memory_limit = 256M
```

## 💡 Exemples d'Utilisation

### 1. Application Web Simple
```php
<?php
require 'vendor/autoload.php';
require 'generate_template.php';

// Quand l'utilisateur clique sur "Télécharger"
if (isset($_GET['download'])) {
    $generator = new NoteConceptuelleTemplate();
    $generator->download('evaluation_' . date('Y-m-d') . '.xlsx');
}
?>

<!DOCTYPE html>
<html>
<body>
    <h1>Générateur de Template</h1>
    <a href="?download=1">
        <button>Télécharger le Template</button>
    </a>
</body>
</html>
```

### 2. Génération avec Nom de Projet
```php
<?php
require 'vendor/autoload.php';
require 'generate_template.php';

$nomProjet = "Projet_Infrastructure_2024";
$generator = new NoteConceptuelleTemplate();
$filename = $generator->generate("Evaluation_{$nomProjet}.xlsx");
echo "Fichier créé : {$filename}";
```

### 3. Utilisation en API
```php
<?php
require 'vendor/autoload.php';
require 'generate_template.php';

header('Content-Type: application/json');

try {
    $generator = new NoteConceptuelleTemplate();
    $filename = $generator->generate('temp_' . uniqid() . '.xlsx');
    
    echo json_encode([
        'success' => true,
        'filename' => $filename,
        'size' => filesize($filename)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

## 📊 Structure des Fichiers

```
votre-projet/
├── vendor/                 # Dépendances (créé par Composer)
│   └── phpoffice/
│       └── phpspreadsheet/
├── generate_template.php   # Générateur principal
├── example_simple.php      # Exemple simplifié
├── composer.json          # Configuration dépendances
├── README.md              # Documentation complète
└── INSTALLATION.md        # Ce fichier
```

## ✅ Vérification de l'Installation

Testez avec ce script :
```php
<?php
// test.php
require 'vendor/autoload.php';

echo "PHP Version: " . phpversion() . "\n";
echo "Extension ZIP: " . (extension_loaded('zip') ? '✓' : '✗') . "\n";
echo "Extension XML: " . (extension_loaded('xml') ? '✓' : '✗') . "\n";
echo "PhpSpreadsheet: " . (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet') ? '✓' : '✗') . "\n";
```

Exécuter :
```bash
php test.php
```

Résultat attendu :
```
PHP Version: 8.1.x
Extension ZIP: ✓
Extension XML: ✓
PhpSpreadsheet: ✓
```

## 🎯 Prochaines Étapes

1. ✅ Installation terminée
2. 📖 Lire le README.md pour la documentation complète
3. 🧪 Tester avec `example_simple.php`
4. 🚀 Générer votre premier template avec `generate_template.php`
5. 🔧 Personnaliser selon vos besoins

## 📞 Besoin d'Aide ?

- Documentation PhpSpreadsheet : https://phpspreadsheet.readthedocs.io/
- PHP Manual : https://www.php.net/manual/fr/
- Composer Documentation : https://getcomposer.org/doc/

Bon développement ! 🎉
