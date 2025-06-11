@extends('gestionnaire.layouts.app')

@section('title', 'Livraisons vers l\'Usine')
@section('page-title', 'Livraisons vers l\'Usine')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('gestionnaire.stock.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-warehouse me-2"></i>Voir Stock
        </a>
        <a href="{{ route('gestionnaire.livraisons.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Nouvelle Livraison
        </a>
    </div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Livraisons</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_livraisons']) }}</h3>
                        <small class="text-muted">Période sélectionnée</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-truck text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Quantité Totale</h6>
                        <h3 class="mb-0 text-info">{{ number_format($stats['total_quantite'], 1) }}L</h3>
                        <small class="text-muted">Livrée à l'usine</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-tint text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Montant Total</h6>
                        <small class="text-muted">DH</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-money-bill text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Prix Moyen</h6>
                        <small class="text-muted">DH/L</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-chart-line text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-2">
        <div class="card border-left-warning h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-warning mb-1">{{ $stats['livraisons_planifiees'] }}</h4>
                <small class="text-muted">Planifiées</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="card border-left-info h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-info mb-1">{{ $stats['livraisons_validees'] }}</h4>
                <small class="text-muted">Validées</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="card border-left-success h-100">
            <div class="card-body p-3 text-center">
                <small class="text-muted">Payées</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>Filtres et Recherche
            </h5>
            <div class="d-flex align-items-center">
                <span class="text-muted me-2">Coopérative :</span>
                <span class="badge bg-primary fs-6">{{ $cooperative->nom_cooperative }}</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('gestionnaire.livraisons.index') }}" class="row g-3">
            <!-- Date Range -->
            <div class="col-md-3">
                <label for="date_debut" class="form-label">Date Début</label>
                <input type="date" 
                       class="form-control" 
                       id="date_debut" 
                       name="date_debut" 
                       value="{{ request('date_debut', now()->subDays(30)->format('Y-m-d')) }}">
            </div>

            <div class="col-md-3">
                <label for="date_fin" class="form-label">Date Fin</label>
                <input type="date" 
                       class="form-control" 
                       id="date_fin" 
                       name="date_fin" 
                       value="{{ request('date_fin', now()->format('Y-m-d')) }}">
            </div>

            <!-- Status Filter -->
            <div class="col-md-3">
                <label for="statut" class="form-label">Statut</label>
                <select class="form-select" id="statut" name="statut">
                    <option value="">Tous les statuts</option>
                    <option value="planifiee" {{ request('statut') === 'planifiee' ? 'selected' : '' }}>Planifiée</option>
                    <option value="validee" {{ request('statut') === 'validee' ? 'selected' : '' }}>Validée</option>
                    <option value="payee" {{ request('statut') === 'payee' ? 'selected' : '' }}>Payée</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('gestionnaire.livraisons.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Livraisons Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Liste des Livraisons
                <span class="badge bg-primary ms-2">{{ $livraisons->total() }} livraison(s)</span>
            </h5>
            
            <!-- Sort Options -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-sort me-1"></i>Trier par
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'date_livraison', 'sort_order' => 'desc']) }}">
                        <i class="fas fa-calendar-alt me-2"></i>Date (Plus récent)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'date_livraison', 'sort_order' => 'asc']) }}">
                        <i class="fas fa-calendar-alt me-2"></i>Date (Plus ancien)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'quantite_litres', 'sort_order' => 'desc']) }}">
                        <i class="fas fa-tint me-2"></i>Quantité (+ élevée)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'montant_total', 'sort_order' => 'desc']) }}">
                        <i class="fas fa-money-bill me-2"></i>Montant (+ élevé)
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        @if($livraisons->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Quantité</th>
                            <th>Prix Unitaire</th>
                            <th>Montant Total</th>
                            <th>Statut</th>
                            <th>Créée le</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($livraisons as $livraison)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $livraison->date_livraison->format('d/m/Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $livraison->date_livraison->translatedFormat('l') }}</small>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="fw-bold text-primary">{{ $livraison->quantite_formattee }}</span>
                                </td>
                                
                                <td>
                                    <span class="text-muted">{{ $livraison->prix_formattee }}</span>
                                </td>
                                
                                <td>
                                    <span class="fw-bold text-success">{{ $livraison->montant_formattee }}</span>
                                </td>
                                
                                <td>
                                    <span class="badge bg-{{ $livraison->statut_color }}">
                                        {{ $livraison->statut_label }}
                                    </span>
                                </td>
                                
                                <td>
                                    <div>
                                        <small>{{ $livraison->created_at->format('d/m/Y H:i') }}</small>
                                        <br>
                                        <small class="text-muted">{{ $livraison->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($livraison->statut === 'planifiee')
                                            <!-- Valider -->
                                            <form action="{{ route('gestionnaire.livraisons.validate', $livraison) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Valider cette livraison ?\n\nQuantité: {{ $livraison->quantite_formattee }}\nMontant: {{ $livraison->montant_formattee }}')">
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
                                                  onsubmit="return confirm('Supprimer cette livraison ?\n\nQuantité: {{ $livraison->quantite_formattee }}\nMontant: {{ $livraison->montant_formattee }}\n\nLe stock sera automatiquement restauré.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="Supprimer la livraison">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <div class="text-center w-100">
                                                @if($livraison->statut === 'validee')
                                                    <span class="text-info small">
                                                        <i class="fas fa-check-circle me-1"></i>Validée
                                                    </span>
                                                @elseif($livraison->statut === 'payee')
                                                    <span class="text-success small">
                                                        <i class="fas fa-money-bill me-1"></i>Payée
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Affichage de {{ $livraisons->firstItem() }} à {{ $livraisons->lastItem() }} 
                        sur {{ $livraisons->total() }} livraisons
                    </div>
                    <div>
                        {{ $livraisons->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-truck text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Aucune livraison trouvée</h5>
                <p class="text-muted">Aucune livraison ne correspond aux critères de recherche.</p>
                <a href="{{ route('gestionnaire.livraisons.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Créer la première livraison
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Quick Actions Card -->
@if($stats['livraisons_planifiees'] > 0)
<div class="card mt-4">
    <div class="card-header bg-warning text-dark">
        <h6 class="card-title mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>Actions Rapides
        </h6>
    </div>
    <div class="card-body">
        <div class="alert alert-warning mb-3">
            <i class="fas fa-info-circle me-2"></i>
            Vous avez <strong>{{ $stats['livraisons_planifiees'] }} livraison(s) planifiée(s)</strong> en attente de validation.
        </div>
        
        <div class="d-flex gap-2">
            <a href="{{ route('gestionnaire.livraisons.index', ['statut' => 'planifiee']) }}" 
               class="btn btn-warning">
                <i class="fas fa-list me-2"></i>Voir les livraisons planifiées
            </a>
            <a href="{{ route('gestionnaire.paiements.index') }}" class="btn btn-info">
                <i class="fas fa-money-bill me-2"></i>Gérer les paiements
            </a>
        </div>
    </div>
</div>
@endif
@endsection

<style>
.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}
.border-left-info {
    border-left: 4px solid #17a2b8 !important;
}
.border-left-success {
    border-left: 4px solid #28a745 !important;
}
</style>