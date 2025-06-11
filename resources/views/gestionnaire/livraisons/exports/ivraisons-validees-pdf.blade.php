<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des Livraisons Validées</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #0066cc;
        }
        
        .header h1 {
            color: #0066cc;
            font-size: 20px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header h2 {
            color: #666;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .header .periode {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-weight: bold;
            color: #495057;
        }
        
        /* Info Section */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        
        .info-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #28a745;
        }
        
        .info-box h3 {
            color: #28a745;
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .info-item {
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            color: #495057;
        }
        
        .info-value {
            color: #212529;
        }
        
        /* Statistics Cards */
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        
        .stat-card {
            display: table-cell;
            width: 25%;
            text-align: center;
            background: #fff;
            border: 1px solid #dee2e6;
            padding: 15px 10px;
            vertical-align: top;
        }
        
        .stat-card:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }
        
        .stat-card:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 10px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #fff;
        }
        
        .table-header {
            font-size: 14px;
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        th {
            background: #f8f9fa;
            color: #495057;
            font-weight: bold;
            padding: 12px 8px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-size: 11px;
        }
        
        td {
            padding: 10px 8px;
            border: 1px solid #dee2e6;
            font-size: 11px;
        }
        
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        tr:hover {
            background: #e9ecef;
        }
        
        /* Text alignments */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        /* Colors */
        .text-success { color: #28a745; }
        .text-primary { color: #0066cc; }
        .text-danger { color: #dc3545; }
        .text-warning { color: #ffc107; }
        .text-muted { color: #6c757d; }
        
        /* Summary Section */
        .summary-section {
            margin-top: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #0066cc;
        }
        
        .summary-title {
            font-size: 14px;
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 15px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
        }
        
        .summary-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 10px;
        }
        
        .summary-item-value {
            font-size: 16px;
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }
        
        .summary-item-label {
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 10px;
        }
        
        /* Page break */
        .page-break {
            page-break-after: always;
        }
        
        /* Responsive adjustments for PDF */
        @media print {
            .container {
                max-width: none;
                margin: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Rapport des Livraisons Validées</h1>
            <h2>{{ $cooperative->nom_cooperative }}</h2>
            <div class="periode">
                Période: {{ $stats['periode_debut']->format('d/m/Y') }} - {{ $stats['periode_fin']->format('d/m/Y') }}
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-left">
                <div class="info-box">
                    <h3>Informations Coopérative</h3>
                    <div class="info-item">
                        <span class="info-label">Nom:</span> 
                        <span class="info-value">{{ $cooperative->nom_cooperative }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Matricule:</span> 
                        <span class="info-value">{{ $cooperative->matricule }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span> 
                        <span class="info-value">{{ $cooperative->email }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Téléphone:</span> 
                        <span class="info-value">{{ $cooperative->telephone }}</span>
                    </div>
                </div>
            </div>
            <div class="info-right">
                <div class="info-box">
                    <h3>Informations Rapport</h3>
                    <div class="info-item">
                        <span class="info-label">Date de génération:</span> 
                        <span class="info-value">{{ now()->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Type de rapport:</span> 
                        <span class="info-value">{{ $inclureDetails ? 'Détaillé' : 'Résumé' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Nombre de livraisons:</span> 
                        <span class="info-value">{{ $stats['total_livraisons'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $stats['total_livraisons'] }}</div>
                <div class="stat-label">Livraisons</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ number_format($stats['total_quantite'], 2) }} L</div>
                <div class="stat-label">Quantité Totale</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ number_format($stats['quantite_moyenne'], 2) }} L</div>
                <div class="stat-label">Quantité Moyenne</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ number_format($stats['total_montant'], 2) }} DH</div>
                <div class="stat-label">Montant Total</div>
            </div>
        </div>

        @if($inclureDetails)
            <!-- Detailed Table -->
            <div class="table-header">
                <i class="fas fa-list"></i> Détails des Livraisons Validées
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th width="15%">Date Livraison</th>
                        <th width="15%">Quantité (L)</th>
                        <th width="15%">Montant (DH)</th>
                        <th width="20%">Date Validation</th>
                        <th width="35%">Observations</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($livraisons as $livraison)
                        <tr>
                            <td class="text-center">{{ $livraison->date_livraison->format('d/m/Y') }}</td>
                            <td class="text-right">{{ number_format($livraison->quantite_litres, 2) }}</td>
                            <td class="text-right">{{ number_format($livraison->quantite_litres * 3.50, 2) }}</td>
                            <td class="text-center">{{ $livraison->updated_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center text-success">Livraison validée</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <!-- Summary Table -->
            <div class="table-header">
                <i class="fas fa-chart-bar"></i> Résumé par Date
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th width="25%">Date</th>
                        <th width="20%">Nb Livraisons</th>
                        <th width="25%">Quantité Totale (L)</th>
                        <th width="30%">Montant Total (DH)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($livraisonsGroupees as $groupe)
                        <tr>
                            <td class="text-center">{{ $groupe['date']->format('d/m/Y') }}</td>
                            <td class="text-center">{{ $groupe['nombre_livraisons'] }}</td>
                            <td class="text-right">{{ number_format($groupe['quantite_totale'], 2) }}</td>
                            <td class="text-right">{{ number_format($groupe['montant_total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-title">Résumé de la Période</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-item-value">{{ $stats['total_livraisons'] }}</div>
                    <div class="summary-item-label">Total Livraisons</div>
                </div>
                <div class="summary-item">
                    <div class="summary-item-value">{{ number_format($stats['total_quantite'], 2) }} L</div>
                    <div class="summary-item-label">Total Quantité</div>
                </div>
                <div class="summary-item">
                    <div class="summary-item-value">{{ number_format($stats['total_montant'], 2) }} DH</div>
                    <div class="summary-item-label">Total Montant</div>
                </div>
            </div>
            
            @if($stats['premiere_livraison'] && $stats['derniere_livraison'])
            <div style="margin-top: 15px; text-align: center; color: #6c757d; font-size: 11px;">
                <strong>Première livraison:</strong> {{ $stats['premiere_livraison']->format('d/m/Y') }} | 
                <strong>Dernière livraison:</strong> {{ $stats['derniere_livraison']->format('d/m/Y') }}
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Document généré le {{ now()->format('d/m/Y à H:i') }} | Système de Gestion Laitière</p>
            <p>Ce document contient des informations confidentielles de {{ $cooperative->nom_cooperative }}</p>
        </div>
    </div>
</body>
</html>