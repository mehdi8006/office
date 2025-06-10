@extends('direction.layouts.app')

@section('title', 'Créer un Utilisateur')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('direction.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('direction.utilisateurs.index') }}">Utilisateurs</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Créer un Nouvel Utilisateur</h1>
                <a href="{{ route('direction.utilisateurs.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>

            <!-- Formulaire -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Informations de l'Utilisateur
                    </h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('direction.utilisateurs.store') }}">
                        @csrf

                        <!-- Nom complet -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="nom_complet" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    Nom Complet <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('nom_complet') is-invalid @enderror" 
                                       id="nom_complet" 
                                       name="nom_complet" 
                                       value="{{ old('nom_complet') }}" 
                                       placeholder="Ex: Ahmed Ben Mohamed"
                                       required>
                                @error('nom_complet')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Email et Téléphone -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope text-primary me-1"></i>
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="exemple@sgccl.ma"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="telephone" class="form-label">
                                    <i class="fas fa-phone text-primary me-1"></i>
                                    Téléphone <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('telephone') is-invalid @enderror" 
                                       id="telephone" 
                                       name="telephone" 
                                       value="{{ old('telephone') }}" 
                                       placeholder="0661-123456"
                                       required>
                                @error('telephone')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Rôle et Statut -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="role" class="form-label">
                                    <i class="fas fa-user-tag text-primary me-1"></i>
                                    Rôle <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('role') is-invalid @enderror" 
                                        id="role" 
                                        name="role" 
                                        required>
                                    <option value="">Sélectionner un rôle</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                                            {{ ucfirst($role) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="statut" class="form-label">
                                    <i class="fas fa-toggle-on text-primary me-1"></i>
                                    Statut <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('statut') is-invalid @enderror" 
                                        id="statut" 
                                        name="statut" 
                                        required>
                                    <option value="actif" {{ old('statut', 'actif') == 'actif' ? 'selected' : '' }}>
                                        Actif
                                    </option>
                                    <option value="inactif" {{ old('statut') == 'inactif' ? 'selected' : '' }}>
                                        Inactif
                                    </option>
                                </select>
                                @error('statut')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Mot de passe -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="mot_de_passe" class="form-label">
                                    <i class="fas fa-lock text-primary me-1"></i>
                                    Mot de Passe <span class="text-danger">*</span>
                                </label>
                                <input type="password" 
                                       class="form-control @error('mot_de_passe') is-invalid @enderror" 
                                       id="mot_de_passe" 
                                       name="mot_de_passe" 
                                       placeholder="Minimum 8 caractères"
                                       required>
                                @error('mot_de_passe')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="mot_de_passe_confirmation" class="form-label">
                                    <i class="fas fa-lock text-primary me-1"></i>
                                    Confirmer le Mot de Passe <span class="text-danger">*</span>
                                </label>
                                <input type="password" 
                                       class="form-control" 
                                       id="mot_de_passe_confirmation" 
                                       name="mot_de_passe_confirmation" 
                                       placeholder="Répétez le mot de passe"
                                       required>
                            </div>
                        </div>

                        <!-- Information sur les rôles -->
                        <div class="alert alert-info" role="alert">
                            <h6><i class="fas fa-info-circle me-2"></i>Information sur les rôles :</h6>
                            <ul class="mb-0">
                                <li><strong>Direction :</strong> Accès complet au système, gestion des coopératives et utilisateurs</li>
                                <li><strong>Gestionnaire :</strong> Gestion d'une coopérative spécifique (membres, réceptions, etc.)</li>
                                <li><strong>USVA :</strong> Gestion de l'usine et des livraisons</li>
                            </ul>
                        </div>

                        <!-- Information supplémentaire -->
                        <div class="alert alert-secondary" role="alert">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Note :</strong> Le matricule de l'utilisateur sera généré automatiquement lors de la création.
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('direction.utilisateurs.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>
                                Créer l'Utilisateur
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Conseils de sécurité -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Conseils de Sécurité
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-key text-warning me-1"></i> Mot de passe fort :</h6>
                            <ul class="small">
                                <li>Minimum 8 caractères</li>
                                <li>Mélange de lettres et chiffres</li>
                                <li>Éviter les mots courants</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-user-shield text-info me-1"></i> Gestion des accès :</h6>
                            <ul class="small">
                                <li>Créer uniquement les comptes nécessaires</li>
                                <li>Vérifier régulièrement les utilisateurs actifs</li>
                                <li>Désactiver les comptes inutilisés</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Afficher des conseils selon le rôle sélectionné
    const roleSelect = document.getElementById('role');
    const roleInfo = {
        'direction': 'Accès complet au système. À réserver aux responsables de direction uniquement.',
        'gestionnaire': 'Sera assigné à une coopérative pour la gérer. Un gestionnaire = une coopérative.',
        'usva': 'Accès aux fonctionnalités de l\'usine et gestion des livraisons.'
    };

    roleSelect.addEventListener('change', function() {
        const selectedRole = this.value;
        const existingAlert = document.querySelector('.role-info-alert');
        
        if (existingAlert) {
            existingAlert.remove();
        }

        if (selectedRole && roleInfo[selectedRole]) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-light border role-info-alert mt-2';
            alert.innerHTML = `<small><i class="fas fa-info-circle me-1"></i> ${roleInfo[selectedRole]}</small>`;
            roleSelect.parentElement.appendChild(alert);
        }
    });

    // Validation en temps réel du mot de passe
    const password = document.getElementById('mot_de_passe');
    const confirmPassword = document.getElementById('mot_de_passe_confirmation');

    function validatePassword() {
        if (password.value.length > 0 && password.value.length < 8) {
            password.classList.add('is-invalid');
        } else {
            password.classList.remove('is-invalid');
        }

        if (confirmPassword.value.length > 0 && password.value !== confirmPassword.value) {
            confirmPassword.classList.add('is-invalid');
        } else {
            confirmPassword.classList.remove('is-invalid');
        }
    }

    password.addEventListener('input', validatePassword);
    confirmPassword.addEventListener('input', validatePassword);
});
</script>
@endpush
@endsection