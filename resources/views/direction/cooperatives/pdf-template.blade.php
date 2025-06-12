<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Liste des Coopératives - SGCCL</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 10mm 15mm 10mm;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.2;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        
        .header h1 {
            color: #007bff;
            font-size: 16px;
            margin: 0 0 3px 0;
            font-weight: bold;
        }
        
        .header h2 {
            color: #6c757d;
            font-size: 12px;
            margin: 0;
            font-weight: normal;
        }
        
        .info-section {
            margin-bottom: 12px;
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .filter-info {
            background-color: #e7f3ff;
            border-left: 3px solid #007bff;
            padding: 6px 8px;
            margin-bottom: 12px;
            font-size: 8px;
        }
        
        .filter-info h4 {
            margin: 0 0 6px 0;
            color: #007bff;
            font-size: 10px;
        }
        
        .stats-container {
            display: flex;
            justify-content: space-around;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .stat-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 6px 4px;
            width: 18%;
        }
        
        .stat-number {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 2px;
        }
        
        .stat-label {
            font-size: 7px;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 7px;
        }
        
        th {
            background-color: #007bff;
            color: white;
            padding: 4px 3px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #0056b3;
            font-size: 7px;
        }
        
        td {
            padding: 3px 2px;
            border: 1px solid #dee2e6;
            vertical-align: top;
            word-wrap: break-word;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 6px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-actif {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-inactif {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .cooperative-name {
            font-weight: bold;
            color: #007bff;
            font-size: 8px;
        }
        
        .matricule {
            background-color: #6c757d;
            color: white;
            padding: 1px 2px;
            border-radius: 2px;
            font-size: 6px;
            font-weight: bold;
        }
        
        .responsable-info {
            font-size: 7px;
            line-height: 1.1;
        }
        
        .responsable-name {
            font-weight: bold;
            color: #495057;
        }
        
        .no-responsable {
            color: #dc3545;
            font-style: italic;
            font-size: 7px;
        }
        
        .membres-count {
            text-align: center;
            font-weight: bold;
            color: #17a2b8;
        }
        
        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 7px;
        }
        
        .generation-info {
            margin-top: 10px;
            font-size: 7px;
            color: #6c757d;
            text-align: right;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
            font-size: 10px;
        }
        
        .contact-info {
            font-size: 6px;
            color: #6c757d;
            line-height: 1.1;
        }
        
        .address {
            font-size: 7px;
            color: #495057;
            max-width: 80px;
            word-wrap: break-word;
            line-height: 1.1;
        }
        
        /* Responsive table columns for A4 */
        .col-matricule { width: 8%; }
        .col-nom { width: 18%; }
        .col-contact { width: 16%; }
        .col-adresse { width: 16%; }
        .col-responsable { width: 18%; }
        .col-statut { width: 8%; }
        .col-membres { width: 6%; }
        .col-date { width: 10%; }
        
        /* When members column is not included */
        .without-members .col-date { width: 16%; }
        
        /* Print optimizations */
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>SGCCL - Système de Gestion des Coopératives</h1>
        <h2>Liste des Coopératives Agricoles</h2>
    </div>

    <!-- Informations sur les filtres appliqués -->
    @if($filtres['statut'] || $filtres['responsable_filter'] || $filtres['responsable_nom'] || $filtres['date_debut'])
    <div class="filter-info">
        <h4>Filtres Appliqués :</h4>
        @if($filtres['statut'])
            <div><strong>Statut :</strong> {{ ucfirst($filtres['statut']) }}</div>
        @endif
        @if($filtres['responsable_filter'])
            <div><strong>Responsable :</strong> 
                {{ $filtres['responsable_filter'] === 'avec' ? 'Avec responsable assigné' : 'Sans responsable assigné' }}
            </div>
        @endif
        @if($filtres['responsable_nom'])
            <div><strong>Gestionnaire spécifique :</strong> {{ $filtres['responsable_nom'] }}</div>
        @endif
        @if($filtres['date_debut'] && $filtres['date_fin'])
            <div><strong>Période de création :</strong> 
                Du {{ \Carbon\Carbon::parse($filtres['date_debut'])->format('d/m/Y') }} 
                au {{ \Carbon\Carbon::parse($filtres['date_fin'])->format('d/m/Y') }}
            </div>
        @endif
    </div>
    @endif

    <!-- Statistiques récapitulatives -->
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-number">{{ $stats['total'] }}</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $stats['actives'] }}</div>
            <div class="stat-label">Actives</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $stats['inactives'] }}</div>
            <div class="stat-label">Inactives</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $stats['avec_responsable'] }}</div>
            <div class="stat-label">Avec Responsable</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $stats['sans_responsable'] }}</div>
            <div class="stat-label">Sans Responsable</div>
        </div>
    </div>

    @if($filtres['include_membres'])
    <div class="info-section">
        <strong>Informations supplémentaires :</strong> Le nombre de membres actifs est inclus pour chaque coopérative.
        <br><strong>Total des membres actifs :</strong> {{ $stats['total_membres'] }}
    </div>
    @endif

    <!-- Tableau des coopératives -->
    @if($cooperatives->count() > 0)
        <table class="{{ $filtres['include_membres'] ? '' : 'without-members' }}">
            <thead>
                <tr>
                    <th class="col-matricule">Matricule</th>
                    <th class="col-nom">Nom de la Coopérative</th>
                    <th class="col-contact">Contact</th>
                    <th class="col-adresse">Adresse</th>
                    <th class="col-responsable">Responsable</th>
                    <th class="col-statut">Statut</th>
                    @if($filtres['include_membres'])
                    <th class="col-membres">Membres</th>
                    @endif
                    <th class="col-date">Créée le</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cooperatives as $cooperative)
                <tr>
                    <!-- Matricule -->
                    <td class="col-matricule">
                        <span class="matricule">{{ $cooperative->matricule }}</span>
                    </td>

                    <!-- Nom de la coopérative -->
                    <td class="col-nom">
                        <div class="cooperative-name">{{ $cooperative->nom_cooperative }}</div>
                    </td>

                    <!-- Contact -->
                    <td class="col-contact contact-info">
                        <div><strong>Email :</strong></div>
                        <div style="margin-bottom: 2px;">{{ $cooperative->email }}</div>
                        <div><strong>Tél :</strong></div>
                        <div>{{ $cooperative->telephone }}</div>
                    </td>

                    <!-- Adresse -->
                    <td class="col-adresse">
                        <div class="address">{{ $cooperative->adresse }}</div>
                    </td>

                    <!-- Responsable -->
                    <td class="col-responsable">
                        @if($cooperative->responsable)
                            <div class="responsable-info">
                                <div class="responsable-name">{{ $cooperative->responsable->nom_complet }}</div>
                                <div style="margin: 1px 0;">{{ $cooperative->responsable->email }}</div>
                                <div style="margin: 1px 0;">{{ $cooperative->responsable->telephone }}</div>
                                <div><span class="matricule">{{ $cooperative->responsable->matricule }}</span></div>
                            </div>
                        @else
                            <div class="no-responsable">Aucun responsable assigné</div>
                        @endif
                    </td>

                    <!-- Statut -->
                    <td class="col-statut">
                        <span class="status-badge status-{{ $cooperative->statut }}">
                            {{ ucfirst($cooperative->statut) }}
                        </span>
                    </td>

                    <!-- Membres (si inclus) -->
                    @if($filtres['include_membres'])
                    <td class="col-membres membres-count">
                        {{ $cooperative->membresActifs->count() }}
                    </td>
                    @endif

                    <!-- Date de création -->
                    <td class="col-date" style="text-align: center;">
                        {{ $cooperative->created_at->format('d/m/Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <strong>Aucune coopérative trouvée</strong>
            <br>
            Aucun résultat ne correspond aux critères de recherche appliqués.
        </div>
    @endif

    <!-- Informations de génération -->
    <div class="generation-info">
        <div><strong>Rapport généré le :</strong> {{ $generated_at->format('d/m/Y à H:i:s') }}</div>
        <div><strong>Généré par :</strong> {{ $generated_by->nom_complet }} ({{ $generated_by->matricule }})</div>
        <div><strong>Total coopératives dans ce rapport :</strong> {{ $cooperatives->count() }}</div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <div>SGCCL - Système de Gestion des Coopératives Agricoles</div>
        <div>{{ now()->format('Y') }} - Rapport confidentiel réservé à un usage interne</div>
    </div>
</body>
</html>