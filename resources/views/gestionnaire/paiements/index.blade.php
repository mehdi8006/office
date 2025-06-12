@extends('gestionnaire.layouts.app')

@section('title', 'Gestion des Paiements Usine')
@section('page-title', 'Paiements Usine - Quinzaine Actuelle')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('gestionnaire.livraisons.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-truck me-2"></i>Voir Livraisons
        </a>
        <a href="{{ route('gestionnaire.paiements.download-historique') }}" class="btn btn-success">
            <i class="fas fa-file-pdf me-2"></i>Télécharger Historique PDF
        </a>
    </div>
@endsection

@section('content')
<!-- Info Coopérative -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h6 class="card-title mb-0">
                    <i class="fas fa-building me-2"></i>Coopérative
                </h6>
            </div>
            <div class="card-body">
                <h5 class="text-primary mb-2">{{ $cooperative->nom_cooperative }}</h5>
                <p class="text-muted mb-1"><strong>Matricule :</strong> {{ $cooperative->matricule }}</p>
                <p class="text-muted mb-0"><strong>Gestionnaire :</strong> {{ auth()->user()->nom_complet }}</p>
            </div>
        </div>
    </div>

    <!-- Prix Unitaire -->
    <div class="col-lg-6">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0">
                    <i class="fas fa-coins me-2"></i>Prix Unitaire Actuel
                </h6>
            </div>
            <div class="card-body text-center">
                <h2 class="text-info mb-2">{{ number_format($prixUnitaire, 2) }} DH/L</h2>
                <p class="text-muted mb-0">Prix par litre de lait</p>
                <small class="text-muted">Utilisé pour les calculs de paiements</small>
            </div>
        </div>
    </div>
</div>

<!-- Quinzaine Actuelle -->
<div class="row mb-4">
    <div class="col-lg-8 mx-auto">
        <div class="card border-{{ $quinzaineActuelle['statut_color'] }} quinzaine-card">
            <div class="card-header bg-{{ $quinzaineActuelle['statut_color'] }} bg-opacity-10">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar me-2"></i>{{ $quinzaineActuelle['label'] }}
                    </h5>
                    <span class="badge bg-{{ $quinzaineActuelle['statut_color'] }} fs-6">
                        @switch($quinzaineActuelle['statut'])
                            @case('en_cours')
                                En Cours
                                @break
                            @case('non_calcule')
                                Non Calculé
                                @break
                            @case('en_attente')
                                En Attente de Paiement
                                @break
                            @case('paye')
                                Payé
                                @break
                        @endswitch
                    </span>
                </div>
            </div>
            
            <div class="card-body">
                @if($quinzaineActuelle['total_quantite'] > 0)
                    <!-- Quantité -->
                    <div class="text-center mb-3">
                        <h3 class="text-primary mb-1">{{ number_format($quinzaineActuelle['total_quantite'], 1) }} L</h3>
                        <small class="text-muted">Quantité livrée (validée)</small>
                    </div>

                    <!-- Montant -->
                    @if($quinzaineActuelle['statut'] !== 'non_calcule' && $quinzaineActuelle['statut'] !== 'en_cours')
                        <div class="text-center mb-3">
                            <div class="bg-light p-3 rounded">
                                <h3 class="text-success mb-1">{{ number_format($quinzaineActuelle['montant_calcule'], 2) }} DH</h3>
                                <small class="text-muted">Montant calculé</small>
                            </div>
                        </div>
                    @endif

                    <!-- Message spécial ou Actions -->
                    <div class="text-center">
                        @if($quinzaineActuelle['est_en_cours'])
                            <!-- Période en cours -->
                            <div class="alert alert-info">
                                <i class="fas fa-clock me-2"></i>
                                <strong>Quinzaine en cours</strong>
                                <br>
                                <small>{{ $quinzaineActuelle['message_special'] }}</small>
                            </div>
                        @elseif($quinzaineActuelle['peut_calculer'])
                            <!-- Peut calculer -->
                            <button type="button" 
                                    class="btn btn-warning"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#calculerModal"
                                    data-debut="{{ $quinzaineActuelle['date_debut'] }}"
                                    data-fin="{{ $quinzaineActuelle['date_fin'] }}"
                                    data-label="{{ $quinzaineActuelle['label'] }}"
                                    data-quantite="{{ $quinzaineActuelle['total_quantite'] }}">
                                <i class="fas fa-calculator me-1"></i>Calculer Paiement
                            </button>
                        @elseif($quinzaineActuelle['statut'] === 'en_attente')
                            <!-- En attente de paiement -->
                            <div class="alert alert-warning">
                                <i class="fas fa-hourglass-half me-2"></i>
                                <strong>Paiement calculé</strong>
                                <br>
                                <small>En attente de validation par la direction</small>
                            </div>
                        @elseif($quinzaineActuelle['statut'] === 'paye')
                            <!-- Déjà payé -->
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Paiement effectué</strong>
                                <br>
                                <small>Payé le {{ $quinzaineActuelle['paiement']->date_paiement->format('d/m/Y') }}</small>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Aucune livraison -->
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-truck" style="font-size: 3rem; opacity: 0.3;"></i>
                        <h5 class="mt-3 mb-2">Aucune livraison</h5>
                        <p class="mb-0">Aucune livraison validée pour cette quinzaine</p>
                    </div>
                @endif
            </div>

            <!-- Footer avec dates -->
            <div class="card-footer bg-light">
                <div class="row">
                    <div class="col">
                        <small class="text-muted">
                            <i class="fas fa-calendar-alt me-1"></i>
                            {{ \Carbon\Carbon::parse($quinzaineActuelle['date_debut'])->format('d/m/Y') }} - 
                            {{ \Carbon\Carbon::parse($quinzaineActuelle['date_fin'])->format('d/m/Y') }}
                        </small>
                    </div>
                    <div class="col-auto">
                        @if($quinzaineActuelle['est_terminee'])
                            <span class="badge bg-secondary">Terminée</span>
                        @else
                            <span class="badge bg-info">En cours</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tableau des Paiements en Attente (Lecture seule) -->
@if($paiementsEnAttente->count() > 0)
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-clock me-2 text-warning"></i>
            Paiements en Attente de Validation ({{ $paiementsEnAttente->count() }})
        </h5>
        <small class="text-muted">Ces paiements ont été calculés et attendent la validation de la direction</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Quinzaine</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire</th>
                        <th>Montant Total</th>
                        <th>Date Calcul</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paiementsEnAttente as $paiement)
                        <tr>
                            <td>
                                <strong>{{ $paiement->quinzaine_label }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $paiement->quantite_formattee }}</span>
                            </td>
                            <td>
                                {{ $paiement->prix_formattee }}
                            </td>
                            <td>
                                <strong class="text-success">{{ $paiement->montant_formattee }}</strong>
                            </td>
                            <td>
                                {{ $paiement->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $paiement->statut_color }}">
                                    {{ $paiement->statut_label }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th>Total</th>
                        <th>
                            <span class="badge bg-primary">
                                {{ number_format($paiementsEnAttente->sum('quantite_litres'), 2) }} L
                            </span>
                        </th>
                        <th>-</th>
                        <th>
                            <strong class="text-success">
                                {{ number_format($paiementsEnAttente->sum('montant'), 2) }} DH
                            </strong>
                        </th>
                        <th>-</th>
                        <th>-</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@else
<div class="card">
    <div class="card-body text-center py-4">
        <i class="fas fa-check-circle text-success" style="font-size: 3rem; opacity: 0.5;"></i>
        <h5 class="mt-3 text-muted">Aucun paiement en attente</h5>
        <p class="text-muted mb-0">Tous les paiements calculés ont été traités</p>
    </div>
</div>
@endif

<!-- Modal Calculer Paiement -->
<div class="modal fade" id="calculerModal" tabindex="-1" aria-labelledby="calculerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calculerModalLabel">
                    <i class="fas fa-calculator me-2"></i>Calculer Paiement Quinzaine
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('gestionnaire.paiements.calculer-quinzaine') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="modal_date_debut" name="date_debut">
                    <input type="hidden" id="modal_date_fin" name="date_fin">
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Informations Quinzaine</h6>
                        <div><strong>Période :</strong> <span id="modal_periode"></span></div>
                        <div><strong>Quantité :</strong> <span id="modal_quantite"></span> L</div>
                    </div>

                    <div class="mb-3">
                        <label for="prix_unitaire" class="form-label">
                            <i class="fas fa-coins text-warning me-1"></i>
                            Prix Unitaire (DH/L) <span class="text-danger">*</span>
                        </label>
                        <input type="number" 
                               class="form-control @error('prix_unitaire') is-invalid @enderror" 
                               id="prix_unitaire" 
                               name="prix_unitaire" 
                               value="{{ old('prix_unitaire', $prixUnitaire) }}" 
                               step="0.01"
                               min="0.1"
                               max="999.99"
                               required>
                        @error('prix_unitaire')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="fas fa-lightbulb me-1"></i>
                            Prix suggéré : {{ number_format($prixUnitaire, 2) }} DH/L
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded">
                        <h6>Calcul Prévisionnel :</h6>
                        <div id="calcul_previsionnel">
                            <span id="quantite_calc">0</span> L × 
                            <span id="prix_calc">{{ number_format($prixUnitaire, 2) }}</span> DH/L = 
                            <strong id="montant_calc">0.00 DH</strong>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note :</strong> Une fois calculé, le paiement sera en attente de validation par la direction.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-calculator me-1"></i>Calculer Paiement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.bg-opacity-10 {
    --bs-bg-opacity: 0.1;
}

