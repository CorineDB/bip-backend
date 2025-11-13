<?php
/**
 * Script de Test - Vérification de l'Installation
 * 
 * Ce script vérifie que toutes les dépendances sont installées
 * et teste la génération des templates
 */

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║   TEST D'INSTALLATION - Générateur de Template Excel        ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Test 1 : Version PHP
echo "📌 Test 1 : Version PHP\n";
$phpVersion = phpversion();
echo "   Version détectée : {$phpVersion}\n";
if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "   ✅ PHP version compatible (>= 7.4)\n\n";
} else {
    echo "   ❌ ERREUR : PHP 7.4 ou supérieur requis\n\n";
    exit(1);
}

// Test 2 : Extensions PHP
echo "📌 Test 2 : Extensions PHP requises\n";
$extensions = ['zip', 'xml', 'xmlwriter', 'xmlreader', 'mbstring', 'gd'];
$missingExtensions = [];

foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✅' : '❌';
    echo "   {$status} Extension {$ext}: " . ($loaded ? 'OK' : 'MANQUANTE') . "\n";
    if (!$loaded && $ext !== 'gd') { // gd est optionnelle
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    echo "\n   ⚠️  Extensions manquantes : " . implode(', ', $missingExtensions) . "\n";
    echo "   Installez-les avec : sudo apt-get install php-" . implode(' php-', $missingExtensions) . "\n\n";
    exit(1);
}
echo "\n";

// Test 3 : Composer et autoload
echo "📌 Test 3 : Composer et autoload\n";
if (file_exists('vendor/autoload.php')) {
    echo "   ✅ Fichier vendor/autoload.php trouvé\n";
    require 'vendor/autoload.php';
    echo "   ✅ Autoload chargé avec succès\n\n";
} else {
    echo "   ❌ ERREUR : vendor/autoload.php non trouvé\n";
    echo "   Exécutez : composer install\n\n";
    exit(1);
}

// Test 4 : PhpSpreadsheet
echo "📌 Test 4 : Bibliothèque PhpSpreadsheet\n";
if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    echo "   ✅ PhpSpreadsheet est disponible\n";
    
    // Vérifier la version
    try {
        $reflection = new ReflectionClass('PhpOffice\PhpSpreadsheet\Spreadsheet');
        $composerFile = dirname(dirname($reflection->getFileName())) . '/composer.json';
        if (file_exists($composerFile)) {
            $composerData = json_decode(file_get_contents($composerFile), true);
            $version = $composerData['version'] ?? 'inconnue';
            echo "   Version : {$version}\n\n";
        }
    } catch (Exception $e) {
        echo "   Version : non détectée\n\n";
    }
} else {
    echo "   ❌ ERREUR : PhpSpreadsheet non trouvée\n";
    echo "   Installez avec : composer require phpoffice/phpspreadsheet\n\n";
    exit(1);
}

// Test 5 : Fichiers du projet
echo "📌 Test 5 : Fichiers du projet\n";
$fichiers = [
    'generate_template.php' => 'Script principal',
    'example_simple.php' => 'Exemple simplifié',
    'exemples_personnalisation.php' => 'Exemples avancés',
    'README.md' => 'Documentation',
    'INSTALLATION.md' => 'Guide d\'installation',
    'composer.json' => 'Configuration Composer'
];

$fichierManquants = [];
foreach ($fichiers as $fichier => $description) {
    $existe = file_exists($fichier);
    $status = $existe ? '✅' : '⚠️ ';
    echo "   {$status} {$fichier} ({$description})\n";
    if (!$existe && in_array($fichier, ['generate_template.php', 'composer.json'])) {
        $fichierManquants[] = $fichier;
    }
}

if (!empty($fichierManquants)) {
    echo "\n   ❌ Fichiers critiques manquants : " . implode(', ', $fichierManquants) . "\n\n";
    exit(1);
}
echo "\n";

