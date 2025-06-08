@extends('gestionnaire.layouts.app')

@section('title', 'Gestion des Paiements Usine')
@section('page-title', 'Gestion des Paiements Usine')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('gestionnaire.livraisons.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-truck me-2"></i>Voir Livraisons
        </a>
          @if(count($pendingPeriods) > 0)
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#calculerPaiementsModal">
                <i class="fas fa-calculator me-2"></i>Calculer Paiements
            </button>
        @endif
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
                        <h6 class="text-muted mb-1">Total Paiements</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_paiements']) }}</h3>
                        <small class="text-muted">Période sélectionnée</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-money-bill text-primary" style="font-size: 2rem;"></i>
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
                        <h3 class="mb-0 text-success">{{ number_format($stats['total_montant'], 2) }}</h3>
                        <small class="text-muted">DH</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-coins text-success" style="font-size: 2rem;"></i>
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
                        <h6 class="text-muted mb-1">En Attente</h6>
                        <h3 class="mb-0 text-warning">{{ number_format($stats['montant_en_attente'], 2) }}</h3>
                        <small class="text-muted">{{ $stats['paiements_en_attente'] }} paiement(s)</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-clock text-warning" style="font-size: 2rem;"></i>
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
                        <h6 class="text-muted mb-1">Payés</h6>
                        <h3 class="mb-0 text-info">{{ number_format($stats['montant_paye'], 2) }}</h3>
                        <small class="text-muted">{{ $stats['paiements_payes'] }} paiement(s)</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-check-circle text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Periods Alert -->
@if(count($pendingPeriods)> 0)
<div class="alert alert-warning">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle me-3" style="font-size: 1.5rem;"></i>
        <div class="flex-grow-1">
            <h6 class="alert-heading mb-1">Périodes en attente de calcul</h6>
            <p class="mb-2">{{ count($pendingPeriods) }} période(s) ont des livraisons validées non payées.</p>
            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#calculerPaiementsModal">
                <i class="fas fa-calculator me-1"></i>Calculer maintenant
            </button>
        </div>
    </div>
</div>
@endif

<!-- Livraisons non payées Alert -->
@if($stats['livraisons_non_payees'] > 0)
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>{{ $stats['livraisons_non_payees'] }} livraison(s) validée(s)</strong> en attente de paiement.
</div>
@endif

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
        <form method="GET" action="{{ route('gestionnaire.paiements.index') }}" class="row g-3">
            <!-- Date Range -->
            <div class="col-md-3">
                <label for="date_debut" class="form-label">Date Début</label>
                <input type="date" 
                       class="form-control" 
                       id="date_debut" 
                       name="date_debut" 
                       value="{{ request('date_debut', now()->subDays(60)->format('Y-m-d')) }}">
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
                    <option value="en_attente" {{ request('statut') === 'en_attente' ? 'selected' : '' }}>En Attente</option>
                    <option value="paye" {{ request('statut') === 'paye' ? 'selected' : '' }}>Payé</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('gestionnaire.paiements.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Paiements Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Liste des Paiements
                <span class="badge bg-primary ms-2">{{ $paiements->total() }} paiement(s)</span>
            </h5>
            
            <!-- Sort Options -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-sort me-1"></i>Trier par
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'date_paiement', 'sort_order' => 'desc']) }}">
                        <i class="fas fa-calendar-alt me-2"></i>Date (Plus récent)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'date_paiement', 'sort_order' => 'asc']) }}">
                        <i class="fas fa-calendar-alt me-2"></i>Date (Plus ancien)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'montant', 'sort_order' => 'desc']) }}">
                        <i class="fas fa-money-bill me-2"></i>Montant (+ élevé)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'statut', 'sort_order' => 'asc']) }}">
                        <i class="fas fa-flag me-2"></i>Statut
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        @if($paiements->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date Paiement</th>
                            <th>Livraison</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Créé le</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paiements as $paiement)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $paiement->date_paiement->format('d/m/Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $paiement->date_paiement->translatedFormat('l') }}</small>
                                    </div>
                                </td>
                                
                                <td>
                                    <div>
                                        <strong>{{ $paiement->livraison->date_livraison->format('d/m/Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $paiement->livraison->quantite_formattee }}</small>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="fw-bold text-success">{{ $paiement->montant_formattee }}</span>
                                </td>
                                
                                <td>
                                    <span class="badge bg-{{ $paiement->statut_color }}">
                                        {{ $paiement->statut_label }}
                                    </span>
                                </td>
                                
                                <td>
                                    <div>
                                        <small>{{ $paiement->created_at->format('d/m/Y H:i') }}</small>
                                        <br>
                                        <small class="text-muted">{{ $paiement->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($paiement->statut === 'en_attente')
                                            <!-- Marquer comme payé -->
                                            <form action="{{ route('gestionnaire.paiements.marquer-paye', $paiement) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Marquer ce paiement comme payé ?\n\nMontant: {{ $paiement->montant_formattee }}\nDate: {{ $paiement->date_paiement->format('d/m/Y') }}')">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-success" 
                                                        title="Marquer comme payé">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @else
                                            <div class="text-center w-100">
                                                <span class="text-success small">
                                                    <i class="fas fa-check-circle me-1"></i>Payé
                                                </span>
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
                        Affichage de {{ $paiements->firstItem() }} à {{ $paiements->lastItem() }} 
                        sur {{ $paiements->total() }} paiements
                    </div>
                    <div>
                        {{ $paiements->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-money-bill text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Aucun paiement trouvé</h5>
                <p class="text-muted">Aucun paiement ne correspond aux critères de recherche.</p>
                @if(count($pendingPeriods)> 0)
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#calculerPaiementsModal">
                        <i class="fas fa-calculator me-2"></i>Calculer les premiers paiements
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Calculate Payments Modal -->
@if(count($pendingPeriods) > 0)
<div class="modal fade" id="calculerPaiementsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calculator me-2"></i>
                    Calculer les Paiements
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Information :</strong> Les paiements sont calculés automatiquement tous les 15 jours 
                    (1-15 et 16-fin du mois) pour toutes les livraisons validées.
                </div>

                <h6 class="mb-3">Périodes disponibles pour calcul :</h6>
                
                @foreach($pendingPeriods as $period)
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $period['type'] }}</h6>
                                    <p class="text-muted mb-0">{{ $period['label'] }}</p>
                                </div>
                                <form action="{{ route('gestionnaire.paiements.calculer-periode') }}" 
                                      method="POST" 
                                      style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="periode_debut" value="{{ $period['debut'] }}">
                                    <input type="hidden" name="periode_fin" value="{{ $period['fin'] }}">
                                    <button type="submit" 
                                            class="btn btn-warning"
                                            onclick="return confirm('Calculer les paiements pour la période {{ $period['label'] }} ?')">
                                        <i class="fas fa-calculator me-1"></i>Calculer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Fermer
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Summary Card -->
@if($stats['total_paiements'] > 0)
<div class="card mt-4">
    <div class="card-header bg-light">
        <h6 class="card-title mb-0">
            <i class="fas fa-chart-pie me-2"></i>Résumé des Paiements
        </h6>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="border-end">
                    <h5 class="text-success mb-1">{{ number_format($stats['total_montant'], 2) }} DH</h5>
                    <small class="text-muted">Montant Total</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border-end">
                    <h5 class="text-warning mb-1">{{ number_format($stats['montant_en_attente'], 2) }} DH</h5>
                    <small class="text-muted">En Attente</small>
                </div>
            </div>
            <div class="col-md-4">
                <h5 class="text-info mb-1">{{ number_format($stats['montant_paye'], 2) }} DH</h5>
                <small class="text-muted">Payés</small>
            </div>
        </div>
        
        @if($stats['total_montant'] > 0)
            <div class="progress mt-3" style="height: 20px;">
                @php
                    $percentagePaye = ($stats['montant_paye'] / $stats['total_montant']) * 100;
                    $percentageAttente = ($stats['montant_en_attente'] / $stats['total_montant']) * 100;
                @endphp
                
                <div class="progress-bar bg-info" 
                     style="width: {{ $percentagePaye }}%"
                     title="Payés: {{ number_format($percentagePaye, 1) }}%">
                    {{ number_format($percentagePaye, 1) }}%
                </div>
                <div class="progress-bar bg-warning" 
                     style="width: {{ $percentageAttente }}%"
                     title="En attente: {{ number_format($percentageAttente, 1) }}%">
                    {{ number_format($percentageAttente, 1) }}%
                </div>
            </div>
        @endif
    </div>
</div>
@endif
@endsection