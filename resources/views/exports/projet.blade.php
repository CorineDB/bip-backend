<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IDÉE DE PROJET</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            font-size: 14px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        .field {
            margin-bottom: 10px;
        }
        .field .label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>IDÉE DE PROJET</h1>
    </div>

    <div class="section">
        <div class="field">
            <span class="label">Titre du projet :</span>
            <span>{{ $projet['origineProjet']['titre'] ?? 'N/A' }}</span>
        </div>
        <div class="field">
            <span class="label">Numéro d'identification BIP :</span>
            <span>{{ $projet['identifiantBip'] ?? 'N/A' }}</span>
        </div>
        <div class="field">
            <span class="label">Coût du projet :</span>
            <span>{{ number_format($projet['coutEstimatif'] ?? 0, 0, ',', ' ') }} XOF</span>
        </div>
        <div class="field">
            <span class="label">Date de démarrage de l'étude :</span>
            <span>{{ $projet['dateDebutEtude'] ?? 'N/A' }}</span>
        </div>
        <div class="field">
            <span class="label">Date d'achèvement de l'étude :</span>
            <span>{{ $projet['dateFinEtude'] ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="section">
        <h2>Origine du projet</h2>
        <div class="field">
            <span class="label">Titre du projet:</span>
            <p>{{ $projet['origineProjet']['titre'] ?? 'N/A' }}</p>
        </div>
        <div class="field">
            <span class="label">Origine du projet:</span>
            <p>{{ $projet['origineProjet']['origine'] ?? 'N/A' }}</p>
        </div>
        <div class="field">
            <span class="label">Fondement:</span>
            <p>{{ $projet['origineProjet']['fondement']['informationsPND']['orientationsStrategiques'][0] ?? 'N/A' }}</p>
        </div>
        <div class="field">
            <span class="label">Situation actuelle:</span>
            <p>{{ $projet['origineProjet']['situationActuelle'] ?? 'N/A' }}</p>
        </div>
        <div class="field">
            <span class="label">Situation désirée:</span>
            <p>{{ $projet['origineProjet']['situationDesiree'] ?? 'N/A' }}</p>
        </div>
        <div class="field">
            <span class="label">Contraintes à respecter et gérer:</span>
            <p>{{ $projet['origineProjet']['contraintes'] ?? 'N/A' }}</p>
        </div>
    </div>

    <div class="section">
        <h2>Description sommaire de l'idée de projet</h2>
        <div class="field">
            <span class="label">Description générale du projet (Contexte & objectifs):</span>
            <p>{{ $projet['descriptionSommaire']['descriptionSommaire'] ?? 'N/A' }}</p>
        </div>
        <div class="field">
            <span class="label">Echéancier des principaux extrants (Indicateurs de réalisations physiques):</span>
            <p>{{ $projet['descriptionSommaire']['echeancierPrincipauxExtrants'][0]['indicateurRealisation'] ?? 'N/A' }}</p>
        </div>
        <div class="field">
            <span class="label">Description des principaux extrants (spécifications techniques):</span>
            <p>{{ $projet['descriptionSommaire']['descriptionsExtrants'][0] ?? 'N/A' }}</p>
        </div>
        <div class="field">
            <span class="label">Caractéristiques techniques du projet:</span>
            <p>{{ is_array($projet['descriptionSommaire']['caracteristiquesTechniques']) ? implode(', ', $projet['descriptionSommaire']['caracteristiquesTechniques']) : ($projet['descriptionSommaire']['caracteristiquesTechniques'] ?? 'N/A') }}</p>
        </div>
        <div class="field">
            <span class="label">Localisation, choix du ou des site(s) d'accueil et impact environnemental probable:</span>
            <p>
                @foreach($projet['zonesIntervention'] as $zone)
                    {{ $zone['code'] }}
                @endforeach
            </p>
        </div>
        <div class="field">
            <span class="label">Aspects organisationnels du projet:</span>
            <p>{{ $projet['descriptionSommaire']['aspectsOrganisationnels'] ?? 'N/A' }}</p>
        </div>
        <div class="field">
            <span class="label">Estimation des coûts et des bénéfices:</span>
            <p>Coût estimé: {{ number_format($projet['coutEstimatif'] ?? 0, 0, ',', ' ') }} XOF</p>
        </div>
        <div class="field">
            <span class="label">Risques immédiats:</span>
            <p>{{ is_array($projet['descriptionSommaire']['risquesImmediats']) ? implode(', ', $projet['descriptionSommaire']['risquesImmediats']) : ($projet['descriptionSommaire']['risquesImmediats'] ?? 'N/A') }}</p>
        </div>
        <div class="field">
            <span class="label">Conclusions et recommandations:</span>
            <p>{{ $projet['descriptionSommaire']['conclusionsRecommandations'] ?? 'N/A' }}</p>
        </div>
    </div>

</body>
</html>
