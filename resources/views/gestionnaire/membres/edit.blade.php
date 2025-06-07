@extends('gestionnaire.layouts.app')

@section('title', 'Modifier Membre - ' . $membre->nom_complet)
@section('page-title', 'Modifier le Membre')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('gestionnaire.membres.show', $membre) }}" class="btn btn-outline-info">
            <i class="fas fa-eye me-2"></i>Voir détails
        </a>
        <a href="{{ route('gestionnaire.membres.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour à la liste
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Left Column - Current Info -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0">
                    <i class="fas fa-user me-2"></i>Informations actuelles
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 60px; height: 60px;">
                        <i class="fas fa-user text-white" style="font-size: 1.5rem;"></i>
                    </div>
                    <h5 class="mt-2 mb-1">{{ $membre->nom_complet }}</h5>
                    <span class="badge bg-{{ $membre->statut_color }}">{{ $membre->statut_label }}</span>
                </div>

                <div class="small">
                    <div class="mb-2">
                        <strong>CIN:</strong> {{ $membre->numero_carte_nationale }}
                    </div>
                    <div class="mb-2">
                        <strong>Email:</strong> {{ $membre->email }}
                    </div>
                    <div class="mb-2">
                        <strong>Tél:</strong> {{ $membre->telephone }}
                    </div>
                    <div class="mb-2">
                        <strong>Coopérative:</strong> {{ $membre->cooperative->nom_cooperative }}
                    </div>
                    <div class="mb-2">
                        <strong>Inscrit le:</strong> {{ $membre->created_at->format('d/m/Y') }}
                    </div>
                </div>

                @if($membre->statut === 'suppression' && $membre->raison_suppression)
                    <div class="alert alert-danger mt-3">
                        <small><strong>Raison suppression:</strong><br>{{ $membre->raison_suppression }}</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column - Edit Form -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-edit me-2"></i>Modifier les informations
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('gestionnaire.membres.update', $membre) }}" novalidate>
                    @csrf
                    @method('PUT')
                    
                    <!-- Cooperative Selection -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="id_cooperative" class="form-label">
                                <i class="fas fa-building me-2 text-primary"></i>
                                Coopérative <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('id_cooperative') is-invalid @enderror" 
                                    id="id_cooperative" 
                                    name="id_cooperative" 
                                    required>
                                <option value="">-- Sélectionnez une coopérative --</option>
                                @foreach($cooperatives as $cooperative)
                                    <option value="{{ $cooperative->id_cooperative }}" 
                                            {{ (old('id_cooperative', $membre->id_cooperative) == $cooperative->id_cooperative) ? 'selected' : '' }}>
                                        {{ $cooperative->nom_cooperative }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_cooperative')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
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
                                   value="{{ old('nom_complet', $membre->nom_complet) }}"
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
                                   value="{{ old('numero_carte_nationale', $membre->numero_carte_nationale) }}"
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
                                   value="{{ old('email', $membre->email) }}"
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
                                   value="{{ old('telephone', $membre->telephone) }}"
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
                                      required>{{ old('adresse', $membre->adresse) }}</textarea>
                            @error('adresse')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Status Information -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Information :</strong> Pour modifier le statut du membre (actif/inactif), 
                        utilisez les boutons d'action depuis la liste des membres ou la page de détails.
                    </div>

                    <!-- Change Detection -->
                    <div class="alert alert-warning d-none" id="changesAlert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Modifications détectées :</strong> 
                        <span id="changedFields"></span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('gestionnaire.membres.show', $membre) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <span id="submitText">
                                <i class="fas fa-save me-2"></i>Enregistrer les modifications
                            </span>
                            <span id="loadingText" class="d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Enregistrement...
                            </span>
                        </button>
                    </div>
                </form>
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
    const changesAlert = document.getElementById('changesAlert');
    const changedFields = document.getElementById('changedFields');

    // Original values for change detection
    const originalValues = {
        id_cooperative: '{{ $membre->id_cooperative }}',
        nom_complet: '{{ $membre->nom_complet }}',
        numero_carte_nationale: '{{ $membre->numero_carte_nationale }}',
        email: '{{ $membre->email }}',
        telephone: '{{ $membre->telephone }}',
        adresse: `{{ $membre->adresse }}`
    };

    const inputs = {
        id_cooperative: document.getElementById('id_cooperative'),
        nom_complet: document.getElementById('nom_complet'),
        numero_carte_nationale: document.getElementById('numero_carte_nationale'),
        email: document.getElementById('email'),
        telephone: document.getElementById('telephone'),
        adresse: document.getElementById('adresse')
    };

    // Form submission handler
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitText.classList.add('d-none');
        loadingText.classList.remove('d-none');
    });

    // Change detection
    Object.keys(inputs).forEach(key => {
        const input = inputs[key];
        input.addEventListener('input', function() {
            detectChanges();
            validateInput(input);
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

    function detectChanges() {
        const changedFieldsList = [];
        let hasChanges = false;

        Object.keys(inputs).forEach(key => {
            const currentValue = inputs[key].value.trim();
            const originalValue = originalValues[key].trim();
            
            if (currentValue !== originalValue) {
                hasChanges = true;
                const fieldLabels = {
                    id_cooperative: 'Coopérative',
                    nom_complet: 'Nom complet',
                    numero_carte_nationale: 'CIN',
                    email: 'Email',
                    telephone: 'Téléphone',
                    adresse: 'Adresse'
                };
                changedFieldsList.push(fieldLabels[key]);
            }
        });

        if (hasChanges) {
            submitBtn.disabled = false;
            changesAlert.classList.remove('d-none');
            changedFields.textContent = changedFieldsList.join(', ');
        } else {
            submitBtn.disabled = true;
            changesAlert.classList.add('d-none');
        }
    }

    function validateInput(input) {
        if (input.hasAttribute('required') && !input.value.trim()) {
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
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

    // Warn before leaving with unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (!submitBtn.disabled && changesAlert && !changesAlert.classList.contains('d-none')) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Initial change detection
    detectChanges();
});
</script>
@endpush