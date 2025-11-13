# Générateur de Template Excel - Outil d'Évaluation de Note Conceptuelle

Ce projet PHP permet de générer automatiquement un fichier Excel formaté pour l'évaluation de notes conceptuelles de projets.

## 📋 Prérequis

- PHP 7.4 ou supérieur
- Composer (gestionnaire de dépendances PHP)
- Extension PHP : `ext-zip`, `ext-xml`, `ext-gd` (optionnelle pour les images)

## 🚀 Installation

### 1. Installer les dépendances

```bash
composer require phpoffice/phpspreadsheet
```

### 2. Structure du projet

```
votre-projet/
├── vendor/              # Dossier généré par Composer
├── generate_template.php # Script principal
├── example_simple.php   # Exemple simplifié
└── composer.json        # Configuration Composer
```

## 💻 Utilisation

### Utilisation basique

```php
<?php
require 'vendor/autoload.php';
require 'generate_template.php';

// Créer une instance du générateur
$generator = new NoteConceptuelleTemplate();

// Générer le fichier Excel
$filename = $generator->generate('mon_template.xlsx');
echo "Fichier généré : {$filename}";
```

### Téléchargement direct (pour application web)

```php
<?php
require 'vendor/autoload.php';
require 'generate_template.php';

// Créer et télécharger directement
$generator = new NoteConceptuelleTemplate();
$generator->download('evaluation_projet.xlsx');
```

### Exécution en ligne de commande

```bash
php generate_template.php
```

## 📄 Structure du Template Généré

Le template Excel comprend les sections suivantes :

### 1. **En-tête du projet**
- Titre du projet
- Numéro d'identification BIP
- Coût du projet
- Dates de démarrage et d'achèvement

### 2. **Sections d'évaluation**
- Contexte et justification
- Objectif général du projet
- Objectifs spécifiques
- Résultats attendus

### 3. **Démarche de conduite**
- Démarche administrative
- Démarche technique
- Parties prenantes
- Livrables du processus
- Cohérence avec le PAG
- Pilotage et gouvernance
- Chronogramme

### 4. **Budget**
- Budget détaillé
- Coût estimatif
- Sources de financement

### 5. **Signature et résultats**
- Informations du proposant
- Résultats de l'examen (avec formules automatiques)

## 🎨 Fonctionnalités

### Mise en forme automatique
- ✅ Couleurs de fond (rouge, bleu clair, vert sarcelle)
- ✅ Polices en gras et tailles variées
- ✅ Alignements et retours à la ligne automatiques
- ✅ Bordures et fusion de cellules
- ✅ Largeurs de colonnes prédéfinies

### Validation des données
- 📋 Listes déroulantes pour les statuts de validation
- 📋 Choix : "Validé", "Réservé", "Rejeté"
- 📋 Messages d'aide pour l'utilisateur

### Formules Excel
- 🧮 Comptage automatique des rubriques validées
- 🧮 Comptage des rubriques réservées et rejetées
- 🧮 Calcul du pourcentage de validation
- 🧮 Formule : `=COUNTIF(C$14:C$44,"Validé")`

## 🔧 Personnalisation

### Modifier les couleurs

```php
// Dans la classe NoteConceptuelleTemplate
const COLOR_RED = 'FFC00000';        // Rouge pour l'évaluation
const COLOR_LIGHT_BLUE = 'FFEBFFFC'; // Bleu clair pour les titres
const COLOR_TEAL = 'FF09A493';       // Vert sarcelle pour sections
```

### Ajouter une nouvelle section

```php
// Exemple d'ajout d'une section
$this->addSection(60, 'Nouvelle section', 'A:B', self::COLOR_LIGHT_BLUE);
$this->addValidationButton(60);
$this->addGuide(60);
```

### Modifier les options de validation

```php
$validation->setFormula1('"Validé,Réservé,Rejeté,En attente"');
```

## 📊 Exemple de résultat

Le fichier Excel généré contient :
- **31 rubriques** à évaluer
- **Formules automatiques** pour les statistiques
- **Guide de notation** intégré
- **Format professionnel** prêt à l'emploi

## 🐛 Résolution de problèmes

### Erreur : "Class not found"
```bash
composer install
composer dump-autoload
```

### Erreur : "Extension zip not found"
```bash
# Ubuntu/Debian
sudo apt-get install php-zip

# CentOS/RHEL
sudo yum install php-zip
```

### Erreur de mémoire PHP
```php
ini_set('memory_limit', '256M');
```

## 📝 Notes importantes

1. **Versions PHP** : Testé avec PHP 7.4, 8.0, 8.1, 8.2
2. **PhpSpreadsheet** : Version 1.28+ recommandée
3. **Performance** : Génération en ~1-2 secondes
4. **Taille du fichier** : ~15-20 KB

## 📚 Ressources

- [Documentation PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/)
- [Guide de référence Excel](https://support.microsoft.com/excel)

## 🤝 Support

Pour toute question ou problème :
1. Vérifiez que toutes les dépendances sont installées
2. Consultez les logs d'erreur PHP
3. Testez avec l'exemple simplifié fourni

## 📄 Licence

Ce code est fourni à titre d'exemple et peut être modifié selon vos besoins.
