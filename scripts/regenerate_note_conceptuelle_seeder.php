<?php

/**
 * Script pour régénérer le seeder du canevas de rédaction de note conceptuelle
 * Usage: php scripts/regenerate_note_conceptuelle_seeder.php
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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CanvasRedactionNoteConceptuelleSeeder extends Seeder
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

            \$formsData = \$this->documentData['forms'] ?? [];
            \$documentData = collect(\$this->documentData)->except(['forms', 'champs', 'id'])->toArray();
            \$documentData = array_merge(\$documentData, ["categorieId" => \$categorieDocument->id]);

            \$document = Document::updateOrCreate(['nom' => \$documentData['nom']], \$documentData);

            if (!empty(\$formsData)) {
                foreach (\$formsData as \$elementData) {
                    \$this->createElementRecursive(\$elementData, \$document, null);
                }
            }

            DB::commit();
        } catch (\Exception \$e) {
            DB::rollback();
            throw \$e;
        }
    }

    private function createElementRecursive(array \$elementData, \$document, \$parentSection = null): void
    {
        if (\$elementData['element_type'] === 'section') {
            \$this->createSection(\$elementData, \$document, \$parentSection);
        } elseif (\$elementData['element_type'] === 'field') {
            \$this->createChamp(\$elementData, \$document, \$parentSection);
        }
    }

    private function createSection(array \$sectionData, \$document, \$parentSection = null): void
    {
        \$sectionAttributes = [
            'intitule' => \$sectionData['intitule'] ?? \$sectionData['label'] ?? 'Section sans titre',
            'slug' => \$sectionData['key'] ?? \$sectionData['attribut'] ?? null,
            'description' => \$sectionData['description'] ?? null,
            'documentId' => \$document->id,
            'parentSectionId' => \$parentSection ? \$parentSection->id : null,
            'ordre_affichage' => \$sectionData['ordre_affichage'],
        ];

        \$section = \$document->sections()->updateOrCreate([
            'intitule' => \$sectionData['intitule'] ?? \$sectionData['label'] ?? 'Section sans titre',
            'documentId' => \$document->id
        ], \$sectionAttributes);

        if (isset(\$sectionData['elements']) && !empty(\$sectionData['elements'])) {
            foreach (\$sectionData['elements'] as \$childElement) {
                \$this->createElementRecursive(\$childElement, \$document, \$section);
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

        \App\Models\Champ::updateOrCreate([
            'attribut' => \$champData['attribut'],
            'sectionId' => \$parentSection ? \$parentSection->id : null,
            'documentId' => \$document->id
        ], \$champAttributes);
    }
}

PHP;
}

echo "🔍 Extraction du canevas de rédaction de note conceptuelle...\n\n";

$canevas = $documentRepo->getCanevasRedactionNoteConceptuelle();

if (!$canevas) {
    echo "   ❌ Document non trouvé\n";
    exit(1);
}

// Obtenir la structure via CanevasNoteConceptuelleResource
$resource = new \App\Http\Resources\CanevasNoteConceptuelleResource($canevas);
$data = $resource->toArray(request());

echo "   ✅ Document trouvé: {$data['nom']}\n";
echo "   📊 Forms: " . count($data['forms'] ?? []) . "\n";

// Convertir en tableau pur (supprimer Collections et objets Laravel)
$data = json_decode(json_encode($data), true);

// Nettoyer les données (supprimer IDs et réindexer)
$cleanedForms = cleanData($data['forms'] ?? []);

// Préparer les données du document
$documentData = [
    'nom' => $data['nom'],
    'slug' => $data['slug'] ?? 'canevas-redaction-note-conceptuelle',
    'description' => $data['description'] ?? '',
    'type' => $data['type'],
    'forms' => $cleanedForms,
];

echo "   📝 Slug: " . ($data['slug'] ?? 'canevas-redaction-note-conceptuelle') . "\n";

// Générer le contenu du seeder
$seederContent = generateSeederContent($documentData, 'canevas-redaction-note-conceptuelle');

// Écrire le fichier seeder
$seederPath = __DIR__ . "/../database/seeders/CanvasRedactionNoteConceptuelleSeeder.php";
file_put_contents($seederPath, $seederContent);

echo "   💾 Seeder généré: {$seederPath}\n\n";
echo "✨ Régénération terminée!\n";
