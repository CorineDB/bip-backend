<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Note Conceptuelle - {{ $projet->identifiant_bip }}</title>
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
            padding: 10px 0;
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
            padding-top: 10px;
        }

        .page-number:after {
            content: counter(page);
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
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            color: #2E74B5;
        }

        .cover-page .info-box {
            background: #f8f9fa;
            border: 2px solid #00a651;
            border-radius: 10px;
            padding: 20px;
            margin: 40px auto;
            width: 80%;
            text-align: left;
        }

        .info-box .info-row {
            margin: 10px 0;
            padding: 5px 0;
        }

        .info-box .label {
            font-weight: bold;
            color: #2E74B5;
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
        .content-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #2E74B5;
            margin: 30px 0 15px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #2E74B5;
            page-break-after: avoid;
        }

        .field-title {
            font-size: 14px;
            font-weight: bold;
            color: #2E74B5;
            margin: 20px 0 10px 0;
            page-break-after: avoid;
        }

        .field-content {
            text-align: justify;
            margin-bottom: 15px;
            line-height: 1.8;
            color: #333;
        }

        .field-content p {
            margin: 10px 0;
        }

        .empty-content {
            font-style: italic;
            color: #999;
        }

        /* Table pour les informations du projet */
        .project-info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .project-info-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .project-info-table .label-cell {
            width: 35%;
            background: #f8f9fa;
            font-weight: bold;
            color: #2E74B5;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <div style="float: left;">Note Conceptuelle</div>
        <div style="float: right;">{{ $projet->identifiant_bip }}</div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <div>
            Page <span class="page-number"></span>
        </div>
        <div style="font-size: 9px; margin-top: 5px;">
            Généré le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <!-- Page de garde -->
    <div class="cover-page">
        <div class="title">
            RÉPUBLIQUE DU BÉNIN
        </div>

        <div class="subtitle">
            NOTE CONCEPTUELLE
        </div>

        <div class="info-box">
            <div class="info-row">
                <span class="label">Titre du projet :</span><br>
                {{ $projet->titre_projet ?? 'N/A' }}
            </div>
            <div class="info-row">
                <span class="label">Identifiant BIP :</span><br>
                {{ $projet->identifiant_bip ?? 'N/A' }}
            </div>
            @if($projet->ministere)
            <div class="info-row">
                <span class="label">Ministère :</span><br>
                {{ $projet->ministere->nom }}
            </div>
            @endif
            @if($projet->secteur)
            <div class="info-row">
                <span class="label">Secteur :</span><br>
                {{ $projet->secteur->nom }}
            </div>
            @endif
        </div>

        <div style="margin-top: 60px; font-size: 12px; color: #666;">
            {{ now()->format('F Y') }}
        </div>
    </div>

    <!-- Table des matières -->
    <div class="toc-page">
        <div class="toc-title">SOMMAIRE</div>

        <div class="toc-container">
            @foreach($toc as $entry)
                <div class="toc-entry toc-entry-{{ $entry['level'] }}">
                    <span class="toc-text">{{ $entry['title'] }}</span>
                    <span class="toc-dots"></span>
                    <span class="toc-page-number">{{ $entry['page'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Informations générales du projet -->
    <div class="content-section">
        <div class="section-title">INFORMATIONS GÉNÉRALES</div>

        <table class="project-info-table">
            <tr>
                <td class="label-cell">Titre du projet</td>
                <td>{{ $projet->titre_projet ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label-cell">Identifiant BIP</td>
                <td>{{ $projet->identifiant_bip ?? 'N/A' }}</td>
            </tr>
            @if($projet->ministere)
            <tr>
                <td class="label-cell">Ministère</td>
                <td>{{ $projet->ministere->nom }}</td>
            </tr>
            @endif
            @if($projet->secteur)
            <tr>
                <td class="label-cell">Secteur</td>
                <td>{{ $projet->secteur->nom }}</td>
            </tr>
            @endif
            @if($projet->date_debut_etude)
            <tr>
                <td class="label-cell">Date de début</td>
                <td>
                    @if(is_string($projet->date_debut_etude))
                        {{ $projet->date_debut_etude }}
                    @else
                        {{ $projet->date_debut_etude->format('d/m/Y') }}
                    @endif
                </td>
            </tr>
            @endif
            @if($projet->date_fin_etude)
            <tr>
                <td class="label-cell">Date de fin</td>
                <td>
                    @if(is_string($projet->date_fin_etude))
                        {{ $projet->date_fin_etude }}
                    @else
                        {{ $projet->date_fin_etude->format('d/m/Y') }}
                    @endif
                </td>
            </tr>
            @endif
            @if(!empty($projet->cout_estimatif_projet))
            <tr>
                <td class="label-cell">Coût estimatif</td>
                <td>
                    @php
                        $coutEstimatif = is_array($projet->cout_estimatif_projet)
                            ? ($projet->cout_estimatif_projet['montant'] ?? '')
                            : $projet->cout_estimatif_projet;
                    @endphp
                    @if(!empty($coutEstimatif))
                        {{ number_format($coutEstimatif, 0, ',', ' ') }} FCFA
                    @endif
                </td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Contenu dynamique des sections -->
    @foreach($sections as $section)
        @if(!empty($section['title']))
            <div class="section-title">{{ strtoupper($section['title']) }}</div>
        @endif

        @foreach($section['fields'] as $field)
            <div class="content-section">
                <div class="field-title">{{ strtoupper($field['label']) }}</div>
                <div class="field-content">
                    @if(!empty($field['value']))
                        {!! nl2br(e($field['value'])) !!}
                    @else
                        <span class="empty-content">[Aucun contenu]</span>
                    @endif
                </div>
            </div>
        @endforeach
    @endforeach

</body>
</html>
