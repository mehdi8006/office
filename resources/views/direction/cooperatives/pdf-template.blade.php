<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Liste des Coopératives - SGCCL</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #007bff;
            font-size: 24px;
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        
        .header h2 {
            color: #6c757d;
            font-size: 16px;
            margin: 0;
            font-weight: normal;
        }
        
        .info-section {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .filter-info {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        
        .filter-info h4 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 14px;
        }
        
        .stats-container {
            display: flex;
            justify-content: space-around;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .stat-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 12px;
            width: 18%;
        }
        
        .stat-number {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 3px;
        }
        
        .stat-label {
            font-size: 10px;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        
        th {
            background-color: #007bff;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #0056b3;
        }
        
        td {
            padding: 6px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #e7f3ff;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
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
        }
        
        .matricule {
            background-color: #6c757d;
            color: white;
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .responsable-info {
            font-size: 9px;
        }
        
        .responsable-name {
            font-weight: bold;
            color: #495057;
        }
        
        .no-responsable {
            color: #dc3545;
            font-style: italic;
        }
        
        .membres-count {
            text-align: center;
            font-weight: bold;
            color: #17a2b8;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 10px;
        }
        
        .generation-info {
            margin-top: 20px;
            font-size: 9px;
            color: #6c757d;
            text-align: right;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
        
        .contact-info {
            font-size: 9px;
            color: #6c757d;
        }
        
        .address {
            font-size: 9px;
            color: #495057;
            max-width: 120px;
            word-wrap: break-word;
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
        <table>
            <thead>
                <tr>
                    <th width="8%">Matricule</th>
                    <th width="20%">Nom de la Coopérative</th>
                    <th width="15%">Contact</th>
                    <th width="18%">Adresse</th>
                    <th width="18%">Responsable</th>
                    <th width="8%">Statut</th>
                    @if($filtres['include_membres'])
                    <th width="8%">Membres</th>
                    @endif
                    <th width="{{ $filtres['include_membres'] ? '5%' : '13%' }}">Créée le</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cooperatives as $cooperative)
                <tr>
                    <!-- Matricule -->
                    <td>
                        <span class="matricule">{{ $cooperative->matricule }}</span>
                    </td>

                    <!-- Nom de la coopérative -->
                    <td>
                        <div class="cooperative-name">{{ $cooperative->nom_cooperative }}</div>
                    </td>

                    <!-- Contact -->
                    <td class="contact-info">
                        <div><strong>Email :</strong></div>
                        <div>{{ $cooperative->email }}</div>
                        <div><strong>Tél :</strong></div>
                        <div>{{ $cooperative->telephone }}</div>
                    </td>

                    <!-- Adresse -->
                    <td>
                        <div class="address">{{ $cooperative->adresse }}</div>
                    </td>

                    <!-- Responsable -->
                    <td>
                        @if($cooperative->responsable)
                            <div class="responsable-info">
                                <div class="responsable-name">{{ $cooperative->responsable->nom_complet }}</div>
                                <div>{{ $cooperative->responsable->email }}</div>
                                <div>{{ $cooperative->responsable->telephone }}</div>
                                <div><span class="matricule">{{ $cooperative->responsable->matricule }}</span></div>
                            </div>
                        @else
                            <div class="no-responsable">Aucun responsable assigné</div>
                        @endif
                    </td>

                    <!-- Statut -->
                    <td>
                        <span class="status-badge status-{{ $cooperative->statut }}">
                            {{ ucfirst($cooperative->statut) }}
                        </span>
                    </td>

                    <!-- Membres (si inclus) -->
                    @if($filtres['include_membres'])
                    <td class="membres-count">
                        {{ $cooperative->membresActifs->count() }}
                    </td>
                    @endif

                    <!-- Date de création -->
                    <td style="text-align: center;">
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