// Test 6 : Permissions d'écriture
echo "📌 Test 6 : Permissions d'écriture\n";
$testFile = 'test_write_' . time() . '.tmp';
if (@file_put_contents($testFile, 'test')) {
    echo "   ✅ Permissions d'écriture OK\n";
    @unlink($testFile);
} else {
    echo "   ❌ ERREUR : Impossible d'écrire dans le répertoire\n\n";
    exit(1);
}
echo "\n";

// Test 7 : Génération d'un fichier de test
echo "📌 Test 7 : Génération d'un template de test\n";
try {
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Test de génération');
    $sheet->setCellValue('A2', 'Date : ' . date('Y-m-d H:i:s'));
    
    $testFileName = 'test_generation_' . time() . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($testFileName);
    
    if (file_exists($testFileName)) {
        $size = filesize($testFileName);
        echo "   ✅ Fichier de test créé : {$testFileName}\n";
        echo "   Taille : " . number_format($size) . " octets\n";
        @unlink($testFileName);
        echo "   ✅ Fichier de test supprimé\n";
    } else {
        echo "   ❌ ERREUR : Impossible de créer le fichier\n\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ❌ ERREUR : " . $e->getMessage() . "\n\n";
    exit(1);
}
echo "\n";

// Test 8 : Test de la classe NoteConceptuelleTemplate
echo "📌 Test 8 : Classe NoteConceptuelleTemplate\n";
if (file_exists('generate_template.php')) {
    require_once 'generate_template.php';
    
    if (class_exists('NoteConceptuelleTemplate')) {
        echo "   ✅ Classe NoteConceptuelleTemplate disponible\n";
        
        // Test d'instanciation
        try {
            $generator = new NoteConceptuelleTemplate();
            echo "   ✅ Instanciation réussie\n";
            
            // Test des méthodes
            $methods = ['generate', 'download'];
            foreach ($methods as $method) {
                if (method_exists($generator, $method)) {
                    echo "   ✅ Méthode {$method}() disponible\n";
                } else {
                    echo "   ⚠️  Méthode {$method}() non trouvée\n";
                }
            }
        } catch (Exception $e) {
            echo "   ❌ ERREUR lors de l'instanciation : " . $e->getMessage() . "\n\n";
            exit(1);
        }
    } else {
        echo "   ❌ ERREUR : Classe NoteConceptuelleTemplate non trouvée\n\n";
        exit(1);
    }
} else {
    echo "   ⚠️  Fichier generate_template.php non trouvé (test ignoré)\n";
}
echo "\n";

// Test 9 : Mémoire disponible
echo "📌 Test 9 : Configuration mémoire\n";
$memoryLimit = ini_get('memory_limit');
echo "   Limite mémoire : {$memoryLimit}\n";
$memoryInBytes = return_bytes($memoryLimit);
if ($memoryInBytes >= 128 * 1024 * 1024) {
    echo "   ✅ Mémoire suffisante (>= 128M)\n";
} else {
    echo "   ⚠️  Mémoire faible, augmentez à 256M recommandé\n";
}
echo "\n";

// Test 10 : Résumé final
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    RÉSUMÉ DES TESTS                          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "   ✅ Tous les tests sont passés avec succès!\n\n";
echo "🎉 INSTALLATION VALIDÉE - Vous pouvez maintenant générer vos templates!\n\n";

echo "📚 Prochaines étapes :\n";
echo "   1. Générer un template simple : php example_simple.php\n";
echo "   2. Générer le template complet : php generate_template.php\n";
echo "   3. Consulter la documentation : cat README.md\n";
echo "   4. Explorer les exemples : cat exemples_personnalisation.php\n\n";

echo "💡 Aide :\n";
echo "   - Documentation : README.md\n";
echo "   - Installation : INSTALLATION.md\n";
echo "   - Index : INDEX.md\n\n";

// Fonction helper
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}
