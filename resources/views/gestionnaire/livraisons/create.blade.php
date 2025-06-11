@extends('gestionnaire.layouts.app')

@section('title', 'Nouvelle Livraison vers l\'Usine')
@section('page-title', 'Nouvelle Livraison vers l\'Usine')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('gestionnaire.stock.index') }}" class="btn btn-outline-info">
            <i class="fas fa-warehouse me-2"></i>Voir Stock
        </a>
        <a href="{{ route('gestionnaire.livraisons.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour aux livraisons
        </a>
    </div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <!-- Stock Available Info -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <h3 class="text-success mb-2">{{ number_format($stockTotalCooperative, 2) }} L</h3>
                <p class="text-muted mb-0">Stock total disponible - {{ $cooperative->nom_cooperative }}</p>
            </div>
        </div>

        @if($stockTotalCooperative <= 0)
            <!-- No Stock Available -->
            <div class="card">
                <div class="card-body text-center">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Aucun stock disponible</strong> pour cette date.
                    </div>
                    <a href="{{ route('gestionnaire.stock.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour au stock
                    </a>
                </div>
            </div>
        @else
            <!-- Livraison Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Nouvelle Livraison
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('gestionnaire.livraisons.store') }}">
                        @csrf
                        
                        <!-- Hidden date field -->
                        <input type="hidden" name="date_livraison" value="{{ today()->format('Y-m-d') }}">>
                        
                        <!-- Hidden prix unitaire with default value -->
                        <input type="hidden" name="prix_unitaire" value="3.50">

                        <!-- Quantity Input -->
                        <div class="mb-4">
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
                                       max="{{ number_format($stockTotalCooperative, 2, '.', '') }}"
                                       step="0.01"
                                       required>
                                <span class="input-group-text">L</span>
                            </div>
                            @error('quantite_litres')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Maximum disponible: <strong>{{ number_format($stockTotalCooperative, 2) }} L</strong>
                            </small>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <a href="{{ route('gestionnaire.stock.index') }}" 
                               class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-truck me-2"></i>Créer la Livraison
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantiteInput = document.getElementById('quantite_litres');
    const maxAmount = {{ number_format($stockTotalCooperative, 2, '.', '') }};

    // Validate quantity on input
    quantiteInput.addEventListener('input', function() {
        const currentValue = parseFloat(this.value);
        
        if (currentValue > maxAmount) {
            this.value = maxAmount.toFixed(2);
            this.classList.add('is-invalid');
            
            // Show warning
            let feedback = this.parentNode.parentNode.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.style.display = 'block';
                this.parentNode.parentNode.appendChild(feedback);
            }
            feedback.textContent = `Quantité maximale disponible: ${maxAmount.toFixed(2)} L`;
        } else {
            this.classList.remove('is-invalid');
            // Remove custom feedback if exists
            let feedback = this.parentNode.parentNode.querySelector('.invalid-feedback');
            if (feedback && !feedback.textContent.includes('est requis')) {
                feedback.remove();
            }
        }
    });

    // Also validate on blur
    quantiteInput.addEventListener('blur', function() {
        const currentValue = parseFloat(this.value);
        if (currentValue > maxAmount) {
            this.value = maxAmount.toFixed(2);
        }
    });
});
</script>
@endpush