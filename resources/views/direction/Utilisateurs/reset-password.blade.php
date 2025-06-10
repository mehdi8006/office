@extends('direction.layouts.app')

@section('title', 'Réinitialiser le Mot de Passe')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('direction.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('direction.utilisateurs.index') }}">Utilisateurs</a></li>
    <li class="breadcrumb-item"><a href="{{ route('direction.utilisateurs.show', $utilisateur) }}">{{ $utilisateur->nom_complet }}</a></li>
    <li class="breadcrumb-item active">Réinitialiser mot de passe</li>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h4">Réinitialiser le Mot de Passe</h1>
                <a href="{{ route('direction.utilisateurs.show', $utilisateur) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>

            <!-- Information sur l'utilisateur -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>
                        Utilisateur Concerné
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <strong>Nom :</strong>
                            <br>{{ $utilisateur->nom_complet }}
                        </div>
                        <div class="col-6">
                            <strong>Matricule :</strong>
                            <br><span class="badge bg-secondary">{{ $utilisateur->matricule }}</span>
                        </div>
                        <div class="col-6 mt-2">
                            <strong>Email :</strong>
                            <br>{{ $utilisateur->email }}
                        </div>
                        <div class="col-6 mt-2">
                            <strong>Rôle :</strong>
                            <br><span class="badge bg-primary">{{ ucfirst($utilisateur->role) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Avertissement de sécurité -->
            <div class="alert alert-danger" role="alert">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Attention - Action Sensible</h6>
                <ul class="mb-0">
                    <li>Vous êtes sur le point de réinitialiser le mot de passe de cet utilisateur</li>
                    <li>L'utilisateur devra utiliser le nouveau mot de passe pour se connecter</li>
                    <li>Communiquez le nouveau mot de passe de manière sécurisée</li>
                    <li>Recommandez à l'utilisateur de changer son mot de passe à la prochaine connexion</li>
                </ul>
            </div>

            <!-- Formulaire -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>
                        Nouveau Mot de Passe
                    </h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('direction.utilisateurs.reset-password.update', $utilisateur) }}">
                        @csrf
                        @method('PUT')

                        <!-- Nouveau mot de passe -->
                        <div class="mb-3">
                            <label for="mot_de_passe" class="form-label">
                                <i class="fas fa-lock text-primary me-1"></i>
                                Nouveau Mot de Passe <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('mot_de_passe') is-invalid @enderror" 
                                       id="mot_de_passe" 
                                       name="mot_de_passe" 
                                       placeholder="Minimum 8 caractères"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('mot_de_passe')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Le mot de passe doit contenir au moins 8 caractères.
                            </div>
                        </div>

                        <!-- Confirmation -->
                        <div class="mb-3">
                            <label for="mot_de_passe_confirmation" class="form-label">
                                <i class="fas fa-lock text-primary me-1"></i>
                                Confirmer le Nouveau Mot de Passe <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="mot_de_passe_confirmation" 
                                   name="mot_de_passe_confirmation" 
                                   placeholder="Répétez le nouveau mot de passe"
                                   required>
                            <div class="form-text">
                                <i class="fas fa-check-circle me-1"></i>
                                Saisissez à nouveau le mot de passe pour confirmation.
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('direction.utilisateurs.show', $utilisateur) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Êtes-vous sûr de vouloir réinitialiser le mot de passe de cet utilisateur ?')">
                                <i class="fas fa-key me-1"></i>
                                Réinitialiser le Mot de Passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Générateur de mot de passe -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-magic me-2"></i>
                        Générateur de Mot de Passe Sécurisé
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <input type="text" 
                                   class="form-control" 
                                   id="generatedPassword" 
                                   placeholder="Cliquez sur 'Générer' pour créer un mot de passe"
                                   readonly>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-info w-100" onclick="generatePassword()">
                                <i class="fas fa-magic me-1"></i>
                                Générer
                            </button>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-success btn-sm w-100" onclick="useGeneratedPassword()">
                                <i class="fas fa-arrow-up me-1"></i>
                                Utiliser ce mot de passe
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-info btn-sm w-100" onclick="copyToClipboard()">
                                <i class="fas fa-copy me-1"></i>
                                Copier
                            </button>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <i class="fas fa-shield-alt me-1"></i>
                        Le générateur crée des mots de passe sécurisés avec lettres, chiffres et caractères spéciaux.
                    </small>
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
                            <h6><i class="fas fa-check text-success me-1"></i> Bonnes pratiques :</h6>
                            <ul class="small">
                                <li>Utilisez le générateur de mot de passe</li>
                                <li>Communiquez le mot de passe de manière sécurisée</li>
                                <li>Demandez à l'utilisateur de le changer</li>
                                <li>Ne gardez pas de trace écrite non sécurisée</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-times text-danger me-1"></i> À éviter :</h6>
                            <ul class="small">
                                <li>Mots de passe trop simples</li>
                                <li>Envoi par email non chiffré</li>
                                <li>Réutilisation d'anciens mots de passe</li>
                                <li>Partage avec des tiers non autorisés</li>
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
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('mot_de_passe');
    
    togglePassword.addEventListener('click', function() {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });

    // Validation en temps réel
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

// Générateur de mot de passe sécurisé
function generatePassword() {
    const length = 12;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
    let password = "";
    
    // S'assurer qu'on a au moins un caractère de chaque type
    password += "abcdefghijklmnopqrstuvwxyz"[Math.floor(Math.random() * 26)]; // minuscule
    password += "ABCDEFGHIJKLMNOPQRSTUVWXYZ"[Math.floor(Math.random() * 26)]; // majuscule
    password += "0123456789"[Math.floor(Math.random() * 10)]; // chiffre
    password += "!@#$%^&*"[Math.floor(Math.random() * 8)]; // caractère spécial
    
    // Compléter avec des caractères aléatoires
    for (let i = password.length; i < length; i++) {
        password += charset[Math.floor(Math.random() * charset.length)];
    }
    
    // Mélanger le mot de passe
    password = password.split('').sort(() => Math.random() - 0.5).join('');
    
    document.getElementById('generatedPassword').value = password;
}

// Utiliser le mot de passe généré
function useGeneratedPassword() {
    const generatedPassword = document.getElementById('generatedPassword').value;
    if (generatedPassword) {
        document.getElementById('mot_de_passe').value = generatedPassword;
        document.getElementById('mot_de_passe_confirmation').value = generatedPassword;
        
        // Validation visuelle
        document.getElementById('mot_de_passe').classList.remove('is-invalid');
        document.getElementById('mot_de_passe_confirmation').classList.remove('is-invalid');
        
        // Notification
        const toast = document.createElement('div');
        toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <i class="fas fa-check me-2"></i>
            Mot de passe appliqué avec succès !
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
    }
}

// Copier dans le presse-papiers
function copyToClipboard() {
    const generatedPassword = document.getElementById('generatedPassword');
    if (generatedPassword.value) {
        generatedPassword.select();
        generatedPassword.setSelectionRange(0, 99999); // Pour mobile
        
        try {
            document.execCommand('copy');
            
            // Notification
            const toast = document.createElement('div');
            toast.className = 'alert alert-info alert-dismissible fade show position-fixed';
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <i class="fas fa-copy me-2"></i>
                Mot de passe copié dans le presse-papiers !
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 3000);
        } catch (err) {
            console.error('Erreur lors de la copie:', err);
        }
    }
}
</script>
@endpush
@endsection