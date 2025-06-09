@extends('gestionnaire.layouts.app')

@section('title', 'Gestion des Paiements Usine')
@section('page-title', 'Paiements Usine - ' . \Carbon\Carbon::create($selectedYear, $selectedMonth)->translatedFormat('F Y'))

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('gestionnaire.livraisons.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-truck me-2"></i>Voir Livraisons
        </a>
    </div>
@endsection

@section('content')
<!-- Statistics Cards - Modifiées pour masquer les montants si nécessaire -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Quinzaines</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_quinzaines']) }}</h3>
                        <small class="text-muted">{{ \Carbon\Carbon::create($selectedYear, $selectedMonth)->translatedFormat('F Y') }}</small>
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
                        <h6 class="text-muted mb-1">Quantité Totale</h6>
                        <h3 class="mb-0 text-info">{{ number_format($stats['total_quantite'], 1) }}L</h3>
                        <small class="text-muted">{{ $stats['total_livraisons'] }} livraisons</small>
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
                        <h6 class="text-muted mb-1">Montant Calculé</h6>
                        @if($stats['total_montant'] > 0)
                            <h3 class="mb-0 text-success">{{ number_format($stats['total_montant'], 2) }}</h3>
                            <small class="text-muted">DH</small>
                        @else
                            <h3 class="mb-0 text-muted">-</h3>
                            <small class="text-muted">Pas encore calculé</small>
                        @endif
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
                        <h6 class="text-muted mb-1">Montant Payé</h6>
                        @if($stats['montant_paye'] > 0)
                            <h3 class="mb-0 text-warning">{{ number_format($stats['montant_paye'], 2) }}</h3>
                            <small class="text-muted">DH</small>
                        @else
                            <h3 class="mb-0 text-muted">-</h3>
                            <small class="text-muted">Aucun paiement</small>
                        @endif
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-check-circle text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-2">
        <div class="card border-left-danger h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-danger mb-1">{{ $stats['quinzaines_non_calculees'] }}</h4>
                <small class="text-muted">Non Calculées</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="card border-left-warning h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-warning mb-1">{{ $stats['quinzaines_calculees'] }}</h4>
                <small class="text-muted">En Attente</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="card border-left-success h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-success mb-1">{{ $stats['quinzaines_payees'] }}</h4>
                <small class="text-muted">Payées</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card - Simplifié pour un seul mois -->
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>Sélection du Mois
            </h5>
            <div class="d-flex align-items-center">
                <span class="text-muted me-2">Coopérative :</span>
                <span class="badge bg-primary fs-6">{{ $cooperative->nom_cooperative }}</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('gestionnaire.paiements.index') }}" class="row g-3">
            <!-- Month Selection -->
            <div class="col-md-6">
                <label for="mois" class="form-label">Mois</label>
                <select class="form-select" id="mois" name="mois">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <!-- Year Selection -->
            <div class="col-md-6">
                <label for="annee" class="form-label">Année</label>
                <select class="form-select" id="annee" name="annee">
                    @for($y = now()->year; $y >= now()->year - 2; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Afficher
                </button>
                <a href="{{ route('gestionnaire.paiements.index') }}" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-calendar me-1"></i>Mois Actuel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Quinzaines Cards - 2 quinzaines seulement -->
<div class="row">
    @forelse($quinzaines as $quinzaine)
        <div class="col-lg-6 mb-4">
            <div class="card h-100 quinzaine-card border-{{ $quinzaine['statut_color'] }}">
                <div class="card-header bg-{{ $quinzaine['statut_color'] }} bg-opacity-10">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-calendar me-2"></i>
                            {{ $quinzaine['periode_label'] }}
                        </h6>
                        <span class="badge bg-{{ $quinzaine['statut_color'] }}">
                            @switch($quinzaine['statut'])
                                @case('non_calcule')
                                    Non Calculé
                                    @break
                                @case('calcule')
                                    En Attente
                                    @break
                                @case('paye')
                                    Payé
                                    @break
                            @endswitch
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    @if($quinzaine['total_quantite'] > 0)
                        <!-- Informations basiques (toujours visibles) -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-info mb-1">{{ number_format($quinzaine['total_quantite'], 1) }}L</h4>
                                    <small class="text-muted">Quantité Totale</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-primary mb-1">{{ $quinzaine['livraisons_count'] }}</h4>
                                    <small class="text-muted">Livraisons</small>
                                </div>
                            </div>
                        </div>

                        <!-- Informations financières (seulement si calculé) -->
                        @if($quinzaine['statut'] !== 'non_calcule')
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="text-center bg-light p-2 rounded">
                                        <h4 class="text-success mb-1">{{ number_format($quinzaine['total_montant'], 2) }} DH</h4>
                                        <small class="text-muted">Montant Total</small>
                                    </div>
                                </div>
                            </div>

                            @if($quinzaine['montant_paye'] > 0 || $quinzaine['montant_en_attente'] > 0)
                                <div class="row mb-3">
                                    @if($quinzaine['montant_paye'] > 0)
                                        <div class="col-6">
                                            <div class="text-center">
                                                <h6 class="text-success mb-1">{{ number_format($quinzaine['montant_paye'], 2) }} DH</h6>
                                                <small class="text-muted">Payé</small>
                                            </div>
                                        </div>
                                    @endif
                                    @if($quinzaine['montant_en_attente'] > 0)
                                        <div class="col-6">
                                            <div class="text-center">
                                                <h6 class="text-warning mb-1">{{ number_format($quinzaine['montant_en_attente'], 2) }} DH</h6>
                                                <small class="text-muted">En Attente</small>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Progress bar if partially paid -->
                                @if($quinzaine['total_montant'] > 0)
                                    @php
                                        $percentagePaye = ($quinzaine['montant_paye'] / $quinzaine['total_montant']) * 100;
                                        $percentageAttente = ($quinzaine['montant_en_attente'] / $quinzaine['total_montant']) * 100;
                                    @endphp
                                    
                                    <div class="progress mb-3" style="height: 15px;">
                                        <div class="progress-bar bg-success" 
                                             style="width: {{ $percentagePaye }}%"
                                             title="Payé: {{ number_format($percentagePaye, 1) }}%">
                                        </div>
                                        <div class="progress-bar bg-warning" 
                                             style="width: {{ $percentageAttente }}%"
                                             title="En attente: {{ number_format($percentageAttente, 1) }}%">
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @else
                            <!-- Message pour statut non calculé -->
                            <div class="text-center mb-3">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Période non calculée</strong><br>
                                    <small>Les montants seront affichés après calcul des paiements</small>
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="text-center">
                            @if($quinzaine['peut_calculer'])
                                <form action="{{ route('gestionnaire.paiements.calculer-periode') }}" 
                                      method="POST" 
                                      style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="periode_debut" value="{{ $quinzaine['date_debut'] }}">
                                    <input type="hidden" name="periode_fin" value="{{ $quinzaine['date_fin'] }}">
                                    <button type="submit" 
                                            class="btn btn-warning"
                                            onclick="return confirm('Calculer les paiements pour la période {{ $quinzaine['periode_label'] }} ?\n\nQuantité: {{ number_format($quinzaine['total_quantite'], 1) }}L\n{{ $quinzaine['livraisons_count'] }} livraison(s)')">
                                        <i class="fas fa-calculator me-1"></i>Calculer Paiements
                                    </button>
                                </form>
                            @elseif($quinzaine['statut'] === 'calcule')
                                <span class="text-warning">
                                    <i class="fas fa-clock me-1"></i>En attente de validation usine
                                </span>
                            @elseif($quinzaine['statut'] === 'paye')
                                <span class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>Paiements effectués
                                </span>
                            @elseif(!$quinzaine['est_passe'])
                                <span class="text-muted">
                                    <i class="fas fa-hourglass-half me-1"></i>Période en cours
                                </span>
                            @endif
                        </div>
                    @else
                        <!-- No deliveries -->
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-truck" style="font-size: 2rem; opacity: 0.3;"></i>
                            <p class="mt-2 mb-0">Aucune livraison pour cette période</p>
                        </div>
                    @endif
                </div>

                <!-- Footer with period dates -->
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>
                        {{ \Carbon\Carbon::parse($quinzaine['date_debut'])->format('d/m/Y') }} - 
                        {{ \Carbon\Carbon::parse($quinzaine['date_fin'])->format('d/m/Y') }}
                    </small>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calendar-times text-muted" style="font-size: 4rem;"></i>
                    <h5 class="text-muted mt-3">Aucune donnée trouvée</h5>
                    <p class="text-muted">Aucune quinzaine trouvée pour le mois sélectionné.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Overall Summary Card - Seulement si des données calculées existent -->
@if($stats['total_montant'] > 0)
<div class="card mt-4">
    <div class="card-header bg-light">
        <h6 class="card-title mb-0">
            <i class="fas fa-chart-pie me-2"></i>Résumé du Mois
        </h6>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-3">
                <h5 class="text-info mb-1">{{ number_format($stats['total_quantite'], 1) }} L</h5>
                <small class="text-muted">Quantité Totale</small>
            </div>
            <div class="col-md-3">
                <h5 class="text-success mb-1">{{ number_format($stats['total_montant'], 2) }} DH</h5>
                <small class="text-muted">Montant Total</small>
            </div>
            <div class="col-md-3">
                <h5 class="text-warning mb-1">{{ number_format($stats['montant_en_attente'], 2) }} DH</h5>
                <small class="text-muted">En Attente</small>
            </div>
            <div class="col-md-3">
                <h5 class="text-primary mb-1">{{ number_format($stats['montant_paye'], 2) }} DH</h5>
                <small class="text-muted">Payé</small>
            </div>
        </div>

        <!-- Global progress bar -->
        @if($stats['total_montant'] > 0)
            <div class="progress mt-3" style="height: 25px;">
                @php
                    $globalPercentagePaye = ($stats['montant_paye'] / $stats['total_montant']) * 100;
                    $globalPercentageAttente = ($stats['montant_en_attente'] / $stats['total_montant']) * 100;
                @endphp
                
                <div class="progress-bar bg-success" 
                     style="width: {{ $globalPercentagePaye }}%"
                     title="Payé: {{ number_format($globalPercentagePaye, 1) }}%">
                    {{ number_format($globalPercentagePaye, 1) }}% Payé
                </div>
                <div class="progress-bar bg-warning" 
                     style="width: {{ $globalPercentageAttente }}%"
                     title="En attente: {{ number_format($globalPercentageAttente, 1) }}%">
                    {{ number_format($globalPercentageAttente, 1) }}% En Attente
                </div>
            </div>
        @endif
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.quinzaine-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.quinzaine-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.border-left-danger {
    border-left: 4px solid #dc3545 !important;
}

.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}

.border-left-success {
    border-left: 4px solid #28a745 !important;
}

.bg-opacity-10 {
    --bs-bg-opacity: 0.1;
}

.progress {
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}
</style>
@endpush