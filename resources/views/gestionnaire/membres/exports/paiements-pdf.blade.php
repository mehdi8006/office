<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Historique des Paiements - {{ $membre->nom_complet }}</title>
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
            border-bottom: 2px solid #28a745;
            padding-bottom: 15px;
        }
        
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
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
            border-left: 4px solid #28a745;
            margin-bottom: 25px;
        }
        
        .member-info h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #28a745;
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
            width: 20%;
            text-align: center;
            padding: 10px;
        }
        
        .stats-number {
            font-size: 16px;
            font-weight: bold;
            color: #28a745;
            display: block;
        }
        
        .stats-label {
            font-size: 10px;
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
            background-color: #28a745;
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
        
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #333;
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
        
        .amount-highlight {
            background-color: #d4edda;
            font-weight: bold;
        }
        
        .pending-highlight {
            background-color: #fff3cd;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1 class="title">HISTORIQUE DES PAIEMENTS</h1>
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
        <h3>Résumé Financier</h3>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell">
                    <span class="stats-number">{{ number_format($stats['total_paiements']) }}</span>
                    <div class="stats-label">Total Paiements</div>
                </div>
                <div class="stats-cell">
                    <span class="stats-number">{{ number_format($stats['montant_total_paye'], 2) }} DH</span>
                    <div class="stats-label">Montant Payé</div>
                </div>
                <div class="stats-cell">
                    <span class="stats-number">{{ number_format($stats['montant_total_en_attente'], 2) }} DH</span>
                    <div class="stats-label">En Attente</div>
                </div>
                <div class="stats-cell">
                    <span class="stats-number">{{ number_format($stats['quantite_totale'], 2) }}L</span>
                    <div class="stats-label">Quantité Totale</div>
                </div>
                <div class="stats-cell">
                    <span class="stats-number">{{ number_format($stats['prix_moyen'], 2) }} DH/L</span>
                    <div class="stats-label">Prix Moyen</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    @if($paiements->count() > 0)
        <h3>Détail des Paiements</h3>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 20%;">Période</th>
                    <th style="width: 15%;">Quantité (L)</th>
                    <th style="width: 15%;">Prix Unit. (DH/L)</th>
                    <th style="width: 15%;">Montant (DH)</th>
                    <th style="width: 15%;">Statut</th>
                    <th style="width: 20%;">Date Paiement</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paiements as $paiement)
                    <tr class="{{ $paiement->statut === 'paye' ? 'amount-highlight' : 'pending-highlight' }}">
                        <td>{{ $paiement->periode_debut->format('d/m/Y') }} - {{ $paiement->periode_fin->format('d/m/Y') }}</td>
                        <td class="text-right">{{ number_format($paiement->quantite_totale, 2) }}</td>
                        <td class="text-right">{{ number_format($paiement->prix_unitaire, 2) }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($paiement->montant_total, 2) }}</td>
                        <td class="text-center">
                            <span class="badge badge-{{ $paiement->statut === 'paye' ? 'success' : 'warning' }}">
                                {{ $paiement->statut_label }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($paiement->date_paiement)
                                {{ $paiement->date_paiement->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Rows -->
        <table class="table" style="margin-top: 15px;">
            <tr style="background-color: #28a745; color: white;">
                <td class="font-weight-bold">TOTAL PAYÉ</td>
                <td class="text-right font-weight-bold">{{ number_format($paiements->where('statut', 'paye')->sum('quantite_totale'), 2) }} L</td>
                <td class="text-right font-weight-bold">-</td>
                <td class="text-right font-weight-bold">{{ number_format($stats['montant_total_paye'], 2) }} DH</td>
                <td colspan="2" class="text-center font-weight-bold">{{ $paiements->where('statut', 'paye')->count() }} paiement(s)</td>
            </tr>
            @if($stats['montant_total_en_attente'] > 0)
            <tr style="background-color: #ffc107; color: #333;">
                <td class="font-weight-bold">EN ATTENTE</td>
                <td class="text-right font-weight-bold">{{ number_format($paiements->where('statut', 'calcule')->sum('quantite_totale'), 2) }} L</td>
                <td class="text-right font-weight-bold">-</td>
                <td class="text-right font-weight-bold">{{ number_format($stats['montant_total_en_attente'], 2) }} DH</td>
                <td colspan="2" class="text-center font-weight-bold">{{ $paiements->where('statut', 'calcule')->count() }} paiement(s)</td>
            </tr>
            @endif
            <tr style="background-color: #007bff; color: white;">
                <td class="font-weight-bold">TOTAL GÉNÉRAL</td>
                <td class="text-right font-weight-bold">{{ number_format($stats['quantite_totale'], 2) }} L</td>
                <td class="text-right font-weight-bold">{{ number_format($stats['prix_moyen'], 2) }} DH/L</td>
                <td class="text-right font-weight-bold">{{ number_format($stats['montant_total_paye'] + $stats['montant_total_en_attente'], 2) }} DH</td>
                <td colspan="2" class="text-center font-weight-bold">{{ $stats['total_paiements'] }} paiement(s)</td>
            </tr>
        </table>

        @if($stats['premier_paiement'] && $stats['dernier_paiement'])
        <div style="margin-top: 20px; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #007bff;">
            <strong>Période d'activité :</strong> 
            Du {{ $stats['premier_paiement']->format('d/m/Y') }} 
            au {{ $stats['dernier_paiement']->format('d/m/Y') }}
        </div>
        @endif
    @else
        <div class="no-data">
            <p>Aucun paiement enregistré pour ce membre.</p>
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