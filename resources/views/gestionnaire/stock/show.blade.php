@extends('gestionnaire.layouts.app')

@section('title', 'Détails Stock - ' . $stock->date_stock->format('d/m/Y'))
@section('page-title')
    Stock du {{ $stock->date_stock->format('d/m/Y') }}
    <span class="badge bg-{{ $stock->statut_color }} ms-2">{{ $stock->statut_stock }}</span>
@endsection

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('gestionnaire.stock.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour au Stock
        </a>
        
        @if($stock->quantite_disponible > 0)
            <a href="{{ route('gestionnaire.livraisons.create', ['date' => $stock->date_stock->format('Y-m-d')]) }}" class="btn btn-success">
                <i class="fas fa-truck me-2"></i>Créer Livraison
            </a>
        @endif
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Left Column - Stock Information -->
    <div class="col-lg-4 mb-4">
        <!-- Stock Summary Card -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fas fa-warehouse me-2"></i>Résumé du Stock
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-warehouse text-white" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="mt-3 mb-1">{{ $stock->date_stock->format('d/m/Y') }}</h4>
                    <p class="text-muted">{{ $stock->date_stock->translatedFormat('l') }}</p>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted">Quantité Totale Reçue</label>
                        <div class="fw-bold fs-4 text-primary">
                            <i class="fas fa-tint me-2"></i>{{ $stock->quantite_totale_formattee }}
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted">Stock Disponible</label>
                        <div class="fw-bold fs-4 text-success">
                            <i class="fas fa-check-circle me-2"></i>{{ $stock->quantite_disponible_formattee }}
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted">Quantité Livrée</label>
                        <div class="fw-bold fs-4 text-warning">
                            <i class="fas fa-truck me-2"></i>{{ $stock->quantite_livree_formattee }}
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted">Coopérative</label>
                        <div class="fw-semibold">
                            <span class="badge bg-info fs-6">{{ $stock->cooperative->nom_cooperative }}</span>
                        </div>
                    </div>

                    @if($stock->quantite_totale > 0)
                        <div class="col-12">
                            <label class="form-label text-muted">Progression des Livraisons</label>
                            <div class="progress mb-2" style="height: 25px;">
                                <div class="progress-bar bg-warning" 
                                     style="width: {{ $stock->percentage_livre }}%"
                                     role="progressbar">
                                    {{ number_format($stock->percentage_livre, 1) }}%
                                </div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted">
                                <span>0%</span>
                                <span>{{ number_format($stock->percentage_livre, 1) }}% livré</span>
                                <span>100%</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions Card -->
        @if($stock->quantite_disponible > 0)
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h6 class="card-title mb-0">
                    <i class="fas fa-cogs me-2"></i>Actions Rapides
                </h6>
            </div>
            <div class="card-body">
                <a href="{{ route('gestionnaire.livraisons.create', ['date' => $stock->date_stock->format('Y-m-d')]) }}" 
                   class="btn btn-success w-100 mb-2">
                    <i class="fas fa-truck me-2"></i>Créer une Livraison
                </a>
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    {{ $stock->quantite_disponible_formattee }} disponibles pour livraison
                </small>
            </div>
        </div>
        @endif
    </div>

    <!-- Right Column - Details -->
    <div class="col-lg-8">
        <!-- Receptions Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>Réceptions du Jour
                    <span class="badge bg-primary ms-2">{{ $receptions->count() }} réception(s)</span>
                </h5>
            </div>
            <div class="card-body p-0">
                @if($receptions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Heure</th>
                                    <th>Matricule</th>
                                    <th>Membre</th>
                                    <th>Quantité</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($receptions as $reception)
                                    <tr>
                                        <td>
                                            <strong>{{ $reception->created_at->format('H:i') }}</strong>
                                        </td>
                                        <td>
                                            <code>{{ $reception->matricule_reception }}</code>
                                        </td>
                                        <td>
                                            <div>
                                                {{ $reception->membre->nom_complet }}
                                                <br>
                                                <small class="text-muted">{{ $reception->membre->numero_carte_nationale }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-primary fw-bold">{{ number_format($reception->quantite_litres, 2) }} L</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard text-muted" style="font-size: 3rem;"></i>
                        <h6 class="text-muted mt-3">Aucune réception ce jour</h6>
                        <p class="text-muted">Aucun membre n'a livré de lait ce jour-là.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Livraisons Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-truck me-2"></i>Livraisons vers l'Usine
                    <span class="badge bg-warning ms-2">{{ $livraisons->count() }} livraison(s)</span>
                </h5>
            </div>
            <div class="card-body p-0">
                @if($livraisons->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Heure</th>
                                    <th>Quantité</th>
                                    <th>Prix Unitaire</th>
                                    <th>Montant Total</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($livraisons as $livraison)
                                    <tr>
                                        <td>
                                            <strong>{{ $livraison->created_at->format('H:i') }}</strong>
                                        </td>
                                        <td>
                                            <span class="text-primary fw-bold">{{ $livraison->quantite_formattee }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $livraison->prix_formattee }}</span>
                                        </td>
                                        <td>
                                            <span class="text-success fw-bold">{{ $livraison->montant_formattee }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $livraison->statut_color }}">
                                                {{ $livraison->statut_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @if($livraison->statut === 'planifiee')
                                                    <!-- Valider -->
                                                    <form action="{{ route('gestionnaire.livraisons.validate', $livraison) }}" 
                                                          method="POST" 
                                                          style="display: inline;"
                                                          onsubmit="return confirm('Valider cette livraison ?')">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-success" 
                                                                title="Valider la livraison">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>

                                                    <!-- Supprimer -->
                                                    <form action="{{ route('gestionnaire.livraisons.destroy', $livraison) }}" 
                                                          method="POST" 
                                                          style="display: inline;"
                                                          onsubmit="return confirm('Supprimer cette livraison ?\n\nQuantité: {{ $livraison->quantite_formattee }}\nMontant: {{ $livraison->montant_formattee }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                title="Supprimer la livraison">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted small">
                                                        @if($livraison->statut === 'validee')
                                                            <i class="fas fa-check-circle text-success me-1"></i>Validée
                                                        @elseif($livraison->statut === 'payee')
                                                            <i class="fas fa-money-bill text-success me-1"></i>Payée
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Livraisons Summary -->
                    <div class="card-footer bg-light">
                        @php
                            $totalQuantiteLivree = $livraisons->sum('quantite_litres');
                            $totalMontantLivraisons = $livraisons->sum('montant_total');
                        @endphp
                        <div class="row text-center">
                            <div class="col-md-6">
                                <strong>Total Livré:</strong> 
                                <span class="text-primary">{{ number_format($totalQuantiteLivree, 2) }} L</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Montant Total:</strong> 
                                <span class="text-success">{{ number_format($totalMontantLivraisons, 2) }} DH</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-truck text-muted" style="font-size: 3rem;"></i>
                        <h6 class="text-muted mt-3">Aucune livraison</h6>
                        <p class="text-muted">Aucune livraison n'a été créée pour ce stock.</p>
                        @if($stock->quantite_disponible > 0)
                            <a href="{{ route('gestionnaire.livraisons.create', ['date' => $stock->date_stock->format('Y-m-d')]) }}" 
                               class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Créer la première livraison
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection