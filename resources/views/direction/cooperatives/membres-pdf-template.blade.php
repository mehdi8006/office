<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Liste des Membres - {{ $cooperative->nom_cooperative }}</title>
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
            font-size: 22px;
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        
        .header h2 {
            color: #6c757d;
            font-size: 16px;
            margin: 0;
            font-weight: normal;
        }
        
        .cooperative-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .cooperative-info h3 {
            margin: 0 0 10px 0;
            color: #28a745;
            font-size: 14px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
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
            width: 22%;
        }
        
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 3px;
        }
        
        .stat-label {
            font-size: 9px;
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
        
        tr:hover {
            background-color: #e7f3ff;
        }
        
        .status-badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
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
        
        .status-suppression {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .membre-name {
            font-weight: bold;
            color: #28a745;
        }
        
        .contact-info {
            font-size: 8px;
            color: #6c757d;
        }
        
        .address {
            font-size: 8px;
            color: #495057;
            max-width: 100px;
            word-wrap: break-word;
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
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
        
        .carte-nationale {
            font-size: 8px;
            background-color: #e9ecef;
            padding: 1px 3px;
            border-radius: 2px;
            color: #495057;
        }
        
        .raison-suppression {
            font-size: 8px;
            color: #dc3545;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>SGCCL - Liste des Membres Éleveurs</h1>
        <h2>{{ $cooperative->nom_cooperative }}</h2>
    </div>

    <!-- Informations de la coopérative -->
    <div class="cooperative-info">
        <h3>Informations de la Coopérative</h3>
        <div class="info-grid">
            <div>
                <strong>Matricule :</strong> {{ $cooperative->matricule }}<br>
                <strong>Email :</strong> {{ $cooperative->email }}<br>
                <strong>Téléphone :</strong> {{ $cooperative->telephone }}
            </div>
            <div>
                <strong>Adresse :</strong> {{ $cooperative->adresse }}<br>
                <strong>Statut :</strong> {{ ucfirst($cooperative->statut) }}<br>
                @if($cooperative->responsable)
                    <strong>Responsable :</strong> {{ $cooperative->responsable->nom_complet }}
                @else
                    <strong>Responsable :</strong> Non assigné
                @endif
            </div>
        </div>
    </div>

    <!-- Statistiques récapitulatives -->
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-number">{{ $stats['total_membres'] }}</div>
            <div class="stat-label">Total Membres</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $stats['membres_actifs'] }}</div>
            <div class="stat-label">Actifs</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $stats['membres_inactifs'] }}</div>
            <div class="stat-label">Inactifs</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $stats['membres_supprimes'] }}</div>
            <div class="stat-label">Supprimés</div>
        </div>
    </div>

    <!-- Tableau des membres -->
    @if($membres->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="18%">Nom Complet</th>
                    <th width="14%">Contact</th>
                    <th width="16%">Adresse</th>
                    <th width="12%">Carte Nationale</th>
                    <th width="8%">Statut</th>
                    <th width="10%">Inscrit le</th>
                    <th width="22%">Observations</th>
                </tr>
            </thead>
            <tbody>
                @foreach($membres as $membre)
                <tr>
                    <!-- Nom complet -->
                    <td>
                        <div class="membre-name">{{ $membre->nom_complet }}</div>
                    </td>

                    <!-- Contact -->
                    <td class="contact-info">
                        <div><strong>Email :</strong></div>
                        <div>{{ $membre->email }}</div>
                        <div><strong>Tél :</strong></div>
                        <div>{{ $membre->telephone }}</div>
                    </td>

                    <!-- Adresse -->
                    <td>
                        <div class="address">{{ $membre->adresse }}</div>
                    </td>

                    <!-- Numéro carte nationale -->
                    <td>
                        <span class="carte-nationale">{{ $membre->numero_carte_nationale }}</span>
                    </td>

                    <!-- Statut -->
                    <td>
                        <span class="status-badge status-{{ $membre->statut }}">
                            {{ ucfirst($membre->statut) }}
                        </span>
                    </td>

                    <!-- Date d'inscription -->
                    <td style="text-align: center;">
                        {{ $membre->created_at->format('d/m/Y') }}
                    </td>

                    <!-- Observations -->
                    <td>
                        @if($membre->statut === 'suppression' && $membre->raison_suppression)
                            <div class="raison-suppression">
                                <strong>Raison suppression :</strong><br>
                                {{ $membre->raison_suppression }}
                            </div>
                        @elseif($membre->statut === 'inactif')
                            <div class="contact-info">Membre désactivé</div>
                        @else
                            <div class="contact-info">Membre en règle</div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <strong>Aucun membre trouvé</strong>
            <br>
            Cette coopérative n'a encore aucun membre inscrit.
        </div>
    @endif

    <!-- Informations de génération -->
    <div class="generation-info">
        <div><strong>Rapport généré le :</strong> {{ $generated_at->format('d/m/Y à H:i:s') }}</div>
        <div><strong>Généré par :</strong> {{ $generated_by->nom_complet }} ({{ $generated_by->matricule }})</div>
        <div><strong>Total membres dans ce rapport :</strong> {{ $membres->count() }}</div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <div>SGCCL - {{ $cooperative->nom_cooperative }}</div>
        <div>{{ now()->format('Y') }} - Rapport confidentiel réservé à un usage interne</div>
    </div>
</body>
</html>