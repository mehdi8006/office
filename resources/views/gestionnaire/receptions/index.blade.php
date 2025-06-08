@extends('gestionnaire.layouts.app')

@section('title', 'Réceptions de Lait - Aujourd\'hui')
@section('page-title')
    Réceptions de Lait - {{ today()->format('d/m/Y') }}
    <span class="badge bg-primary ms-2">{{ $stats['total_receptions'] }} réception(s)</span>
@endsection

@section('page-actions')
    <a href="{{ route('gestionnaire.receptions.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Nouvelle Réception
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
                        <h6 class="text-muted mb-1">Total Réceptions</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_receptions']) }}</h3>
                        <small class="text-muted">Aujourd'hui</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-clipboard-list text-success" style="font-size: 2rem;"></i>
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
                        <h3 class="mb-0 text-primary">{{ number_format($stats['quantite_totale'], 2) }}L</h3>
                        <small class="text-muted">En stock: {{ number_format($stats['stock']['quantite_disponible'], 2) }}L</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-tint text-primary" style="font-size: 2rem;"></i>
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
                        <h6 class="text-muted mb-1">Membres Actifs</h6>
                        <h3 class="mb-0 text-info">{{ number_format($stats['nombre_membres']) }}</h3>
                        <small class="text-muted">Ont livré aujourd'hui</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-users text-info" style="font-size: 2rem;"></i>
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
                        <h6 class="text-muted mb-1">Moyenne/Réception</h6>
                        <h3 class="mb-0 text-warning">{{ number_format($stats['quantite_moyenne'], 2) }}L</h3>
                        <small class="text-muted">Par livraison</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-chart-line text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
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
        <form method="GET" action="{{ route('gestionnaire.receptions.index') }}" class="row g-3">
            <!-- Search -->
            <div class="col-md-4">
                <label for="search" class="form-label">Recherche</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Matricule ou nom du membre...">
                </div>
            </div>

            <!-- Member Filter -->
            <div class="col-md-4">
                <label for="membre_id" class="form-label">Membre Éleveur</label>
                <select class="form-select" id="membre_id" name="membre_id">
                    <option value="">Tous les membres</option>
                    @foreach($membresActifs as $membre)
                        <option value="{{ $membre->id_membre }}" 
                                {{ request('membre_id') == $membre->id_membre ? 'selected' : '' }}>
                            {{ $membre->nom_complet }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('gestionnaire.receptions.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Receptions Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Réceptions d'Aujourd'hui
                <span class="badge bg-success ms-2">{{ $receptions->total() }} réception(s)</span>
            </h5>
            
            <!-- Sort Options -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-sort me-1"></i>Trier par
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => 'desc']) }}">
                        <i class="fas fa-clock me-2"></i>Plus récent
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => 'asc']) }}">
                        <i class="fas fa-clock me-2"></i>Plus ancien
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'quantite_litres', 'sort_order' => 'desc']) }}">
                        <i class="fas fa-tint me-2"></i>Quantité (+ élevée)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'quantite_litres', 'sort_order' => 'asc']) }}">
                        <i class="fas fa-tint me-2"></i>Quantité (+ faible)
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
                            <th>Matricule</th>
                            <th>Membre Éleveur</th>
                            <th>Quantité</th>
                            <th>Heure</th>
                            <th width="80">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receptions as $reception)
                            <tr>
                                <td>
                                    <code class="fs-6">{{ $reception->matricule_reception }}</code>
                                </td>
                                
                                <td>
                                    <div>
                                        <strong>{{ $reception->membre->nom_complet }}</strong>
                                        <br>
                                        <small class="text-muted">CIN: {{ $reception->membre->numero_carte_nationale }}</small>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="text-muted">
                                        {{ number_format($reception->quantite_litres, 2) }} L
                                    </span>
                                </td>
                                
                                <td>
                                    <div>
                                        <strong>{{ $reception->created_at->format('H:i') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $reception->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- Delete Button (only for today's receptions) -->
                                        <form action="{{ route('gestionnaire.receptions.destroy', $reception) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette réception ?\n\nMatricule: {{ $reception->matricule_reception }}\nMembre: {{ $reception->membre->nom_complet }}\nQuantité: {{ number_format($reception->quantite_litres, 2) }} L\n\nCette action mettra à jour automatiquement le stock.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Supprimer cette réception">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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
                        Affichage de {{ $receptions->firstItem() }} à {{ $receptions->lastItem() }} 
                        sur {{ $receptions->total() }} réceptions
                    </div>
                    <div>
                        {{ $receptions->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-clipboard text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Aucune réception aujourd'hui</h5>
                <p class="text-muted">Commencez par enregistrer la première réception de lait de la journée.</p>
                <a href="{{ route('gestionnaire.receptions.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Nouvelle Réception
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Stock Status Card -->
@if($stats['stock']['quantite_totale'] > 0)
<div class="card mt-4">
    <div class="card-header bg-light">
        <h6 class="card-title mb-0">
            <i class="fas fa-warehouse me-2"></i>État du Stock - {{ today()->format('d/m/Y') }}
        </h6>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="border-end">
                    <h5 class="text-primary mb-1">{{ number_format($stats['stock']['quantite_totale'], 2) }} L</h5>
                    <small class="text-muted">Total Reçu</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border-end">
                    <h5 class="text-success mb-1">{{ number_format($stats['stock']['quantite_disponible'], 2) }} L</h5>
                    <small class="text-muted">Disponible</small>
                </div>
            </div>
            <div class="col-md-4">
                <h5 class="text-warning mb-1">{{ number_format($stats['stock']['quantite_livree'], 2) }} L</h5>
                <small class="text-muted">Livré</small>
            </div>
        </div>
        
        @if($stats['stock']['quantite_totale'] > 0)
            <div class="progress mt-3" style="height: 20px;">
                @php
                    $percentageDisponible = ($stats['stock']['quantite_disponible'] / $stats['stock']['quantite_totale']) * 100;
                    $percentageLivree = ($stats['stock']['quantite_livree'] / $stats['stock']['quantite_totale']) * 100;
                @endphp
                
                <div class="progress-bar bg-success" 
                     style="width: {{ $percentageDisponible }}%"
                     title="Disponible: {{ number_format($percentageDisponible, 1) }}%">
                    {{ number_format($percentageDisponible, 1) }}%
                </div>
                <div class="progress-bar bg-warning" 
                     style="width: {{ $percentageLivree }}%"
                     title="Livré: {{ number_format($percentageLivree, 1) }}%">
                    {{ number_format($percentageLivree, 1) }}%
                </div>
            </div>
        @endif
    </div>
</div>
@endif
@endsection