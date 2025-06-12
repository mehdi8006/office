@extends('gestionnaire.layouts.app')

@section('title', 'Gestion des Paiements Usine')
@section('page-title', 'Paiements Usine - ' . \Carbon\Carbon::create($selectedYear, $selectedMonth)->translatedFormat('F Y'))

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
<!-- Sélection du mois -->
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-calendar me-2"></i>Sélection du Mois
            </h5>
            <div class="d-flex align-items-center">
                <span class="text-muted me-2">Coopérative :</span>
                <span class="badge bg-primary fs-6">{{ $cooperative->nom_cooperative }}</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('gestionnaire.paiements.index') }}" class="row g-3">
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

<!-- Carte Prix Unitaire -->
<div class="row mb-4">
    <div class="col-lg-4 mb-4">
        <div class="card border-info h-100">
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

    <!-- Cartes Quinzaines -->
    @foreach($quinzaines as $index => $quinzaine)
        <div class="col-lg-4 mb-4">
            <div class="card border-{{ $quinzaine['statut_color'] }} h-100">
                <div class="card-header bg-{{ $quinzaine['statut_color'] }} bg-opacity-10">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-calendar me-2"></i>{{ $quinzaine['label'] }}
                        </h6>
                        <span class="badge bg-{{ $quinzaine['statut_color'] }}">
                            @switch($quinzaine['statut'])
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
                    </div>
                </div>
                
                <div class="card-body">
                    @if($quinzaine['total_quantite'] > 0)
                        <!-- Quantité -->
                        <div class="text-center mb-3">
                            <h4 class="text-primary mb-1">{{ number_format($quinzaine['total_quantite'], 1) }} L</h4>
                            <small class="text-muted">Quantité livrée</small>
                        </div>

                        <!-- Montant -->
                        @if($quinzaine['statut'] !== 'non_calcule')
                            <div class="text-center mb-3">
                                <div class="bg-light p-3 rounded">
                                    <h4 class="text-success mb-1">{{ number_format($quinzaine['montant_calcule'], 2) }} DH</h4>
                                    <small class="text-muted">Montant calculé</small>
                                </div>
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="text-center">
                            @if($quinzaine['peut_calculer'])
                                <button type="button" 
                                        class="btn btn-warning"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#calculerModal"
                                        data-debut="{{ $quinzaine['date_debut'] }}"
                                        data-fin="{{ $quinzaine['date_fin'] }}"
                                        data-label="{{ $quinzaine['label'] }}"
                                        data-quantite="{{ $quinzaine['total_quantite'] }}">
                                    <i class="fas fa-calculator me-1"></i>Calculer
                                </button>
                            @elseif($quinzaine['statut'] === 'en_attente')
                                <form action="{{ route('gestionnaire.paiements.marquer-paye-quinzaine') }}" 
                                      method="POST" 
                                      style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="paiement_id" value="{{ $quinzaine['paiement']->id_paiement }}">
                                    <button type="submit" 
                                            class="btn btn-success"
                                            onclick="return confirm('Marquer cette quinzaine comme payée ?\n\nMontant: {{ number_format($quinzaine['montant_calcule'], 2) }} DH')">
                                        <i class="fas fa-check me-1"></i>Marquer Payé
                                    </button>
                                </form>
                            @elseif($quinzaine['statut'] === 'paye')
                                <span class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>Payé le {{ $quinzaine['paiement']->date_paiement->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>
                    @else
                        <!-- Aucune livraison -->
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-truck" style="font-size: 2rem; opacity: 0.3;"></i>
                            <p class="mt-2 mb-0">Aucune livraison pour cette quinzaine</p>
                        </div>
                    @endif
                </div>

                <!-- Footer avec dates -->
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>
                        {{ \Carbon\Carbon::parse($quinzaine['date_debut'])->format('d/m/Y') }} - 
                        {{ \Carbon\Carbon::parse($quinzaine['date_fin'])->format('d/m/Y') }}
                    </small>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Tableau des Paiements en Attente -->
@if($paiementsEnAttente->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-clock me-2 text-warning"></i>
            Paiements en Attente ({{ $paiementsEnAttente->count() }})
        </h5>
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
                        <th width="150px">Actions</th>
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
                                <form action="{{ route('gestionnaire.paiements.marquer-paye-quinzaine') }}" 
                                      method="POST" 
                                      style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="paiement_id" value="{{ $paiement->id_paiement }}">
                                    <button type="submit" 
                                            class="btn btn-sm btn-success"
                                            onclick="return confirm('Marquer comme payé ?\n\nQuinzaine: {{ $paiement->quinzaine_label }}\nMontant: {{ $paiement->montant_formattee }}')">
                                        <i class="fas fa-check me-1"></i>Marquer Payé
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal setup
    const calculerModal = document.getElementById('calculerModal');
    const prixInput = document.getElementById('prix_unitaire');
    let currentQuantite = 0;

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