.quinzaine-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.quinzaine-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.alert {
    border: none;
    border-radius: 8px;
}

.card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal setup
    const calculerModal = document.getElementById('calculerModal');
    const prixInput = document.getElementById('prix_unitaire');
    let currentQuantite = 0;

    if (calculerModal) {
        calculerModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            
            // Get data from button
            const dateDebut = button.getAttribute('data-debut');
            const dateFin = button.getAttribute('data-fin');
            const label = button.getAttribute('data-label');
            const quantite = parseFloat(button.getAttribute('data-quantite'));
            
            currentQuantite = quantite;
            
            // Update modal content
            document.getElementById('modal_date_debut').value = dateDebut;
            document.getElementById('modal_date_fin').value = dateFin;
            document.getElementById('modal_periode').textContent = label;
            document.getElementById('modal_quantite').textContent = quantite.toFixed(1);
            
            // Update calcul
            updateCalcul();
        });

        // Update calculation when price changes
        prixInput.addEventListener('input', updateCalcul);
    }

    function updateCalcul() {
        const prix = parseFloat(prixInput.value) || 0;
        const montant = currentQuantite * prix;
        
        document.getElementById('quantite_calc').textContent = currentQuantite.toFixed(1);
        document.getElementById('prix_calc').textContent = prix.toFixed(2);
        document.getElementById('montant_calc').textContent = montant.toFixed(2) + ' DH';
    }
});
</script>
@endpush