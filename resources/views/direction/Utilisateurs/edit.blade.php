@extends('direction.layouts.app')

@section('title', 'Modifier l\'Utilisateur')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('direction.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('direction.utilisateurs.index') }}">Utilisateurs</a></li>
    <li class="breadcrumb-item"><a href="{{ route('direction.utilisateurs.show', $utilisateur) }}">{{ $utilisateur->nom_complet }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Modifier l'Utilisateur</h1>
                <div>
                    <a href="{{ route('direction.utilisateurs.show', $utilisateur) }}" class="btn btn-outline-info me-2">
                        <i class="fas fa-eye"></i> Voir les détails
                    </a>
                    <a href="{{ route('direction.utilisateurs.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                </div>
            </div>

            <!-- Information sur l'utilisateur -->
            <div class="alert alert-info mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Matricule :</strong> {{ $utilisateur->matricule }}
                    </div>
                    <div class="col-md-6">
                        <strong>Créé le :</strong> {{ $utilisateur->created_at->format('d/m/Y à H:i') }}
                    </div>
                </div>
            </div>

            <!-- Formulaire -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Modifier les Informations
                    </h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('direction.utilisateurs.update', $utilisateur) }}">
                        @csrf
                        @method('PUT')

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
                                       value="{{ old('nom_complet', $utilisateur->nom_complet) }}" 
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
                                       value="{{ old('email', $utilisateur->email) }}" 
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
                                       value="{{ old('telephone', $utilisateur->telephone) }}" 
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
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}" {{ old('role', $utilisateur->role) == $role ? 'selected' : '' }}>
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
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Attention : changer le rôle peut affecter les permissions de l'utilisateur.
                                </div>
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
                                    <option value="actif" {{ old('statut', $utilisateur->statut) == 'actif' ? 'selected' : '' }}>
                                        Actif
                                    </option>
                                    <option value="inactif" {{ old('statut', $utilisateur->statut) == 'inactif' ? 'selected' : '' }}>
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

                        <!-- Avertissements selon le rôle actuel -->
                        @if($utilisateur->role === 'gestionnaire' && $utilisateur->cooperativeGeree)
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Gestionnaire avec coopérative assignée</h6>
                                <p class="mb-2">Cet utilisateur gère actuellement la coopérative :</p>
                                <div class="card border-warning">
                                    <div class="card-body py-2">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <strong>{{ $utilisateur->cooperativeGeree->nom_cooperative }}</strong>
                                                <br><small class="text-muted">{{ $utilisateur->cooperativeGeree->matricule }}</small>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <a href="{{ route('direction.cooperatives.show', $utilisateur->cooperativeGeree) }}" 
                                                   class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-eye me-1"></i>
                                                    Voir
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Si vous changez son rôle, il perdra l'accès à la gestion de cette coopérative.
                                </small>
                            </div>
                        @endif

                        <!-- Information sur le mot de passe -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-key me-2"></i>Mot de passe</h6>
                            <p class="mb-2">Le mot de passe ne peut pas être modifié via ce formulaire.</p>
                            <a href="{{ route('direction.utilisateurs.reset-password', $utilisateur) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-key me-1"></i>
                                Réinitialiser le mot de passe
                            </a>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('direction.utilisateurs.show', $utilisateur) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Actions Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            @if($utilisateur->statut == 'actif')
                                <form method="POST" 
                                      action="{{ route('direction.utilisateurs.deactivate', $utilisateur) }}" 
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver cet utilisateur ?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="fas fa-pause me-1"></i>
                                        Désactiver l'Utilisateur
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('direction.utilisateurs.activate', $utilisateur) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-play me-1"></i>
                                        Activer l'Utilisateur
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('direction.utilisateurs.reset-password', $utilisateur) }}" class="btn btn-warning w-100">
                                <i class="fas fa-key me-1"></i>
                                Réinitialiser le Mot de Passe
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('direction.utilisateurs.show', $utilisateur) }}" class="btn btn-info w-100">
                                <i class="fas fa-eye me-1"></i>
                                Voir les Détails
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historique des modifications -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Historique
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Compte créé :</strong>
                                <br>{{ $utilisateur->created_at->format('d/m/Y à H:i') }}
                                <br><small class="text-muted">{{ $utilisateur->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="col-md-6">
                                <strong>Dernière modification :</strong>
                                <br>{{ $utilisateur->updated_at->format('d/m/Y à H:i') }}
                                <br><small class="text-muted">{{ $utilisateur->updated_at->diffForHumans() }}</small>
                            </div>
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
    // Afficher des avertissements selon le changement de rôle
    const roleSelect = document.getElementById('role');
    const currentRole = '{{ $utilisateur->role }}';
    
    const roleWarnings = {
        'direction': 'Attention : Ce rôle donne un accès complet au système.',
        'gestionnaire': 'Ce rôle permet de gérer une coopérative spécifique.',
        'usva': 'Ce rôle donne accès aux fonctionnalités de l\'usine.'
    };

    roleSelect.addEventListener('change', function() {
        const selectedRole = this.value;
        const existingAlert = document.querySelector('.role-change-alert');
        
        if (existingAlert) {
            existingAlert.remove();
        }

        if (selectedRole !== currentRole) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-warning border role-change-alert mt-2';
            alert.innerHTML = `
                <small>
                    <i class="fas fa-exclamation-triangle me-1"></i> 
                    <strong>Changement de rôle détecté :</strong> ${roleWarnings[selectedRole] || 'Rôle modifié.'}
                </small>
            `;
            roleSelect.parentElement.appendChild(alert);
        }
    });
});
</script>
@endpush
@endsection