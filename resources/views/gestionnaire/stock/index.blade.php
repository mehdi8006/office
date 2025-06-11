@extends('gestionnaire.layouts.app')

@section('title', 'Gestion du Stock')
@section('page-title', 'Gestion du Stock')



@section('content')
<!-- Stock Total Restant -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="me-3">
                        <i class="fas fa-warehouse text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Stock Total Restant (Non Livré)</h6>
                        <h2 class="mb-0 text-primary">{{ number_format($stockTotalRestant, 2) }} L</h2>
                        <small class="text-muted">{{ $cooperative->nom_cooperative }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-filter me-2"></i>Filtres
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('gestionnaire.stock.index') }}" class="row g-3">
            <!-- Date Range -->
            <div class="col-md-4">
                <label for="date_debut" class="form-label">Date Début</label>
                <input type="date" 
                       class="form-control" 
                       id="date_debut" 
                       name="date_debut" 
                       value="{{ request('date_debut', now()->subDays(30)->format('Y-m-d')) }}">
            </div>

            <div class="col-md-4">
                <label for="date_fin" class="form-label">Date Fin</label>
                <input type="date" 
                       class="form-control" 
                       id="date_fin" 
                       name="date_fin" 
                       value="{{ request('date_fin', now()->format('Y-m-d')) }}">
            </div>

            <!-- Buttons -->
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Filtrer
                    </button>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="avec_receptions_seulement" name="avec_receptions_seulement" {{ request('avec_receptions_seulement') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="avec_receptions_seulement">
                        Jours avec réceptions seulement
                    </label>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table des Réceptions par Jour -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-calendar-alt me-2"></i>Réceptions par Jour
                <span class="badge bg-primary ms-2">{{ $receptions->total() }} jour(s)</span>
            </h5>
            
            <!-- Sort Options -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-sort me-1"></i>Trier par
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'date_reception', 'sort_order' => 'desc']) }}">
                        <i class="fas fa-calendar-alt me-2"></i>Date (Plus récent)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'date_reception', 'sort_order' => 'asc']) }}">
                        <i class="fas fa-calendar-alt me-2"></i>Date (Plus ancien)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'quantite_totale', 'sort_order' => 'desc']) }}">
                        <i class="fas fa-tint me-2"></i>Quantité (+ élevée)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'nombre_receptions', 'sort_order' => 'desc']) }}">
                        <i class="fas fa-list me-2"></i>Nb Réceptions (+ élevé)
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        @if($receptions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Nombre de Réceptions</th>
                            <th>Quantité Totale Reçue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receptions as $reception)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ \Carbon\Carbon::parse($reception->date_reception)->format('d/m/Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($reception->date_reception)->translatedFormat('l') }}</small>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="badge bg-info fs-6">{{ $reception->nombre_receptions }}</span>
                                    <small class="text-muted d-block">réception(s)</small>
                                </td>
                                
                                <td>
                                    <span class="fw-bold text-primary fs-5">{{ number_format($reception->quantite_totale, 2) }} L</span>
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
                        Affichage de {{ $receptions->firstItem() }} à {{ $receptions->lastItem() }} 
                        sur {{ $receptions->total() }} jours
                    </div>
                    <div>
                        {{ $receptions->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-times text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Aucune réception trouvée</h5>
                <p class="text-muted">Aucune réception ne correspond aux critères de recherche.</p>
                <a href="{{ route('gestionnaire.receptions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Ajouter une Réception
                </a>
            </div>
        @endif
    </div>
</div>
@endsection