@extends('gestionnaire.layouts.app')

@section('title', 'Nouvelle Livraison vers l\'Usine')
@section('page-title', 'Nouvelle Livraison vers l\'Usine')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('gestionnaire.stock.show', $stock->date_stock->format('Y-m-d')) }}" class="btn btn-outline-info">
            <i class="fas fa-eye me-2"></i>Voir Stock
        </a>
        <a href="{{ route('gestionnaire.livraisons.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour aux livraisons
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Left Column - Stock Information -->
    <div class="col-lg-4 mb-4">
        <!-- Stock Summary Card -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-warehouse me-2"></i>Stock Disponible
                </h5>
                <small>{{ $stock->date_stock->format('d/m/Y') }} - {{ $stock->date_stock->translatedFormat('l') }}</small>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-12">
                        <div class="bg-light p-3 rounded">
                            <h3 class="text-success mb-1">{{ $stock->quantite_disponible_formattee }}</h3>
                            <small class="text-muted">Stock Disponible</small>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="text-center">
                            <h5 class="text-primary mb-1">{{ $stock->quantite_totale_formattee }}</h5>
                            <small class="text-muted">Total Reçu</small>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="text-center">
                            <h5 class="text-warning mb-1">{{ $stock->quantite_livree_formattee }}</h5>
                            <small class="text-muted">Déjà Livré</small>
                        </div>
                    </div>
                </div>

                @if($stock->quantite_totale > 0)
                    <div class="mt-3">
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-warning" 
                                 style="width: {{ $stock->percentage_livre }}%"
                                 title="Livré: {{ $stock->percentage_livre }}%">
                                {{ number_format($stock->percentage_livre, 1) }}%
                            </div>
                        </div>
                        <small class="text-muted">{{ number_format($stock->percentage_livre, 1) }}% du stock livré</small>
                    </div>
                @endif

                <div class="mt-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Coopérative:</span>
                        <span class="badge bg-primary">{{ $cooperative->nom_cooperative }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Existing Livraisons Card -->
        @if($existingLivraisons->count() > 0)
        <div class="card mt-4">
            <div class="card-header bg-warning text-dark">
                <h6 class="card-title mb-0">
                    <i class="fas fa-truck me-2"></i>Livraisons Existantes
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Quantité</th>
                                <th>Montant</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($existingLivraisons as $livraison)
                                <tr>
                                    <td>{{ number_format($livraison->quantite_litres, 2) }} L</td>
                                    <td>{{ number_format($livraison->montant_total, 2) }} DH</td>
                                    <td>
                                        <span class="badge bg-{{ $livraison->statut_color }} badge-sm">
                                            {{ $livraison->statut_label }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Right Column - Livraison Form -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-plus-circle me-2"></i>Détails de la Livraison
                </h5>
            </div>
            <div class="card-body">
                @if($stock->quantite_disponible <= 0)
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Aucun stock disponible</strong> pour cette date. 
                        Impossible de créer une livraison.
                    </div>
                    <a href="{{ route('gestionnaire.stock.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour au stock
                    </a>
                @else
                    <form method="POST" action="{{ route('gestionnaire.livraisons.store') }}">
                        @csrf
                        
                        <!-- Hidden date field -->
                        <input type="hidden" name="date_livraison" value="{{ $stock->date_stock->format('Y-m-d') }}">
                        
                        <!-- Date Display -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">
                                    <i class="fas fa-calendar me-2 text-primary"></i>
                                    Date de Livraison
                                </label>
                                <div class="form-control bg-light">
                                    <strong>{{ $stock->date_stock->format('d/m/Y') }}</strong>
                                    <span class="text-muted ms-2">({{ $stock->date_stock->translatedFormat('l') }})</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity Input -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="quantite_litres" class="form-label">
                                    <i class="fas fa-tint me-2 text-info"></i>
                                    Quantité à Livrer (Litres) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('quantite_litres') is-invalid @enderror" 
                                           id="quantite_litres" 
                                           name="quantite_litres" 
                                           value="{{ old('quantite_litres') }}"
                                           placeholder="0.00"
                                           min="0.1"
                                           max="{{ $stock->quantite_disponible }}"
                                           step="0.01"
                                           required>
                                    <span class="input-group-text">L</span>
                                </div>
                                @error('quantite_litres')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    Maximum disponible: <strong>{{ $stock->quantite_disponible_formattee }}</strong>
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label for="prix_unitaire" class="form-label">
                                    <i class="fas fa-money-bill me-2 text-success"></i>
                                    Prix Unitaire (DH/L) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('prix_unitaire') is-invalid @enderror" 
                                           id="prix_unitaire" 
                                           name="prix_unitaire" 
                                           value="{{ old('prix_unitaire', '3.50') }}"
                                           placeholder="0.00"
                                           min="0.1"
                                           max="999.99"
                                           step="0.01"
                                           required>
                                    <span class="input-group-text">DH</span>
                                </div>
                                @error('prix_unitaire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Prix par litre</small>
                            </div>
                        </div>

                        <!-- Calculated Amount Display -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">
                                    <i class="fas fa-calculator me-2 text-warning"></i>
                                    Montant Total Calculé
                                </label>
                                <div class="form-control bg-light">
                                    <span id="montant_total" class="fw-bold text-success">0.00 DH</span>
                                    <small class="text-muted ms-2">(Quantité × Prix Unitaire)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Amount Buttons -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">
                                    <i class="fas fa-bolt me-2 text-primary"></i>
                                    Quantités Rapides
                                </label>
                                <div class="d-flex gap-2 flex-wrap">
                                    @php
                                        $quickAmounts = [];
                                        $available = $stock->quantite_disponible;
                                        
                                        if ($available >= 100) {
                                            $quickAmounts[] = 100;
                                        }
                                        if ($available >= 50) {
                                            $quickAmounts[] = 50;
                                        }
                                        if ($available >= 25) {
                                            $quickAmounts[] = 25;
                                        }
                                        if ($available >= 10) {
                                            $quickAmounts[] = 10;
                                        }
                                        
                                        // Add 25%, 50%, 75%, 100% of available
                                        $quickAmounts[] = round($available * 0.25, 2);
                                        $quickAmounts[] = round($available * 0.5, 2);
                                        $quickAmounts[] = round($available * 0.75, 2);
                                        $quickAmounts[] = $available;
                                        
                                        $quickAmounts = array_unique($quickAmounts);
                                        rsort($quickAmounts);
                                    @endphp
                                    
                                    @foreach($quickAmounts as $amount)
                                        @if($amount > 0)
                                            <button type="button" 
                                                    class="btn btn-outline-primary btn-sm quick-amount" 
                                                    data-amount="{{ $amount }}">
                                                {{ number_format($amount, 1) }}L
                                                @if($amount == $available)
                                                    <small>(Tout)</small>
                                                @endif
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Information Alert -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Information :</strong> 
                            La livraison sera créée avec le statut "Planifiée". 
                            Elle devra être validée avant de pouvoir être marquée comme payée.
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('gestionnaire.stock.show', $stock->date_stock->format('Y-m-d')) }}" 
                               class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-truck me-2"></i>Créer la Livraison
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantiteInput = document.getElementById('quantite_litres');
    const prixInput = document.getElementById('prix_unitaire');
    const montantDisplay = document.getElementById('montant_total');
    const quickAmountButtons = document.querySelectorAll('.quick-amount');

    // Function to calculate and display total amount
    function updateMontantTotal() {
        const quantite = parseFloat(quantiteInput.value) || 0;
        const prix = parseFloat(prixInput.value) || 0;
        const montant = quantite * prix;
        
        montantDisplay.textContent = montant.toLocaleString('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + ' DH';
    }

    // Update amount when inputs change
    quantiteInput.addEventListener('input', updateMontantTotal);
    prixInput.addEventListener('input', updateMontantTotal);

    // Quick amount buttons
    quickAmountButtons.forEach(button => {
        button.addEventListener('click', function() {
            const amount = this.dataset.amount;
            quantiteInput.value = amount;
            updateMontantTotal();
            
            // Remove active class from all buttons
            quickAmountButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
        });
    });

    // Validate quantity on input
    quantiteInput.addEventListener('input', function() {
        const maxAmount = {{ $stock->quantite_disponible }};
        const currentValue = parseFloat(this.value);
        
        if (currentValue > maxAmount) {
            this.value = maxAmount;
            this.classList.add('is-invalid');
            
            // Show warning
            let feedback = this.parentNode.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                this.parentNode.appendChild(feedback);
            }
            feedback.textContent = `Quantité maximale disponible: ${maxAmount.toFixed(2)} L`;
        } else {
            this.classList.remove('is-invalid');
        }
        
        updateMontantTotal();
    });

    // Initial calculation
    updateMontantTotal();
});
</script>

<style>
.quick-amount.active {
    background-color: var(--bs-primary);
    color: white;
}

.quick-amount:hover {
    transform: translateY(-1px);
}

.badge-sm {
    font-size: 0.65rem;
}
</style>
@endpush