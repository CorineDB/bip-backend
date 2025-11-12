<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fiche d'Idée de Projet - {{ $project->identifiant_bip }}</title>
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
                <td style="border: none; text-align: left; width: 50%; vertical-align: middle;">
                    <strong style="font-size: 16px; color: #00a651;">IDÉE DE PROJET</strong>
                </td>
                <td style="border: none; text-align: right; width: 50%; vertical-align: middle;">
                    <div style="text-align: right;">
                        <strong style="font-size: 9px;">MINISTÈRE DU DÉVELOPPEMENT ET DE LA COORDINATION</strong><br>
                        <strong style="font-size: 9px;">DE L'ACTION GOUVERNEMENTALE</strong><br>
                        <span style="font-size: 8px;">RÉPUBLIQUE DU BÉNIN</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Pied de page pour toutes les pages -->
    <div class="footer">
        Page <span class="page-number"></span> sur <span class="page-count"></span>
    </div>
    
    <!-- Page de garde -->
    <div class="cover-page">
        {{-- Logo temporarily disabled - requires PHP GD extension --}}
        {{-- <img src="{{ $logo_url }}" class="logo" alt="République du Bénin"> --}}

        <div class="subtitle">RÉPUBLIQUE DU BÉNIN</div>
        <div class="subtitle">MINISTÈRE DU DÉVELOPPEMENT ET DE LA COORDINATION</div>
        <div class="subtitle">DE L'ACTION GOUVERNEMENTALE</div>
        
        <div class="title">FICHE D'IDÉE DE PROJET</div>
        
        <div class="info-box">
            <h2 style="color: #00a651;">{{ $project->titre_projet }}</h2>
            <table style="margin: 20px auto; border: none;">
                <tr>
                    <td style="border: none; text-align: right; padding: 5px;"><strong>Numéro BIP :</strong></td>
                    <td style="border: none; text-align: left; padding: 5px;">{{ $project->identifiant_bip ?: 'Non attribué' }}</td>
                </tr>
                <tr>
                    <td style="border: none; text-align: right; padding: 5px;"><strong>Structure de tutelle :</strong></td>
                    <td style="border: none; text-align: left; padding: 5px;">{{ $project->ministere->nom ?? 'Non renseigné' }}</td>
                </tr>
                <tr>
                    <td style="border: none; text-align: right; padding: 5px;"><strong>Coût estimé :</strong></td>
                    <td style="border: none; text-align: left; padding: 5px;">{{ number_format(is_array($project->cout_estimatif_projet) ? ($project->cout_estimatif_projet['montant'] ?? 0) : 0, 0, ',', ' ') }} FCFA</td>
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
            {{ $project->titre_projet }}
        </div>
        
        <h2 class="subsection-title">2. Origine du projet</h2>
        <div class="field-box">
            {{ $project->origine }}
        </div>
        
        <h2 class="subsection-title">3. Fondement</h2>
        <div class="field-label">(Action de la stratégie/Plan/Programme)</div>

        <div class="field-box">
            {{-- ODD (Objectifs de Développement Durable) --}}
            @if(!empty($fondementData['odds']))
                <strong style="color: #2E74B5; font-size: 11px;">ODD (Objectifs de Développement Durable):</strong>
                <ul style="margin: 5px 0 10px 20px; padding-left: 0;">
                    @foreach($fondementData['odds'] as $odd)
                        <li style="font-size: 10px;">{{ $odd }}</li>
                    @endforeach
                </ul>
            @endif

            {{-- Cibles --}}
            @if(!empty($fondementData['cibles']))
                <strong style="color: #2E74B5; font-size: 11px;">Cibles:</strong>
                <ul style="margin: 5px 0 10px 20px; padding-left: 0;">
                    @foreach($fondementData['cibles'] as $cible)
                        <li style="font-size: 10px;">{{ $cible }}</li>
                    @endforeach
                </ul>
            @endif

            {{-- Plan National de Développement (PND) --}}
            @if(!empty($fondementData['informationsPND']['orientationsStrategiques']) || !empty($fondementData['informationsPND']['objectifsStrategiques']))
                <strong style="color: #2E74B5; font-size: 11px;">Informations PND (Plan National de Développement):</strong>

                @if(!empty($fondementData['informationsPND']['orientationsStrategiques']))
                    <div style="margin-left: 15px; margin-top: 5px;">
                        <em style="font-size: 10px;">Orientations stratégiques:</em>
                        <ul style="margin: 3px 0 5px 20px; padding-left: 0;">
                            @foreach($fondementData['informationsPND']['orientationsStrategiques'] as $orientation)
                                <li style="font-size: 9px;">{{ $orientation }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($fondementData['informationsPND']['objectifsStrategiques']))
                    <div style="margin-left: 15px; margin-top: 5px;">
                        <em style="font-size: 10px;">Objectifs stratégiques:</em>
                        <ul style="margin: 3px 0 10px 20px; padding-left: 0;">
                            @foreach($fondementData['informationsPND']['objectifsStrategiques'] as $objectif)
                                <li style="font-size: 9px;">{{ $objectif }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif

            {{-- Plan d'Action Gouvernemental (PAG) --}}
            @if(!empty($fondementData['informationsPAG']['piliersStrategiques']) || !empty($fondementData['informationsPAG']['axes']) || !empty($fondementData['informationsPAG']['actions']))
                <strong style="color: #2E74B5; font-size: 11px;">Informations PAG (Plan d'Action Gouvernemental):</strong>

                @if(!empty($fondementData['informationsPAG']['piliersStrategiques']))
                    <div style="margin-left: 15px; margin-top: 5px;">
                        <em style="font-size: 10px;">Piliers stratégiques:</em>
                        <ul style="margin: 3px 0 5px 20px; padding-left: 0;">
                            @foreach($fondementData['informationsPAG']['piliersStrategiques'] as $pilier)
                                <li style="font-size: 9px;">{{ $pilier }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($fondementData['informationsPAG']['axes']))
                    <div style="margin-left: 15px; margin-top: 5px;">
                        <em style="font-size: 10px;">Axes:</em>
                        <ul style="margin: 3px 0 5px 20px; padding-left: 0;">
                            @foreach($fondementData['informationsPAG']['axes'] as $axe)
                                <li style="font-size: 9px;">{{ $axe }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($fondementData['informationsPAG']['actions']))
                    <div style="margin-left: 15px; margin-top: 5px;">
                        <em style="font-size: 10px;">Actions:</em>
                        <ul style="margin: 3px 0 5px 20px; padding-left: 0;">
                            @foreach($fondementData['informationsPAG']['actions'] as $action)
                                <li style="font-size: 9px;">{{ $action }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
        </div>
        
        <h2 class="subsection-title">4. Situation actuelle</h2>
        <div class="field-label">(Problématique et/ou besoins)</div>
        <div class="field-box">
            {{ $project->situation_actuelle }}
        </div>
        
        <h2 class="subsection-title">5. Situation désirée</h2>
        <div class="field-label">(Finalité, Buts)</div>
        <div class="field-box">
            {{ $project->situation_desiree }}
        </div>
        
        <h2 class="subsection-title">6. Contraintes à respecter et gérer</h2>
        <div class="field-box">
            {{ $project->contraintes }}
        </div>
        
        <div class="page-break"></div>
        
        <!-- Section 2: Description sommaire -->
        <h1 class="section-title">Description sommaire de l'idée de projet</h1>
        
        <h2 class="subsection-title">1. Description générale du projet</h2>
        <div class="field-label">(Contexte & objectifs)</div>
        <div class="field-box">
            {{ $project->description_projet }}
        </div>
        
        <h2 class="subsection-title">2. Échéancier des principaux extrants</h2>
        <div class="field-label">(Indicateurs de réalisations physiques)</div>
        <div class="field-box">
            @if(is_array($project->echeancier))
                @foreach($project->echeancier as $item)
                    {{ $item }}<br>
                @endforeach
            @elseif(is_string($project->echeancier))
                {{ $project->echeancier }}
            @else
                Aucun échéancier défini
            @endif
        </div>
        
        <h2 class="subsection-title">3. Description des principaux extrants</h2>
        <div class="field-label">(spécifications techniques)</div>
        <div class="field-box">
            @if(is_array($project->description_extrants))
                @foreach($project->description_extrants as $i => $item)
                    <strong>{{ $i + 1 }}.</strong> {{ $item }}<br><br>
                @endforeach
            @elseif(is_string($project->description_extrants))
                {{ $project->description_extrants }}
            @else
                Spécifications à définir
            @endif
        </div>
        
        <h2 class="subsection-title">4. Caractéristiques techniques du projet</h2>
        <div class="warning-box">
            <div class="title">Erreurs fréquentes à éviter</div>
            La description des extrants du projet exige de sortir de la tendance à citer ses interventions ou activités. 
            Les variables économiques (revenu par habitant, emplois générés, consommation par habitant, etc.) doivent être mesurables.
            Il faut éviter de faire des affirmations vagues du genre : le projet favorisera le développement économique ou le bien-être social 
            dans la zone d'intervention.
        </div>
        <div class="field-box">
            {{ $project->caracteristiques ?? '' }}
        </div>
        
        <h2 class="subsection-title">5. Localisation, choix du ou des site(s) d'accueil et impact environnemental probable</h2>
        <div class="field-box">
            {{ $project->impact_environnement }}
        </div>
        
        <h2 class="subsection-title">6. Aspects organisationnels du projet</h2>
        <div class="field-box">
            {{ $project->aspect_organisationnel }}
        </div>
        
        <div class="page-break"></div>
        
        <!-- Section 3: Évaluation -->
        <h1 class="section-title">Évaluation et recommandations</h1>
        
        <h2 class="subsection-title">7. Estimation des coûts et des bénéfices</h2>
        @if($project->estimation_couts)
            @php
                $costBenefits = json_decode($project->estimation_couts, true);
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
            {{ $project->risques_immediats }}
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
