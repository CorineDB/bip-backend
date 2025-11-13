# 📦 Package Complet - Générateur de Template Excel

## 🎯 Description du Projet

Ce package contient tous les fichiers nécessaires pour générer automatiquement un fichier Excel formaté pour l'évaluation de notes conceptuelles de projets, basé sur le document O-5_Outil_evaluation_de_la_note_conceptuelle.xlsx.

## 📂 Contenu du Package

### Fichiers Principaux

1. **generate_template.php** (19 KB)
   - Script principal avec la classe `NoteConceptuelleTemplate`
   - Génère le template complet avec toutes les sections
   - Inclut les formules automatiques et les validations
   - Prêt à l'emploi

2. **example_simple.php** (4.9 KB)
   - Version simplifiée pour débuter rapidement
   - Exemple de base avec 5 questions
   - Parfait pour comprendre le fonctionnement

3. **exemples_personnalisation.php** (11 KB)
   - 8 exemples de personnalisation avancée
   - Ajout de logo, couleurs personnalisées
   - Protection par mot de passe
   - Génération multi-projets

### Documentation

4. **README.md** (4.7 KB)
   - Documentation complète du projet
   - Guide d'utilisation détaillé
   - Exemples de code
   - Résolution de problèmes

5. **INSTALLATION.md** (4.5 KB)
   - Guide d'installation pas à pas
   - Installation de Composer
   - Dépannage commun
   - Tests de vérification

6. **composer.json** (717 bytes)
   - Configuration des dépendances
   - Scripts automatiques
   - Compatible PHP 7.4+

## 🚀 Démarrage Rapide (3 minutes)

### Étape 1 : Installation
```bash
# Placez tous les fichiers dans un dossier
cd mon-projet

# Installez les dépendances
composer install
```

### Étape 2 : Test Rapide
```bash
# Générez un template simplifié
php example_simple.php

# Ou générez le template complet
php generate_template.php
```

### Étape 3 : Personnalisation
Éditez `generate_template.php` selon vos besoins ou consultez `exemples_personnalisation.php` pour des idées.

## 🔑 Fonctionnalités Clés

### ✅ Template Complet
- **31 rubriques** d'évaluation
- **En-tête** avec informations du projet
- **Sections thématiques** :
  - Contexte et justification
  - Objectifs (général et spécifiques)
  - Démarche de conduite
  - Budget et financement
  - Résultats de l'examen

### 🎨 Mise en Forme Professionnelle
- **3 couleurs thématiques** :
  - Rouge (FFC00000) pour l'évaluation
  - Bleu clair (FFEBFFFC) pour les en-têtes
  - Vert sarcelle (FF09A493) pour les sections
- **Polices** en gras et tailles variées
- **Cellules fusionnées** pour une meilleure lisibilité
- **Bordures** automatiques

### 📊 Formules Automatiques
```excel
=COUNTIF(C$14:C$44,"Validé")      # Nombre de validations
=COUNTIF(C$14:C$44,"Réservé")     # Nombre de réservations
=COUNTIF(C$14:C$44,"Rejeté")      # Nombre de rejets
=IF(B53>0,B50/B53,0)              # Pourcentage de validation
```

### ✏️ Validation des Données
- Listes déroulantes : "Validé", "Réservé", "Rejeté"
- Messages d'aide intégrés
- Protection contre les erreurs de saisie

## 💡 Cas d'Usage

### Usage 1 : Application Web
```php
// index.php
if (isset($_POST['generer'])) {
    $generator = new NoteConceptuelleTemplate();
    $generator->download('evaluation_' . date('Y-m-d') . '.xlsx');
}
```

### Usage 2 : Script Batch
```php
// batch_generation.php
$projets = ['Projet A', 'Projet B', 'Projet C'];
foreach ($projets as $projet) {
    $gen = new NoteConceptuelleTemplate();
    $gen->generate("Evaluation_{$projet}.xlsx");
}
```

### Usage 3 : API REST
```php
// api.php
header('Content-Type: application/json');
$generator = new NoteConceptuelleTemplate();
$file = $generator->generate('temp_' . uniqid() . '.xlsx');
echo json_encode(['file' => $file, 'size' => filesize($file)]);
```

## 🔧 Personnalisation Facile

### Changer les Couleurs
```php
// Dans generate_template.php
const COLOR_RED = 'FFFF0000';        // Nouveau rouge
const COLOR_LIGHT_BLUE = 'FF0000FF'; // Nouveau bleu
```

### Ajouter une Section
```php
$this->addSection(60, 'Ma nouvelle section', 'A:B');
$this->addValidationButton(60);
$this->addGuide(60);
```

### Modifier la Validation
```php
$validation->setFormula1('"Excellent,Bon,Moyen,Faible"');
```

## 📋 Structure du Template Généré

```
NOTE CONCEPTUELLE DE PROJET
├── Informations du projet
│   ├── Titre du projet
│   ├── Numéro BIP
│   ├── Coût
│   └── Dates
├── Sections d'évaluation (avec validation)
│   ├── Contexte et justification
│   ├── Objectifs
│   ├── Démarche de conduite
│   └── Budget
├── Signature
│   ├── Informations du proposant
│   └── Nom du ministère
└── Résultats de l'examen
    ├── Compteurs automatiques
    └── Pourcentage de validation
```

## 🎓 Exemples de Personnalisation

Le fichier `exemples_personnalisation.php` contient :

1. ✅ Ajout de logo en en-tête
2. ✅ Couleurs par ministère
3. ✅ Sections personnalisées (analyse des risques)
4. ✅ Métadonnées du document
5. ✅ Feuille de statistiques supplémentaire
6. ✅ Mise en forme conditionnelle
7. ✅ Protection par mot de passe
8. ✅ Génération multiple

## 🐛 Support et Dépannage

### Problèmes Courants

**"Class not found"**
```bash
composer dump-autoload
```

**"Extension zip missing"**
```bash
sudo apt-get install php-zip php-xml
```

**Erreur de mémoire**
```ini
memory_limit = 256M  # Dans php.ini
```

### Ressources

- 📖 [PhpSpreadsheet Docs](https://phpspreadsheet.readthedocs.io/)
- 🌐 [PHP Manual](https://www.php.net/manual/fr/)
- 📦 [Composer](https://getcomposer.org/)

## 📊 Caractéristiques Techniques

| Caractéristique | Détail |
|----------------|--------|
| **Langage** | PHP 7.4+ / 8.x |
| **Bibliothèque** | PhpSpreadsheet 1.28+ |
| **Format** | XLSX (Excel 2007+) |
| **Taille** | ~15-20 KB |
| **Temps** | ~1-2 secondes |
| **Mémoire** | ~50-100 MB |

## 🎯 Roadmap (Améliorations Futures)

- [ ] Support multi-langues (FR/EN)
- [ ] Thèmes de couleurs prédéfinis
- [ ] Import de données depuis JSON/CSV
- [ ] Export en PDF
- [ ] Génération de graphiques
- [ ] Interface web complète
- [ ] API RESTful

## 📄 Licence

Ce code est fourni à titre d'exemple éducatif.
Vous êtes libre de le modifier et de l'adapter à vos besoins.

## ✨ Conclusion

Ce package vous offre une solution complète et professionnelle pour générer automatiquement des templates d'évaluation de notes conceptuelles. 

**Prêt à commencer ?**

```bash
composer install
php example_simple.php
```

**Questions ? Problèmes ?**
Consultez les fichiers de documentation inclus.

**Bon développement ! 🚀**

---

*Package créé le 13 novembre 2025*
*Version 1.0.0*
