@extends('gestionnaire.layouts.app')

@section('title', 'Nouveau Membre Éleveur')
@section('page-title', 'Nouveau Membre Éleveur')

@section('page-actions')
    <a href="{{ route('gestionnaire.membres.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-plus me-2"></i>Informations du nouveau membre
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('gestionnaire.membres.store') }}" novalidate>
                    @csrf
                    
                    <!-- Cooperative Selection -->
                    <!-- Cooperative Information (Read-only) -->
<div class="row mb-3">
    <div class="col-md-12">
        <label class="form-label">
            <i class="fas fa-building me-2 text-primary"></i>
            Coopérative
        </label>
        <div class="form-control bg-light d-flex align-items-center">
            <i class="fas fa-info-circle text-info me-2"></i>
            <strong>{{ $cooperative->nom_cooperative }}</strong>
            <span class="badge bg-primary ms-2">{{ $cooperative->matricule }}</span>
        </div>
        <small class="text-muted">Le membre sera automatiquement assigné à votre coopérative.</small>
    </div>
</div>
                    <!-- Personal Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nom_complet" class="form-label">
                                <i class="fas fa-user me-2 text-success"></i>
                                Nom complet <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('nom_complet') is-invalid @enderror" 
                                   id="nom_complet" 
                                   name="nom_complet" 
                                   value="{{ old('nom_complet') }}"
                                   placeholder="Nom et prénom complets"
                                   required>
                            @error('nom_complet')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="numero_carte_nationale" class="form-label">
                                <i class="fas fa-id-card me-2 text-info"></i>
                                Numéro CIN <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('numero_carte_nationale') is-invalid @enderror" 
                                   id="numero_carte_nationale" 
                                   name="numero_carte_nationale" 
                                   value="{{ old('numero_carte_nationale') }}"
                                   placeholder="Numéro de carte nationale"
                                   required>
                            @error('numero_carte_nationale')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2 text-warning"></i>
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   placeholder="adresse@email.com"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="telephone" class="form-label">
                                <i class="fas fa-phone me-2 text-danger"></i>
                                Téléphone <span class="text-danger">*</span>
                            </label>
                            <input type="tel" 
                                   class="form-control @error('telephone') is-invalid @enderror" 
                                   id="telephone" 
                                   name="telephone" 
                                   value="{{ old('telephone') }}"
                                   placeholder="0661234567"
                                   required>
                            @error('telephone')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="adresse" class="form-label">
                                <i class="fas fa-map-marker-alt me-2 text-secondary"></i>
                                Adresse <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                      id="adresse" 
                                      name="adresse" 
                                      rows="3"
                                      placeholder="Adresse complète du membre"
                                      required>{{ old('adresse') }}</textarea>
                            @error('adresse')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Information Note -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Information :</strong> Le membre sera automatiquement activé après la création. 
                        Vous pourrez modifier son statut depuis la liste des membres.
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('gestionnaire.membres.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <span id="submitText">
                                <i class="fas fa-save me-2"></i>Créer le membre
                            </span>
                            <span id="loadingText" class="d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Création en cours...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Card (will be populated with JS) -->
        <div class="card mt-4 d-none" id="previewCard">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0">
                    <i class="fas fa-eye me-2"></i>Aperçu des informations
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nom :</strong> <span id="preview-nom"></span><br>
                        <strong>CIN :</strong> <span id="preview-cin"></span><br>
                        <strong>Email :</strong> <span id="preview-email"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Téléphone :</strong> <span id="preview-telephone"></span><br>
                        <strong>Coopérative :</strong> <span id="preview-cooperative"></span><br>
                        <strong>Adresse :</strong> <span id="preview-adresse"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const loadingText = document.getElementById('loadingText');
    const previewCard = document.getElementById('previewCard');

    // Form submission handler
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitText.classList.add('d-none');
        loadingText.classList.remove('d-none');
    });

    // Input validation and preview
    const inputs = {
        nom_complet: document.getElementById('nom_complet'),
        numero_carte_nationale: document.getElementById('numero_carte_nationale'),
        email: document.getElementById('email'),
        telephone: document.getElementById('telephone'),
        adresse: document.getElementById('adresse'),
        id_cooperative: document.getElementById('id_cooperative')
    };

    // Real-time validation
    Object.keys(inputs).forEach(key => {
        const input = inputs[key];
        input.addEventListener('input', function() {
            validateInput(input);
            updatePreview();
        });
    });

    // Phone number formatting
    inputs.telephone.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 10) value = value.substring(0, 10);
        this.value = value;
    });

    // CIN formatting (uppercase)
    inputs.numero_carte_nationale.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Email validation
    inputs.email.addEventListener('blur', function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value && !emailRegex.test(this.value)) {
            this.classList.add('is-invalid');
            let feedback = this.parentNode.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                this.parentNode.appendChild(feedback);
            }
            feedback.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i>Format d\'email invalide';
        } else {
            this.classList.remove('is-invalid');
        }
    });

    function validateInput(input) {
        if (input.hasAttribute('required') && !input.value.trim()) {
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    }

    function updatePreview() {
        const allFilled = Object.values(inputs).every(input => input.value.trim());
        
        if (allFilled) {
            previewCard.classList.remove('d-none');
            
            document.getElementById('preview-nom').textContent = inputs.nom_complet.value;
            document.getElementById('preview-cin').textContent = inputs.numero_carte_nationale.value;
            document.getElementById('preview-email').textContent = inputs.email.value;
            document.getElementById('preview-telephone').textContent = inputs.telephone.value;
            document.getElementById('preview-adresse').textContent = inputs.adresse.value;
            
            const cooperativeSelect = inputs.id_cooperative;
            const cooperativeName = cooperativeSelect.options[cooperativeSelect.selectedIndex].text;
            document.getElementById('preview-cooperative').textContent = cooperativeName;
        } else {
            previewCard.classList.add('d-none');
        }
    }

    // Form validation on submit
    form.addEventListener('submit', function(e) {
        let hasErrors = false;
        
        Object.values(inputs).forEach(input => {
            if (input.hasAttribute('required') && !input.value.trim()) {
                input.classList.add('is-invalid');
                hasErrors = true;
            }
        });

        // Email validation
        if (inputs.email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(inputs.email.value)) {
            inputs.email.classList.add('is-invalid');
            hasErrors = true;
        }

        // Phone validation
        if (inputs.telephone.value && inputs.telephone.value.length < 10) {
            inputs.telephone.classList.add('is-invalid');
            hasErrors = true;
        }

        if (hasErrors) {
            e.preventDefault();
            submitBtn.disabled = false;
            submitText.classList.remove('d-none');
            loadingText.classList.add('d-none');
            
            showToast('Veuillez corriger les erreurs dans le formulaire', 'error');
        }
    });
});
</script>
@endpush