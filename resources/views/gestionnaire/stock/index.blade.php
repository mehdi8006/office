@extends('gestionnaire.layouts.app')

@section('title', 'Gestion du Stock')
@section('page-title', 'Gestion du Stock')

@section('page-actions')
    <a href="{{ route('gestionnaire.livraisons.index') }}" class="btn btn-primary">
        <i class="fas fa-truck me-2"></i>Livraisons Usine
    </a>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Jours</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_jours']) }}</h3>
                        <small class="text-success">{{ number_format($stats['jours_avec_stock']) }} avec stock</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-calendar-alt text-primary" style="font-size: 2rem;"></i>
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
                        <h6 class="text-muted mb-1">Stock Total</h6>
                        <h3 class="mb-0 text-info">{{ number_format($stats['total_quantite'], 1) }}L</h3>
                        <small class="text-muted">Période sélectionnée</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-warehouse text-info" style="font-size: 2rem;"></i>
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
                        <h6 class="text-muted mb-1">Stock Disponible</h6>
                        <h3 class="mb-0 text-success">{{ number_format($stats['total_disponible'], 1) }}L</h3>
                        <small class="text-muted">Non livré</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
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
                        <h6 class="text-muted mb-1">Taux Livraison</h6>
                        <h3 class="mb-0 text-warning">{{ number_format($stats['taux_livraison'], 1) }}%</h3>
                        <small class="text-muted">{{ number_format($stats['total_livree'], 1) }}L livrés</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-truck text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-2">
        <div class="card border-left-warning h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-warning mb-1">{{ $stats['stocks_non_livres'] }}</h4>
                <small class="text-muted">Non Livrés</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card border-left-info h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-info mb-1">{{ $stats['stocks_partiellement_livres'] }}</h4>
                <small class="text-muted">Partiellement Livrés</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card border-left-success h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-success mb-1">{{ $stats['stocks_entierement_livres'] }}</h4>
                <small class="text-muted">Entièrement Livrés</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card border-left-secondary h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-secondary mb-1">{{ $stats['total_jours'] - $stats['jours_avec_stock'] }}</h4>
                <small class="text-muted">Sans Stock</small>
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
        <form method="GET" action="{{ route('gestionnaire.stock.index') }}" class="row g-3">
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
                <label for="statut_stock" class="form-label">Statut Stock</label>
                <select class="form-select" id="statut_stock" name="statut_stock">
                    <option value="">Tous les statuts</option>
                    <option value="non_livre" {{ request('statut_stock') === 'non_livre' ? 'selected' : '' }}>Non Livré</option>
                    <option value="partiellement_livre" {{ request('statut_stock') === 'partiellement_livre' ? 'selected' : '' }}>Partiellement Livré</option>
                    <option value="entierement_livre" {{ request('statut_stock') === 'entierement_livre' ? 'selected' : '' }}>Entièrement Livré</option>
                    <option value="stock_vide" {{ request('statut_stock') === 'stock_vide' ? 'selected' : '' }}>Sans Stock</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Filtrer
                    </button>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="avec_stock_seulement" name="avec_stock_seulement" {{ request('avec_stock_seulement') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="avec_stock_seulement">
                            Avec stock seulement
                        </label>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Stock Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Stock Quotidien
                <span class="badge bg-primary ms-2">{{ $stocks->total() }} jour(s)</span>
            </h5>
            
            <!-- Sort Options -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-sort me-1"></i>Trier par
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'date_stock', 'sort_order' => 'desc']) }}">
                        <i class="fas fa-calendar-alt me-2"></i>Date (Plus récent)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'date_stock', 'sort_order' => 'asc']) }}">
                        <i class="fas fa-calendar-alt me-2"></i>Date (Plus ancien)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'quantite_totale', 'sort_order' => 'desc']) }}">
                        <i class="fas fa-tint me-2"></i>Quantité (+ élevée)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'quantite_disponible', 'sort_order' => 'desc']) }}">
                        <i class="fas fa-check-circle me-2"></i>Disponible (+ élevé)
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        @if($stocks->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Stock Total</th>
                            <th>Disponible</th>
                            <th>Livré</th>
                            <th>Statut</th>
                            <th>Progression</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stocks as $stock)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $stock->date_stock->format('d/m/Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $stock->date_stock->translatedFormat('l') }}</small>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="fw-bold text-primary">{{ number_format($stock->quantite_totale, 2) }} L</span>
                                </td>
                                
                                <td>
                                    <span class="text-success">{{ number_format($stock->quantite_disponible, 2) }} L</span>
                                </td>
                                
                                <td>
                                    <span class="text-warning">{{ number_format($stock->quantite_livree, 2) }} L</span>
                                </td>
                                
                                <td>
                                    <span class="badge bg-{{ $stock->statut_color }}">
                                        {{ $stock->statut_stock }}
                                    </span>
                                </td>
                                
                                <td style="width: 150px;">
                                    @if($stock->quantite_totale > 0)
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-warning" 
                                                 style="width: {{ $stock->percentage_livre }}%"
                                                 title="Livré: {{ $stock->percentage_livre }}%">
                                                {{ number_format($stock->percentage_livre, 0) }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ number_format($stock->percentage_livre, 1) }}% livré</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- View Details -->
                                        <a href="{{ route('gestionnaire.stock.show', $stock->date_stock->format('Y-m-d')) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if($stock->quantite_disponible > 0)
                                            <!-- Create Livraison -->
                                            <a href="{{ route('gestionnaire.livraisons.create', ['date' => $stock->date_stock->format('Y-m-d')]) }}" 
                                               class="btn btn-sm btn-outline-success" 
                                               title="Créer livraison">
                                                <i class="fas fa-truck"></i>
                                            </a>
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
                        Affichage de {{ $stocks->firstItem() }} à {{ $stocks->lastItem() }} 
                        sur {{ $stocks->total() }} jours
                    </div>
                    <div>
                        {{ $stocks->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-warehouse text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Aucun stock trouvé</h5>
                <p class="text-muted">Aucun stock ne correspond aux critères de recherche.</p>
                <a href="{{ route('gestionnaire.receptions.index') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Voir les Réceptions
                </a>
            </div>
        @endif
    </div>
</div>
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
.border-left-secondary {
    border-left: 4px solid #6c757d !important;
}
</style>