<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Historique des Réceptions - {{ $membre->nom_complet }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }
        
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
            margin: 0;
        }
        
        .subtitle {
            font-size: 14px;
            color: #666;
            margin: 5px 0 0 0;
        }
        
        .member-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin-bottom: 25px;
        }
        
        .member-info h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #007bff;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 30%;
            font-weight: bold;
            padding: 3px 0;
        }
        
        .info-value {
            display: table-cell;
            padding: 3px 0;
        }
        
        .stats-section {
            background-color: #e9ecef;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 5px;
        }
        
        .stats-section h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #333;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stats-cell {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 10px;
        }
        
        .stats-number {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            display: block;
        }
        
        .stats-label {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            font-size: 11px;
        }
        
        .table td {
            font-size: 11px;
        }
        
        .table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .font-weight-bold {
            font-weight: bold;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1 class="title">HISTORIQUE DES RÉCEPTIONS DE LAIT</h1>
        <p class="subtitle">Rapport généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <!-- Member Information -->
    <div class="member-info">
        <h3>Informations du Membre</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nom complet :</div>
                <div class="info-value">{{ $membre->nom_complet }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Numéro CIN :</div>
                <div class="info-value">{{ $membre->numero_carte_nationale }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email :</div>
                <div class="info-value">{{ $membre->email }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Téléphone :</div>
                <div class="info-value">{{ $membre->telephone }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Coopérative :</div>
                <div class="info-value">{{ $membre->cooperative->nom_cooperative }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Statut :</div>
                <div class="info-value">{{ $membre->statut_label }}</div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-section">
        <h3>Résumé Statistique</h3>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell">
                    <span class="stats-number">{{ number_format($stats['total_receptions']) }}</span>
                    <div class="stats-label">Total Réceptions</div>
                </div>
                <div class="stats-cell">
                    <span class="stats-number">{{ number_format($stats['total_quantite'], 2) }}L</span>
                    <div class="stats-label">Quantité Totale</div>
                </div>
                <div class="stats-cell">
                    <span class="stats-number">{{ number_format($stats['moyenne_quantite'], 2) }}L</span>
                    <div class="stats-label">Moyenne par Réception</div>
                </div>
                <div class="stats-cell">
                    <span class="stats-number">
                        @if($stats['premiere_reception'])
                            {{ $stats['premiere_reception']->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </span>
                    <div class="stats-label">Première Réception</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Receptions Table -->
    @if($receptions->count() > 0)
        <h3>Détail des Réceptions</h3>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 15%;">Date</th>
                    <th style="width: 20%;">Matricule</th>
                    <th style="width: 15%;">Quantité (L)</th>
                    <th style="width: 25%;">Coopérative</th>
                    <th style="width: 25%;">Heure d'enregistrement</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receptions as $reception)
                    <tr>
                        <td>{{ $reception->date_reception->format('d/m/Y') }}</td>
                        <td class="font-weight-bold">{{ $reception->matricule_reception }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($reception->quantite_litres, 2) }}</td>
                        <td>{{ $reception->cooperative->nom_cooperative }}</td>
                        <td class="text-center">{{ $reception->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Row -->
        <table class="table" style="margin-top: 15px;">
            <tr style="background-color: #007bff; color: white;">
                <td colspan="2" class="font-weight-bold">TOTAL GÉNÉRAL</td>
                <td class="text-right font-weight-bold">{{ number_format($stats['total_quantite'], 2) }} L</td>
                <td colspan="2" class="text-center font-weight-bold">{{ $stats['total_receptions'] }} réception(s)</td>
            </tr>
        </table>
    @else
        <div class="no-data">
            <p>Aucune réception enregistrée pour ce membre.</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>
            Document généré automatiquement par le système de gestion des coopératives laitières<br>
            Date de génération : {{ now()->format('d/m/Y à H:i:s') }}
        </p>
    </div>
</body>
</html>