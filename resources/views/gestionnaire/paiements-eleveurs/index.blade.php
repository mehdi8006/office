@extends('gestionnaire.layouts.app')

@section('title', 'Paiements aux Éleveurs')
@section('page-title', 'Paiements aux Éleveurs - ' . $dates['label'])

@section('page-actions')
    <a href="{{ route('gestionnaire.membres.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-users me-2"></i>Voir Membres
    </a>
@endsection

@section('content')
<!-- Summary Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Membres</h6>
                        <h3 class="mb-0 text-primary">{{ number_format($stats['total_membres']) }}</h3>
                        <small class="text-muted">Ont livré cette quinzaine</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-users text-primary" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card stats-card h-100 border-left-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Membres en Attente</h6>
                        <h3 class="mb-0 text-warning">{{ number_format($stats['en_attente']) }}</h3>
                        <small class="text-muted">À payer : {{ number_format($stats['montant_en_attente'], 2) }} DH</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-clock text-warning" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card stats-card h-100 border-left-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Membres Payés</h6>
                        <h3 class="mb-0 text-success">{{ number_format($stats['payes']) }}</h3>
                        <small class="text-muted">Payé : {{ number_format($stats['montant_paye'], 2) }} DH</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-check-circle text-success" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Period Selection -->
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-calendar me-2"></i>Sélection de la Quinzaine
            </h5>
            <div class="d-flex align-items-center">
                <span class="text-muted me-2">Coopérative :</span>
                <span class="badge bg-primary fs-6">{{ $cooperative->nom_cooperative }}</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('gestionnaire.paiements-eleveurs.index') }}" class="row g-3">
            <!-- Month Selection -->
            <div class="col-md-4">
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
            <div class="col-md-3">
                <label for="annee" class="form-label">Année</label>
                <select class="form-select" id="annee" name="annee">
                    @for($y = now()->year; $y >= now()->year - 2; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            <!-- Quinzaine Selection -->
            <div class="col-md-3">
                <label for="quinzaine" class="form-label">Quinzaine</label>
                <select class="form-select" id="quinzaine" name="quinzaine">
                    <option value="1" {{ $selectedQuinzaine == 1 ? 'selected' : '' }}>1-15</option>
                    <option value="2" {{ $selectedQuinzaine == 2 ? 'selected' : '' }}>16-Fin</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Afficher
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@php
    // Filter only members with "en_attente" status
    $membresEnAttente = collect($membresData)->filter(function($data) {
        return $data['statut'] === 'en_attente';
    });
@endphp

<!-- Members Pending Payment Table -->
<div class="card">
    <div class="card-header bg-warning-subtle">
        <h5 class="card-title mb-0">
            <i class="fas fa-clock me-2"></i>Membres en Attente de Paiement - {{ $dates['label'] }}
            <span class="badge bg-warning ms-2">{{ $membresEnAttente->count() }} membre(s)</span>
        </h5>
    </div>
    
    <div class="card-body p-0">
        @if($membresEnAttente->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Membre Éleveur</th>
                            <th>Quantité Livrée</th>
                            <th>Prix Unitaire</th>
                            <th>Montant à Payer</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($membresEnAttente as $data)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $data['membre']->nom_complet }}</strong>
                                        <br>
                                        <small class="text-muted">CIN: {{ $data['membre']->numero_carte_nationale }}</small>
                                        @if($data['receptions_count'] > 0)
                                            <br>
                                            <small class="text-info">{{ $data['receptions_count'] }} livraison(s)</small>
                                        @endif
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="fw-bold text-info">{{ number_format($data['quantite_totale'], 2) }} L</span>
                                </td>
                                
                                <td>
                                    @if($data['paiement'])
                                        <span class="text-muted">{{ number_format($data['paiement']->prix_unitaire, 2) }} DH/L</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                
                                <td>
                                    <span class="fw-bold text-success fs-5">{{ number_format($data['montant_calcule'], 2) }} DH</span>
                                </td>
                                
                                <td>
                                    <!-- Mark as Paid Button -->
                                    <form action="{{ route('gestionnaire.paiements-eleveurs.marquer-paye', $data['membre']->id_membre) }}" 
                                          method="POST" 
                                          style="display: inline;"
                                          onsubmit="return confirm('Confirmer le paiement ?\n\nMembre: {{ $data['membre']->nom_complet }}\nMontant: {{ number_format($data['montant_calcule'], 2) }} DH')">
                                        @csrf
                                        <input type="hidden" name="periode_debut" value="{{ $dates['debut']->format('Y-m-d') }}">
                                        <input type="hidden" name="periode_fin" value="{{ $dates['fin']->format('Y-m-d') }}">
                                        <button type="submit" 
                                                class="btn btn-success btn-sm" 
                                                title="Marquer comme payé">
                                            <i class="fas fa-check-circle me-1"></i>Marquer Payé
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Footer -->
            <div class="card-footer bg-light">
                <div class="row text-center">
                    <div class="col-md-4">
                        <strong>Membres en Attente:</strong> 
                        <span class="text-warning">{{ $membresEnAttente->count() }}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Quantité Totale:</strong> 
                        <span class="text-info">{{ number_format($membresEnAttente->sum('quantite_totale'), 2) }} L</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Montant Total à Payer:</strong> 
                        <span class="text-success">{{ number_format($membresEnAttente->sum('montant_calcule'), 2) }} DH</span>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Aucun paiement en attente</h5>
                <p class="text-muted">Tous les membres ont été payés pour cette quinzaine ou aucun membre n'a livré.</p>
                
                @if($stats['total_membres'] > 0)
                    <div class="alert alert-info d-inline-block mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>{{ $stats['payes'] }}</strong> membre(s) déjà payé(s) pour cette quinzaine
                    </div>
                @endif
                
                <div class="mt-3">
                    <a href="{{ route('gestionnaire.receptions.index') }}" class="btn btn-primary me-2">
                        <i class="fas fa-tint me-2"></i>Voir les Réceptions
                    </a>
                    <a href="{{ route('gestionnaire.membres.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-users me-2"></i>Voir les Membres
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Calculation Action (Only if there are non-calculated members) -->
@if($stats['non_calcules'] > 0)
<div class="card mt-4">
    <div class="card-header bg-info-subtle">
        <h6 class="card-title mb-0">
            <i class="fas fa-calculator me-2"></i>Action Requise
        </h6>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-3">
            <i class="fas fa-info-circle me-2"></i>
            <strong>{{ $stats['non_calcules'] }} membre(s)</strong> ont livré mais leurs paiements n'ont pas encore été calculés.
        </div>
        
        <form action="{{ route('gestionnaire.paiements-eleveurs.calculer-quinzaine') }}" 
              method="POST" 
              onsubmit="return confirm('Calculer les paiements pour tous les membres de cette quinzaine ?')">
            @csrf
            <input type="hidden" name="periode_debut" value="{{ $dates['debut']->format('Y-m-d') }}">
            <input type="hidden" name="periode_fin" value="{{ $dates['fin']->format('Y-m-d') }}">
            
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label for="prix_unitaire" class="form-label">Prix Unitaire (DH/L)</label>
                    <div class="input-group">
                        <input type="number" 
                               class="form-control" 
                               id="prix_unitaire" 
                               name="prix_unitaire" 
                               value="2.50" 
                               min="0.1" 
                               max="999.99" 
                               step="0.01" 
                               required>
                        <span class="input-group-text">DH</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-calculator me-2"></i>Calculer les Paiements
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}

.border-left-success {
    border-left: 4px solid #28a745 !important;
}

.stats-card {
    border-left: 4px solid #007bff;
    transition: transform 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
}

.bg-warning-subtle {
    background-color: #fff3cd !important;
}

.bg-info-subtle {
    background-color: #d1ecf1 !important;
}

.table td {
    vertical-align: middle;
}

.btn-success {
    transition: all 0.2s ease;
}

.btn-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}
</style>
@endpush