<?php

/**
 * Script pour régénérer le seeder de la fiche d'idée de projet
 * Usage: php scripts/regenerate_fiche_idee_seeder.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$documentRepo = app(\App\Repositories\DocumentRepository::class);

/**
 * Nettoyer récursivement les données (supprimer IDs)
 */
function cleanData($data) {
    if (!is_array($data)) {
        return $data;
    }

    $cleaned = [];

    foreach ($data as $key => $value) {
        // Ignorer les clés ID
        if (in_array($key, ['id', 'sectionId', 'documentId', 'categorieId', 'parentSectionId', 'category_id'])) {
            continue;
        }

        // Nettoyer récursivement les valeurs
        if (is_array($value)) {
            $cleaned[$key] = cleanData($value);
        } else {
            $cleaned[$key] = $value;
        }
    }

    return $cleaned;
}

/**
 * Générer le contenu du fichier seeder
 */
function generateSeederContent(array $documentData, string $categorieSlug): string
{
    $documentDataExport = var_export($documentData, true);

    // Nettoyer l'export pour un meilleur formatage
    $documentDataExport = str_replace('array (', '[', $documentDataExport);
    $documentDataExport = str_replace(')', ']', $documentDataExport);
    $documentDataExport = preg_replace('/=> \n\s+\[/', '=> [', $documentDataExport);

    // Supprimer les clés numériques (0 =>, 1 =>, 2 =>, etc.)
    $documentDataExport = preg_replace('/\n\s+\d+ => /', "\n      ", $documentDataExport);

    // Extraire le nom de la catégorie à partir du slug
    $categorieNom = ucfirst(str_replace('-', ' ', $categorieSlug));

    return <<<PHP
<?php

namespace Database\Seeders;

use App\Models\CategorieDocument;
use App\Models\Document;
use App\Models\Section;
use App\Models\Champ;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CanevasRedactionFicheIdeeProjet extends Seeder
{
    protected \$documentData = {$documentDataExport};

    public function run(): void
    {
        DB::beginTransaction();

        try {
            \$categorieDocument = CategorieDocument::updateOrCreate([
                'slug' => '{$categorieSlug}'
            ], [
                'nom' => "{$categorieNom}",
                'slug' => '{$categorieSlug}',
                'format' => 'formulaire'
            ]);

            \$sectionsData = \$this->documentData['sections'] ?? [];
            \$champsData = \$this->documentData['champs'] ?? [];
            \$documentData = collect(\$this->documentData)->except(['sections', 'champs', 'id', 'categorie'])->toArray();
            \$documentData = array_merge(\$documentData, ["categorieId" => \$categorieDocument->id]);

            \$document = Document::updateOrCreate(['nom' => \$documentData['nom']], \$documentData);

            // Créer les sections
            if (!empty(\$sectionsData)) {
                foreach (\$sectionsData as \$sectionData) {
                    \$this->createSection(\$sectionData, \$document, null);
                }
            }

            // Créer les champs directs (sans section)
            if (!empty(\$champsData)) {
                foreach (\$champsData as \$champData) {
                    \$this->createChamp(\$champData, \$document, null);
                }
            }

            DB::commit();
        } catch (\Exception \$e) {
            DB::rollback();
            throw \$e;
        }
    }

    private function createSection(array \$sectionData, \$document, \$parentSection = null): void
    {
        \$sectionAttributes = [
            'intitule' => \$sectionData['intitule'],
            'slug' => \$sectionData['slug'] ?? null,
            'description' => \$sectionData['description'] ?? null,
            'documentId' => \$document->id,
            'parentSectionId' => \$parentSection ? \$parentSection->id : null,
            'ordre_affichage' => \$sectionData['ordre_affichage'],
        ];

        \$section = Section::updateOrCreate([
            'intitule' => \$sectionData['intitule'],
            'documentId' => \$document->id,
            'parentSectionId' => \$parentSection ? \$parentSection->id : null
        ], \$sectionAttributes);

        // Créer les sous-sections
        if (isset(\$sectionData['childSections']) && !empty(\$sectionData['childSections'])) {
            foreach (\$sectionData['childSections'] as \$childSection) {
                \$this->createSection(\$childSection, \$document, \$section);
            }
        }

        // Créer les champs de la section
        if (isset(\$sectionData['champs']) && !empty(\$sectionData['champs'])) {
            foreach (\$sectionData['champs'] as \$champData) {
                \$this->createChamp(\$champData, \$document, \$section);
            }
        }
    }

    private function createChamp(array \$champData, \$document, \$parentSection = null): void
    {
        \$champAttributes = [
            'label' => \$champData['label'],
            'info' => \$champData['info'] ?? null,
            'attribut' => \$champData['attribut'] ?? null,
            'placeholder' => \$champData['placeholder'] ?? null,
            'is_required' => \$champData['is_required'] ?? false,
            'champ_standard' => \$champData['champ_standard'] ?? false,
            'isEvaluated' => \$champData['isEvaluated'] ?? false,
            'default_value' => \$champData['default_value'] ?? null,
            'ordre_affichage' => \$champData['ordre_affichage'],
            'type_champ' => \$champData['type_champ'],
            'meta_options' => \$champData['meta_options'] ?? [],
            'startWithNewLine' => \$champData['startWithNewLine'] ?? false,
            'documentId' => \$document->id,
            'sectionId' => \$parentSection ? \$parentSection->id : null
        ];

        Champ::updateOrCreate([
            'attribut' => \$champData['attribut'],
            'sectionId' => \$parentSection ? \$parentSection->id : null,
            'documentId' => \$document->id
        ], \$champAttributes);
    }
}

PHP;
}

echo "🔍 Extraction de la fiche d'idée de projet...\n\n";

$ficheIdee = $documentRepo->getFicheIdee();

if (!$ficheIdee) {
    echo "   ❌ Document fiche d'idée non trouvé\n";
    exit(1);
}

// Obtenir la structure via DocumentResource
$resource = new \App\Http\Resources\DocumentResource($ficheIdee);
$data = $resource->toArray(request());

// Convertir en tableau pur (supprimer Collections et objets Laravel)
$data = json_decode(json_encode($data), true);

echo "   ✅ Document trouvé: {$data['nom']}\n";
echo "   📊 Sections: " . count($data['sections'] ?? []) . "\n";
echo "   📊 Champs directs: " . count($data['champs'] ?? []) . "\n";

// Nettoyer les données (supprimer IDs et réindexer)
$cleanedSections = cleanData($data['sections'] ?? []);
$cleanedChamps = cleanData($data['champs'] ?? []);

// Préparer les données du document
$documentData = [
    'nom' => $data['nom'],
    'slug' => $data['slug'] ?? 'fiche-idee',
    'description' => $data['description'] ?? '',
    'type' => $data['type'],
    'sections' => $cleanedSections,
    'champs' => $cleanedChamps,
];

echo "   📝 Slug: " . ($data['slug'] ?? 'fiche-idee (par défaut)') . "\n";

// Générer le contenu du seeder
$seederContent = generateSeederContent($documentData, 'fiche-idee');

// Écrire le fichier seeder
$seederPath = __DIR__ . "/../database/seeders/CanevasRedactionFicheIdeeProjet.php";
file_put_contents($seederPath, $seederContent);

echo "   💾 Seeder généré: {$seederPath}\n\n";
echo "✨ Régénération terminée!\n";
