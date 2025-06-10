@extends('gestionnaire.layouts.app')

@section('title', 'Paiements aux Éleveurs')
@section('page-title', 'Paiements aux Éleveurs - ' . $dates['label'])

@section('page-actions')
    <a href="{{ route('gestionnaire.membres.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-users me-2"></i>Voir Membres
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
                        <h6 class="text-muted mb-1">Total Membres</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_membres']) }}</h3>
                        <small class="text-muted">Ont livré</small>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-users text-primary" style="font-size: 2rem;"></i>
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
                        <small class="text-muted">Période</small>
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
                        <h6 class="text-muted mb-1">Montant Payé</h6>
                        <h3 class="mb-0 text-warning">{{ number_format($stats['montant_paye'], 2) }}</h3>
                        <small class="text-muted">DH</small>
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
                <h4 class="text-danger mb-1">{{ $stats['non_calcules'] }}</h4>
                <small class="text-muted">Non Calculés</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="card border-left-warning h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-warning mb-1">{{ $stats['en_attente'] }}</h4>
                <small class="text-muted">En Attente</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="card border-left-success h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-success mb-1">{{ $stats['payes'] }}</h4>
                <small class="text-muted">Payés</small>
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

<!-- Global Actions -->
@if($peutCalculer || $stats['en_attente'] > 0)
<div class="card mb-4">
    <div class="card-header bg-light">
        <h6 class="card-title mb-0">
            <i class="fas fa-cogs me-2"></i>Actions Globales
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            @if($peutCalculer)
                <div class="col-md-6">
                    <form action="{{ route('gestionnaire.paiements-eleveurs.calculer-quinzaine') }}" 
                          method="POST" 
                          onsubmit="return confirm('Calculer les paiements pour tous les membres de cette quinzaine ?')">
                        @csrf
                        <input type="hidden" name="periode_debut" value="{{ $dates['debut']->format('Y-m-d') }}">
                        <input type="hidden" name="periode_fin" value="{{ $dates['fin']->format('Y-m-d') }}">
                        
                        <div class="mb-3">
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
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-calculator me-2"></i>Calculer Tous les Paiements
                        </button>
                    </form>
                </div>
            @endif

            @if($stats['en_attente'] > 0)
                <div class="col-md-6">
                    <form action="{{ route('gestionnaire.paiements-eleveurs.marquer-tous-payes') }}" 
                          method="POST" 
                          onsubmit="return confirm('Marquer tous les paiements en attente comme payés ?\n\nMontant total: {{ number_format($stats['montant_en_attente'], 2) }} DH')">
                        @csrf
                        <input type="hidden" name="periode_debut" value="{{ $dates['debut']->format('Y-m-d') }}">
                        <input type="hidden" name="periode_fin" value="{{ $dates['fin']->format('Y-m-d') }}">
                        
                        <div class="mb-3">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>{{ $stats['en_attente'] }} paiement(s) en attente</strong><br>
                                Montant total: <strong>{{ number_format($stats['montant_en_attente'], 2) }} DH</strong>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle me-2"></i>Marquer Tous comme Payés
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Members Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>Liste des Membres - {{ $dates['label'] }}
            <span class="badge bg-primary ms-2">{{ count($membresData) }} membre(s)</span>
        </h5>
    </div>
    
    <div class="card-body p-0">
        @if(count($membresData) > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Membre Éleveur</th>
                            <th>Quantité Livrée</th>
                            <th>Montant Calculé</th>
                            <th>Statut</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($membresData as $data)
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
                                    <span class="fw-bold text-success">{{ number_format($data['montant_calcule'], 2) }} DH</span>
                                    @if($data['paiement'])
                                        <br>
                                        <small class="text-muted">{{ number_format($data['paiement']->prix_unitaire, 2) }} DH/L</small>
                                    @endif
                                </td>
                                
                                <td>
                                    <span class="badge bg-{{ $data['statut_color'] }}">
                                        @switch($data['statut'])
                                            @case('non_calcule')
                                                Non Calculé
                                                @break
                                            @case('en_attente')
                                                En Attente
                                                @break
                                            @case('paye')
                                                Payé
                                                @break
                                        @endswitch
                                    </span>
                                    
                                    @if($data['paiement'] && $data['paiement']->date_paiement)
                                        <br>
                                        <small class="text-muted">{{ $data['paiement']->date_paiement->format('d/m/Y') }}</small>
                                    @endif
                                </td>
                                
                                <td>
                                    @if($data['statut'] === 'en_attente')
                                        <!-- Mark as Paid -->
                                        <form action="{{ route('gestionnaire.paiements-eleveurs.marquer-paye', $data['membre']->id_membre) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Marquer ce paiement comme payé ?\n\nMembre: {{ $data['membre']->nom_complet }}\nMontant: {{ number_format($data['montant_calcule'], 2) }} DH')">
                                            @csrf
                                            <input type="hidden" name="periode_debut" value="{{ $dates['debut']->format('Y-m-d') }}">
                                            <input type="hidden" name="periode_fin" value="{{ $dates['fin']->format('Y-m-d') }}">
                                            <button type="submit" 
                                                    class="btn btn-sm btn-success" 
                                                    title="Marquer comme payé">
                                                <i class="fas fa-check"></i> Payé
                                            </button>
                                        </form>
                                    @elseif($data['statut'] === 'paye')
                                        <span class="text-success small">
                                            <i class="fas fa-check-circle me-1"></i>Paiement effectué
                                        </span>
                                    @elseif($data['statut'] === 'non_calcule')
                                        <span class="text-muted small">
                                            <i class="fas fa-clock me-1"></i>En attente de calcul
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Footer -->
            <div class="card-footer bg-light">
                <div class="row text-center">
                    <div class="col-md-3">
                        <strong>Total Quantité:</strong> 
                        <span class="text-info">{{ number_format($stats['total_quantite'], 2) }} L</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Total Montant:</strong> 
                        <span class="text-success">{{ number_format($stats['total_montant'], 2) }} DH</span>
                    </div>
                    <div class="col-md-3">
                        <strong>En Attente:</strong> 
                        <span class="text-warning">{{ number_format($stats['montant_en_attente'], 2) }} DH</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Payé:</strong> 
                        <span class="text-primary">{{ number_format($stats['montant_paye'], 2) }} DH</span>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Aucun membre trouvé</h5>
                <p class="text-muted">Aucun membre n'a livré de lait pendant cette quinzaine.</p>
                <a href="{{ route('gestionnaire.receptions.index') }}" class="btn btn-primary">
                    <i class="fas fa-tint me-2"></i>Voir les Réceptions
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.border-left-danger {
    border-left: 4px solid #dc3545 !important;
}

.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}

.border-left-success {
    border-left: 4px solid #28a745 !important;
}

.stats-card {
    border-left: 4px solid #28a745;
    transition: transform 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
}
</style>
@endpush