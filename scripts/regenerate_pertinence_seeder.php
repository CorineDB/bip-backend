<?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Repositories\CategorieCritereRepository;
use App\Http\Resources\CategorieCritereResource;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Function to clean data (remove IDs and convert Collections)
function cleanData($data) {
    if (!is_array($data)) {
        return $data;
    }

    $cleaned = [];
    foreach ($data as $key => $value) {
        // Ignorer les clés ID
        if (in_array($key, ['id', 'sectionId', 'documentId', 'categorieId', 'parentSectionId', 'category_id', 'critere_id', 'categorie_critere_id'])) {
            continue;
        }

        if (is_numeric($key)) {
            // Conserver les clés numériques mais les nettoyer récursivement
            $cleaned[$key] = is_array($value) ? cleanData($value) : $value;
        } else {
            $cleaned[$key] = is_array($value) ? cleanData($value) : $value;
        }
    }

    return $cleaned;
}

try {
    echo "Régénération du seeder de la grille d'évaluation de pertinence...\n\n";

    $repository = app(CategorieCritereRepository::class);
    $grillePertinence = $repository->getCanevasEvaluationDePertinence();

    if (!$grillePertinence) {
        echo "Erreur: Grille d'évaluation de pertinence non trouvée dans la base de données.\n";
        exit(1);
    }

    // Transform to resource
    $resource = new CategorieCritereResource($grillePertinence);
    $data = $resource->toArray(request());

    // Convert to pure array (remove Collections)
    $data = json_decode(json_encode($data), true);

    // Clean data
    $data = cleanData($data);

    echo "Type: " . $data['type'] . "\n";
    echo "Slug: " . $data['slug'] . "\n";
    echo "Nombre de critères: " . count($data['criteres'] ?? []) . "\n\n";

    // Generate seeder content
    $seederContent = "<?php\n\n";
    $seederContent .= "namespace Database\\Seeders;\n\n";
    $seederContent .= "use Illuminate\\Database\\Console\\Seeds\\WithoutModelEvents;\n";
    $seederContent .= "use Illuminate\\Database\\Seeder;\n";
    $seederContent .= "use Illuminate\\Support\\Facades\\DB;\n\n";
    $seederContent .= "class GrilleEvaluationPertinenceSeeder extends Seeder\n";
    $seederContent .= "{\n";
    $seederContent .= "    /**\n";
    $seederContent .= "     * Run the database seeds.\n";
    $seederContent .= "     */\n";
    $seederContent .= "    public function run(): void\n";
    $seederContent .= "    {\n";

    // Create CategorieCritere
    $seederContent .= "        \$categorieCritere = \\App\\Models\\CategorieCritere::firstOrCreate([\n";
    $seederContent .= "            'slug' => " . var_export($data['slug'], true) . ",\n";
    $seederContent .= "        ], [\n";
    $seederContent .= "            'type' => " . var_export($data['type'], true) . ",\n";
    $seederContent .= "            'slug' => " . var_export($data['slug'], true) . ",\n";
    $seederContent .= "            'is_mandatory' => " . var_export($data['is_mandatory'], true) . "\n";
    $seederContent .= "        ]);\n\n";

    // Function to create valid PHP variable name from text
    $createVarName = function($text, $prefix = '') {
        // Remove special characters and convert to camelCase
        $text = trim($text);
        // Replace accented characters
        $text = str_replace(
            ['é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ü', 'ç', 'É', 'È', 'Ê', 'À', 'Ô', 'Ù'],
            ['e', 'e', 'e', 'e', 'a', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'c', 'E', 'E', 'E', 'A', 'O', 'U'],
            $text
        );
        // Remove parentheses and their content, commas, and other special chars
        $text = preg_replace('/\([^)]*\)/', '', $text);
        $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        // Convert to words and capitalize
        $words = explode(' ', $text);
        $words = array_filter(array_map('trim', $words));
        $result = '';
        foreach ($words as $word) {
            $result .= ucfirst(strtolower($word));
        }
        return '$' . $prefix . $result;
    };

    // Process each critere
    if (!empty($data['criteres'])) {
        foreach ($data['criteres'] as $critere) {
            $critereVarName = $createVarName($critere['intitule'], 'critere');

            $seederContent .= "        // Critère " . $critere['intitule'] . "\n";
            $seederContent .= "        " . $critereVarName . " = \\App\\Models\\Critere::updateOrCreate([\n";
            $seederContent .= "            'intitule' => " . var_export($critere['intitule'], true) . ",\n";
            $seederContent .= "            'categorie_critere_id' => \$categorieCritere->id\n";
            $seederContent .= "        ], [\n";
            $seederContent .= "            'ponderation' => " . var_export($critere['ponderation'], true) . ",\n";
            $seederContent .= "            'commentaire' => " . var_export($critere['commentaire'], true) . ",\n";
            $seederContent .= "            'is_mandatory' => " . var_export($critere['is_mandatory'], true) . "\n";
            $seederContent .= "        ]);\n\n";

            // Process notations for this critere
            if (!empty($critere['notations'])) {
                $notationsVarName = $createVarName($critere['intitule'], 'notations');

                $seederContent .= "        // Notations pour " . $critere['intitule'] . "\n";
                $seederContent .= "        " . $notationsVarName . " = [\n";

                foreach ($critere['notations'] as $notation) {
                    $seederContent .= "            ['libelle' => " . var_export($notation['libelle'], true) . ", ";
                    $seederContent .= "'valeur' => " . var_export($notation['valeur'], true) . ", ";
                    $seederContent .= "'commentaire' => " . var_export($notation['commentaire'], true) . "],\n";
                }

                $seederContent .= "        ];\n\n";

                $seederContent .= "        foreach (" . $notationsVarName . " as \$notation) {\n";
                $seederContent .= "            \\App\\Models\\Notation::firstOrCreate([\n";
                $seederContent .= "                'libelle' => \$notation['libelle'],\n";
                $seederContent .= "                'critere_id' => " . $critereVarName . "->id,\n";
                $seederContent .= "                'categorie_critere_id' => \$categorieCritere->id\n";
                $seederContent .= "            ], [\n";
                $seederContent .= "                'valeur' => \$notation['valeur'],\n";
                $seederContent .= "                'commentaire' => \$notation['commentaire']\n";
                $seederContent .= "            ]);\n";
                $seederContent .= "        }\n\n";
            }
        }
    }

    $seederContent .= "    }\n";
    $seederContent .= "}\n";

    // Write seeder file
    $seederPath = __DIR__ . '/../database/seeders/GrilleEvaluationPertinenceSeeder.php';
    file_put_contents($seederPath, $seederContent);

    echo "✓ Seeder régénéré avec succès: GrilleEvaluationPertinenceSeeder.php\n";
    echo "Fichier créé: $seederPath\n\n";

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
