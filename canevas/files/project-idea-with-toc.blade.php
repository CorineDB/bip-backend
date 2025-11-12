<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fiche d'Idée de Projet - {{ $project->bip_number }}</title>
    <style>
        @page { 
            margin: 100px 50px; 
            size: A4 portrait;
        }
        
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 11px;
            line-height: 1.6;
            color: #333;
        }
        
        /* En-tête et pied de page */
        .header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 50px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        
        .footer {
            position: fixed;
            bottom: -70px;
            left: 0;
            right: 0;
            height: 50px;
            text-align: center;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        
        .page-number:after {
            content: counter(page);
        }
        
        .page-count:after {
            content: counter(pages);
        }
        
        /* Page de garde */
        .cover-page {
            text-align: center;
            page-break-after: always;
            padding-top: 100px;
        }
        
        .cover-page .logo {
            width: 150px;
            margin-bottom: 30px;
        }
        
        .cover-page .title {
            font-size: 28px;
            font-weight: bold;
            color: #00a651;
            margin: 40px 0;
        }
        
        .cover-page .subtitle {
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .cover-page .info-box {
            background: #f8f9fa;
            border: 2px solid #00a651;
            border-radius: 10px;
            padding: 20px;
            margin: 40px auto;
            width: 80%;
        }
        
        /* Table des matières */
        .toc-page {
            page-break-after: always;
        }
        
        .toc-title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 40px;
            color: #2E74B5;
        }
        
        .toc-container {
            width: 100%;
            margin: 0 auto;
        }
        
        .toc-entry {
            margin-bottom: 8px;
            position: relative;
            clear: both;
        }
        
        .toc-entry-1 {
            font-weight: bold;
            font-size: 13px;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        
        .toc-entry-2 {
            margin-left: 30px;
            font-size: 11px;
        }
        
        .toc-entry-3 {
            margin-left: 60px;
            font-size: 10px;
        }
        
        .toc-text {
            float: left;
            max-width: 85%;
        }
        
        .toc-dots {
            float: left;
            width: 10%;
            border-bottom: 1px dotted #999;
            margin: 0 5px;
            height: 15px;
        }
        
        .toc-page-number {
            float: right;
            text-align: right;
            font-weight: normal;
        }
        
        /* Styles du contenu */
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #2E74B5;
            margin-top: 30px;
            margin-bottom: 20px;
            padding: 10px;
            background: #f0f7ff;
            border-left: 4px solid #2E74B5;
            page-break-after: avoid;
        }
        
        .subsection-title {
            font-size: 14px;
            font-weight: bold;
            color: #2E74B5;
            margin-top: 20px;
            margin-bottom: 15px;
            page-break-after: avoid;
        }
        
        .subsubsection-title {
            font-size: 12px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 10px;
            page-break-after: avoid;
        }
        
        .field-box {
            border: 1px solid #ccc;
            min-height: 60px;
            padding: 10px;
            margin: 10px 0;
            background: #fafafa;
            border-radius: 4px;
            page-break-inside: avoid;
        }
        
        .field-label {
            font-style: italic;
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .warning-box {
            background: #e8f4fd;
            border: 1px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            page-break-inside: avoid;
        }
        
        .warning-box .title {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            page-break-inside: avoid;
        }
        
        table th {
            background: #f2f2f2;
            border: 1px solid #ddd;
            padding: 8px;
            font-weight: bold;
            text-align: left;
        }
        
        table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        
        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        
        .signature-table {
            width: 100%;
            border: none;
        }
        
        .signature-table td {
            border: none;
            width: 50%;
            text-align: center;
            padding: 20px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            width: 200px;
            margin: 30px auto 10px;
        }
        
        /* Numérotation automatique */
        .auto-number {
            counter-reset: section;
        }
        
        .numbered-section {
            counter-increment: section;
        }
        
        .numbered-section:before {
            content: counter(section) ". ";
        }
        
        /* Page break controls */
        .page-break {
            page-break-after: always;
        }
        
        .avoid-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <!-- En-tête pour toutes les pages sauf la première -->
    <div class="header">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; text-align: left;">Fiche d'Idée de Projet</td>
                <td style="border: none; text-align: right;">{{ $project->bip_number ?: 'Document de travail' }}</td>
            </tr>
        </table>
    </div>
    
    <!-- Pied de page pour toutes les pages -->
    <div class="footer">
        Page <span class="page-number"></span> sur <span class="page-count"></span>
    </div>
    
    <!-- Page de garde -->
    <div class="cover-page">
        <img src="{{ $logo_url }}" class="logo" alt="République du Bénin">
        
        <div class="subtitle">RÉPUBLIQUE DU BÉNIN</div>
        <div class="subtitle">MINISTÈRE DU DÉVELOPPEMENT ET DE LA COORDINATION</div>
        <div class="subtitle">DE L'ACTION GOUVERNEMENTALE</div>
        
        <div class="title">FICHE D'IDÉE DE PROJET</div>
        
        <div class="info-box">
            <h2 style="color: #00a651;">{{ $project->title }}</h2>
            <table style="margin: 20px auto; border: none;">
                <tr>
                    <td style="border: none; text-align: right; padding: 5px;"><strong>Numéro BIP :</strong></td>
                    <td style="border: none; text-align: left; padding: 5px;">{{ $project->bip_number ?: 'Non attribué' }}</td>
                </tr>
                <tr>
                    <td style="border: none; text-align: right; padding: 5px;"><strong>Structure de tutelle :</strong></td>
                    <td style="border: none; text-align: left; padding: 5px;">{{ $project->supervising_structure }}</td>
                </tr>
                <tr>
                    <td style="border: none; text-align: right; padding: 5px;"><strong>Coût estimé :</strong></td>
                    <td style="border: none; text-align: left; padding: 5px;">{{ number_format($project->project_cost, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr>
                    <td style="border: none; text-align: right; padding: 5px;"><strong>Date d'élaboration :</strong></td>
                    <td style="border: none; text-align: left; padding: 5px;">{{ now()->format('d/m/Y') }}</td>
                </tr>
            </table>
        </div>
    </div>
    
    <!-- Table des matières -->
    <div class="toc-page">
        <h1 class="toc-title">TABLE DES MATIÈRES</h1>
        
        <div class="toc-container">
            @foreach($toc as $section)
                <div class="toc-entry toc-entry-{{ $section['level'] }}">
                    <span class="toc-text">{{ $section['title'] }}</span>
                    <span class="toc-dots"></span>
                    <span class="toc-page-number">{{ $section['page'] }}</span>
                    <div style="clear: both;"></div>
                </div>
                
                @if(isset($section['children']))
                    @foreach($section['children'] as $subsection)
                        <div class="toc-entry toc-entry-{{ $subsection['level'] }}">
                            <span class="toc-text">{{ $subsection['title'] }}</span>
                            <span class="toc-dots"></span>
                            <span class="toc-page-number">{{ $subsection['page'] }}</span>
                            <div style="clear: both;"></div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>
    </div>
    
    <!-- Contenu principal -->
    <div class="content">
        <!-- Section 1: Origine du projet -->
        <h1 class="section-title">Origine du projet</h1>
        
        <h2 class="subsection-title">1. Titre du projet</h2>
        <div class="field-box">
            {{ $project->title }}
        </div>
        
        <h2 class="subsection-title">2. Origine du projet</h2>
        <div class="field-box">
            {{ $project->project_origin }}
        </div>
        
        <h2 class="subsection-title">3. Fondement</h2>
        <div class="field-label">(Action de la stratégie/Plan/Programme)</div>
        <div class="field-box">
            {{ $project->foundation }}
        </div>
        
        <h2 class="subsection-title">4. Situation actuelle</h2>
        <div class="field-label">(Problématique et/ou besoins)</div>
        <div class="field-box">
            {{ $project->current_situation }}
        </div>
        
        <h2 class="subsection-title">5. Situation désirée</h2>
        <div class="field-label">(Finalité, Buts)</div>
        <div class="field-box">
            {{ $project->desired_situation }}
        </div>
        
        <h2 class="subsection-title">6. Contraintes à respecter et gérer</h2>
        <div class="field-box">
            {{ $project->constraints }}
        </div>
        
        <div class="page-break"></div>
        
        <!-- Section 2: Description sommaire -->
        <h1 class="section-title">Description sommaire de l'idée de projet</h1>
        
        <h2 class="subsection-title">1. Description générale du projet</h2>
        <div class="field-label">(Contexte & objectifs)</div>
        <div class="field-box">
            {{ $project->general_description }}
        </div>
        
        <h2 class="subsection-title">2. Échéancier des principaux extrants</h2>
        <div class="field-label">(Indicateurs de réalisations physiques)</div>
        @if($project->outputs_schedule)
            <table>
                <thead>
                    <tr>
                        <th>Extrant</th>
                        <th>Date prévue</th>
                        <th>Indicateur</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(json_decode($project->outputs_schedule, true) as $output)
                        <tr>
                            <td>{{ $output['name'] ?? '' }}</td>
                            <td>{{ $output['date'] ?? '' }}</td>
                            <td>{{ $output['indicator'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="field-box">Aucun échéancier défini</div>
        @endif
        
        <h2 class="subsection-title">3. Description des principaux extrants</h2>
        <div class="field-label">(spécifications techniques)</div>
        @if($project->technical_specifications)
            @foreach(json_decode($project->technical_specifications, true) as $i => $spec)
                <h3 class="subsubsection-title">3.{{ $i + 1 }} {{ $spec['title'] ?? 'Extrant ' . ($i + 1) }}</h3>
                <div class="field-box">
                    {{ $spec['description'] ?? '' }}
                </div>
            @endforeach
        @else
            <div class="field-box">Spécifications à définir</div>
        @endif
        
        <h2 class="subsection-title">4. Caractéristiques techniques du projet</h2>
        <div class="warning-box">
            <div class="title">Erreurs fréquentes à éviter</div>
            La description des extrants du projet exige de sortir de la tendance à citer ses interventions ou activités. 
            Les variables économiques (revenu par habitant, emplois générés, consommation par habitant, etc.) doivent être mesurables.
            Il faut éviter de faire des affirmations vagues du genre : le projet favorisera le développement économique ou le bien-être social 
            dans la zone d'intervention.
        </div>
        <div class="field-box">
            {{ $project->technical_characteristics ?? '' }}
        </div>
        
        <h2 class="subsection-title">5. Localisation, choix du ou des site(s) d'accueil et impact environnemental probable</h2>
        <div class="field-box">
            {{ $project->location }}
        </div>
        
        <h2 class="subsection-title">6. Aspects organisationnels du projet</h2>
        <div class="field-box">
            {{ $project->organizational_aspects }}
        </div>
        
        <div class="page-break"></div>
        
        <!-- Section 3: Évaluation -->
        <h1 class="section-title">Évaluation et recommandations</h1>
        
        <h2 class="subsection-title">7. Estimation des coûts et des bénéfices</h2>
        @if($project->cost_benefits)
            @php
                $costBenefits = json_decode($project->cost_benefits, true);
            @endphp
            
            <h3 class="subsubsection-title">Estimation des coûts</h3>
            <table>
                <thead>
                    <tr>
                        <th>Poste de dépense</th>
                        <th>Montant (FCFA)</th>
                        <th>Pourcentage</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalCost = 0; @endphp
                    @foreach($costBenefits['costs'] ?? [] as $cost)
                        @php $totalCost += $cost['amount']; @endphp
                        <tr>
                            <td>{{ $cost['item'] }}</td>
                            <td style="text-align: right;">{{ number_format($cost['amount'], 0, ',', ' ') }}</td>
                            <td style="text-align: center;">{{ $cost['percentage'] }}%</td>
                        </tr>
                    @endforeach
                    <tr style="font-weight: bold; background: #e8e8e8;">
                        <td>TOTAL</td>
                        <td style="text-align: right;">{{ number_format($totalCost, 0, ',', ' ') }}</td>
                        <td style="text-align: center;">100%</td>
                    </tr>
                </tbody>
            </table>
            
            <h3 class="subsubsection-title">Bénéfices attendus</h3>
            <ul>
                @foreach($costBenefits['benefits'] ?? [] as $benefit)
                    <li>{{ $benefit }}</li>
                @endforeach
            </ul>
        @else
            <div class="field-box">À déterminer lors de l'étude de faisabilité</div>
        @endif
        
        <h2 class="subsection-title">8. Risques immédiats</h2>
        <div class="field-box">
            {{ $project->immediate_risks }}
        </div>
        
        <h2 class="subsection-title">9. Conclusions et recommandations</h2>
        <div class="field-box">
            {{ $project->conclusions }}
        </div>
        
        <!-- Solutions alternatives -->
        <h1 class="section-title">Autres solutions alternatives considérées et non retenues</h1>
        @if($project->alternative_solutions)
            <table>
                <thead>
                    <tr>
                        <th>Description sommaire</th>
                        <th>Principales raisons du rejet</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(json_decode($project->alternative_solutions, true) as $alternative)
                        <tr>
                            <td>{{ $alternative['description'] ?? '' }}</td>
                            <td>{{ $alternative['rejection_reason'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="field-box">Aucune solution alternative n'a été évaluée</div>
        @endif
        
        <!-- Signatures -->
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td>
                        <strong>Demandeur</strong>
                        <div class="signature-line"></div>
                        <small>Date et signature</small>
                    </td>
                    <td>
                        <strong>Responsable</strong>
                        <div class="signature-line"></div>
                        <small>Date et signature</small>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <!-- Script PHP pour numérotation des pages -->
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page {PAGE_NUM} sur {PAGE_COUNT}";
            $size = 10;
            $font = $fontMetrics->getFont("DejaVu Sans");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 35;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>
