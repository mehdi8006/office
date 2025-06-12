<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Historique des Paiements Quinzaines - {{ $cooperative->nom_cooperative }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #28a745;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #28a745;
            font-size: 20px;
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        
        .header h2 {
            color: #6c757d;
            font-size: 14px;
            margin: 0;
            font-weight: normal;
        }
        
        .cooperative-info {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .cooperative-info h3 {
            margin: 0 0 8px 0;
            color: #28a745;
            font-size: 12px;
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
            padding: 10px;
            width: 18%;
        }
        
        .stat-number {
            font-size: 16px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 3px;
        }
        
        .stat-label {
            font-size: 8px;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9px;
        }
        
        th {
            background-color: #28a745;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #1e7e34;
        }
        
        td {
            padding: 6px 5px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .quinzaine-label {
            font-weight: bold;
            color: #28a745;
        }
        
        .montant-highlight {
            font-weight: bold;
            color: #28a745;
        }
        
        .prix-unitaire {
            background-color: #e7f3ff;
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 8px;
        }
        
        .quantite-badge {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 9px;
        }
        
        .generation-info {
            margin-top: 20px;
            font-size: 8px;
            color: #6c757d;
            text-align: right;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>Historique des Paiements Quinzaines</h1>
        <h2>{{ $cooperative->nom_cooperative }}</h2>
    </div>

    <!-- Informations de la coopérative -->
    <div class="cooperative-info">
        <h3>Informations de la Coopérative</h3>
        <div>
            <strong>Matricule :</strong> {{ $cooperative->matricule }} | 
            <strong>Email :</strong> {{ $cooperative->email }} | 
            <strong>Téléphone :</strong> {{ $cooperative->telephone }}
        </div>
        <div>
            <strong>Adresse :</strong> {{ $cooperative->adresse }}
        </div>
        @if($cooperative->responsable)
            <div>
                <strong>Responsable :</strong> {{ $cooperative->responsable->nom_complet }} ({{ $cooperative->responsable->email }})
            </div>
        @endif
    </div>

    <!-- Statistiques récapitulatives -->
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-number">{{ $stats['total_quinzaines'] }}</div>
            <div class="stat-label">Quinzaines Payées</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ number_format($stats['total_quantite'], 1) }}L</div>
            <div class="stat-label">Quantité Totale</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ number_format($stats['total_montant'], 2) }}</div>
            <div class="stat-label">Montant Total (DH)</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ number_format($stats['prix_moyen'], 2) }}</div>
            <div class="stat-label">Prix Moyen (DH/L)</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $stats['premiere_quinzaine'] ? $stats['premiere_quinzaine']->format('m/Y') : '-' }}</div>
            <div class="stat-label">Première à</div>
            <div class="stat-label">{{ $stats['derniere_quinzaine'] ? $stats['derniere_quinzaine']->format('m/Y') : '-' }}</div>
        </div>
    </div>

    <!-- Tableau des quinzaines payées -->
    @if($paiements->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="18%">Quinzaine</th>
                    <th width="12%">Quantité</th>
                    <th width="12%">Prix Unitaire</th>
                    <th width="15%">Montant Total</th>
                    <th width="12%">Date Paiement</th>
                    <th width="12%">Calculé le</th>
                    <th width="19%">Période Couverte</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paiements as $paiement)
                <tr>
                    <!-- Quinzaine -->
                    <td>
                        <div class="quinzaine-label">{{ $paiement->quinzaine_label }}</div>
                    </td>

                    <!-- Quantité -->
                    <td>
                        <span class="quantite-badge">{{ number_format($paiement->quantite_litres, 2) }} L</span>
                    </td>

                    <!-- Prix unitaire -->
                    <td>
                        <span class="prix-unitaire">{{ number_format($paiement->prix_unitaire, 2) }} DH/L</span>
                    </td>

                    <!-- Montant total -->
                    <td>
                        <div class="montant-highlight">{{ number_format($paiement->montant, 2) }} DH</div>
                    </td>

                    <!-- Date paiement -->
                    <td style="text-align: center;">
                        {{ $paiement->date_paiement->format('d/m/Y') }}
                    </td>

                    <!-- Date calcul -->
                    <td style="text-align: center;">
                        {{ $paiement->created_at->format('d/m/Y') }}
                    </td>

                    <!-- Période couverte -->
                    <td style="font-size: 8px;">
                        @php
                            $debut = $paiement->date_paiement->day <= 15 ? 
                                $paiement->date_paiement->startOfMonth() : 
                                $paiement->date_paiement->startOfMonth()->addDays(15);
                            $fin = $paiement->date_paiement->day <= 15 ?
                                $paiement->date_paiement->startOfMonth()->addDays(14) :
                                $paiement->date_paiement->endOfMonth();
                        @endphp
                        {{ $debut->format('d/m/Y') }}<br>au<br>{{ $fin->format('d/m/Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Résumé financier -->
        <div style="margin-top: 20px; background-color: #f8f9fa; padding: 15px; border-radius: 5px;">
            <h6 style="color: #28a745; margin: 0 0 10px 0;">Résumé Financier</h6>
            <div style="display: flex; justify-content: space-between;">
                <div>
                    <strong>Total des paiements :</strong> {{ number_format($stats['total_montant'], 2) }} DH<br>
                    <strong>Quantité totale livrée :</strong> {{ number_format($stats['total_quantite'], 2) }} L<br>
                    <strong>Rendement moyen :</strong> {{ number_format($stats['total_montant'] / max($stats['total_quantite'], 1), 2) }} DH/L
                </div>
                <div>
                    <strong>Quinzaines payées :</strong> {{ $stats['total_quinzaines'] }}<br>
                    <strong>Prix moyen :</strong> {{ number_format($stats['prix_moyen'], 2) }} DH/L<br>
                    <strong>Moyenne par quinzaine :</strong> {{ number_format($stats['total_montant'] / max($stats['total_quinzaines'], 1), 2) }} DH
                </div>
            </div>
        </div>
    @else
        <div class="no-data">
            <strong>Aucune quinzaine payée trouvée</strong>
            <br>
            Aucun paiement n'a encore été effectué pour cette coopérative.
        </div>
    @endif

    <!-- Informations de génération -->
    <div class="generation-info">
        <div><strong>Rapport généré le :</strong> {{ now()->format('d/m/Y à H:i:s') }}</div>
        <div><strong>Généré par :</strong> {{ auth()->user()->nom_complet }} ({{ auth()->user()->matricule }})</div>
        <div><strong>Total quinzaines dans ce rapport :</strong> {{ $paiements->count() }}</div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <div>SGCCL - {{ $cooperative->nom_cooperative }}</div>
        <div>{{ now()->format('Y') }} - Historique des paiements quinzaines - Document confidentiel</div>
    </div>
</body>
</html>