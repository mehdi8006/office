@extends('direction.layouts.app')

@section('title', 'Détails de l\'Utilisateur')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('direction.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('direction.utilisateurs.index') }}">Utilisateurs</a></li>
    <li class="breadcrumb-item active">{{ $utilisateur->nom_complet }}</li>
@endsection

@section('content')
<div class="container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">{{ $utilisateur->nom_complet }}</h1>
        <div>
            @if($utilisateur->id_utilisateur !== auth()->id())
                <a href="{{ route('direction.utilisateurs.edit', $utilisateur) }}" class="btn btn-primary me-2">
                    <i class="fas fa-edit"></i> Modifier
                </a>
            @endif
            <a href="{{ route('direction.utilisateurs.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informations générales -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>
                        Informations Générales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Matricule :</strong>
                            <div class="mt-1">
                                <span class="badge bg-secondary fs-6">{{ $utilisateur->matricule }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Statut :</strong>
                            <div class="mt-1">
                                @if($utilisateur->statut == 'actif')
                                    <span class="badge bg-success fs-6">Actif</span>
                                @else
                                    <span class="badge bg-warning fs-6">Inactif</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong class="text-muted">Nom complet :</strong>
                            <div class="mt-1">{{ $utilisateur->nom_complet }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Email :</strong>
                            <div class="mt-1">
                                <a href="mailto:{{ $utilisateur->email }}" class="text-decoration-none">
                                    <i class="fas fa-envelope me-1"></i>
                                    {{ $utilisateur->email }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Téléphone :</strong>
                            <div class="mt-1">
                                <a href="tel:{{ $utilisateur->telephone }}" class="text-decoration-none">
                                    <i class="fas fa-phone me-1"></i>
                                    {{ $utilisateur->telephone }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Rôle :</strong>
                            <div class="mt-1">
                                @php
                                    $roleColors = [
                                        'direction' => 'danger',
                                        'gestionnaire' => 'primary',
                                        'usva' => 'info'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $roleColors[$utilisateur->role] ?? 'secondary' }} fs-6">
                                    {{ ucfirst($utilisateur->role) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Date de création :</strong>
                            <div class="mt-1">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $utilisateur->created_at->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Dernière modification :</strong>
                            <div class="mt-1">
                                <i class="fas fa-clock me-1"></i>
                                {{ $utilisateur->updated_at->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations spécifiques selon le rôle -->
            @if($utilisateur->role === 'gestionnaire')
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-building me-2"></i>
                            Informations Gestionnaire
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($utilisateur->cooperativeGeree)
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <strong class="text-muted">Coopérative gérée :</strong>
                                    <div class="mt-2">
                                        <div class="card border-success">
                                            <div class="card-body">
                                                <h6 class="card-title">{{ $utilisateur->cooperativeGeree->nom_cooperative }}</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <small class="text-muted">Matricule :</small>
                                                        <span class="badge bg-info">{{ $utilisateur->cooperativeGeree->matricule }}</span>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted">Statut :</small>
                                                        <span class="badge bg-{{ $utilisateur->cooperativeGeree->statut == 'actif' ? 'success' : 'warning' }}">
                                                            {{ ucfirst($utilisateur->cooperativeGeree->statut) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @if(isset($stats['membres_total']))
                                                    <div class="row mt-2">
                                                        <div class="col-md-6">
                                                            <small class="text-muted">Total membres :</small>
                                                            <span class="badge bg-primary">{{ $stats['membres_total'] }}</span>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <small class="text-muted">Membres actifs :</small>
                                                            <span class="badge bg-success">{{ $stats['membres_actifs'] }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="mt-3">
                                                    <a href="{{ route('direction.cooperatives.show', $utilisateur->cooperativeGeree) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i>
                                                        Voir la coopérative
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">Aucune coopérative assignée</h6>
                                <p class="text-muted mb-3">Ce gestionnaire n'a pas encore de coopérative à gérer.</p>
                                <a href="{{ route('direction.cooperatives.index') }}?responsable=" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>
                                    Voir les coopératives sans responsable
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Autres rôles -->
            @if($utilisateur->role === 'usva')
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-industry me-2"></i>
                            Informations USVA
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Cet utilisateur a accès aux fonctionnalités de gestion de l'usine et des livraisons.
                        </div>
                    </div>
                </div>
            @endif

            @if($utilisateur->role === 'direction')
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-crown me-2"></i>
                            Informations Direction
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-shield-alt me-2"></i>
                            <strong>Privilèges administrateur :</strong> Cet utilisateur a un accès complet au système.
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Actions et statistiques -->
        <div class="col-md-4">
            <!-- Actions rapides -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Actions Rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($utilisateur->id_utilisateur !== auth()->id())
                            <a href="{{ route('direction.utilisateurs.edit', $utilisateur) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>
                                Modifier les informations
                            </a>
                            
                            <a href="{{ route('direction.utilisateurs.reset-password', $utilisateur) }}" class="btn btn-warning">
                                <i class="fas fa-key me-1"></i>
                                Réinitialiser le mot de passe
                            </a>
                            
                            @if($utilisateur->statut == 'actif')
                                <form method="POST" 
                                      action="{{ route('direction.utilisateurs.deactivate', $utilisateur) }}" 
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver cet utilisateur ?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-warning">
                                        <i class="fas fa-pause me-1"></i>
                                        Désactiver
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('direction.utilisateurs.activate', $utilisateur) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-success">
                                        <i class="fas fa-play me-1"></i>
                                        Activer
                                    </button>
                                </form>
                            @endif
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <small>Vous consultez votre propre profil. Les modifications sont limitées.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informations système -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Informations Système
                    </h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-2">
                            <strong>ID Système :</strong> {{ $utilisateur->id_utilisateur }}
                        </div>
                        <div class="mb-2">
                            <strong>Matricule :</strong> {{ $utilisateur->matricule }}
                        </div>
                        <div class="mb-2">
                            <strong>Créé le :</strong> {{ $utilisateur->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="mb-2">
                            <strong>Modifié le :</strong> {{ $utilisateur->updated_at->format('d/m/Y H:i') }}
                        </div>
                        @if($utilisateur->updated_at != $utilisateur->created_at)
                            <div class="mb-2">
                                <strong>Dernière modification :</strong> 
                                {{ $utilisateur->updated_at->diffForHumans() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sécurité -->
            @if($utilisateur->id_utilisateur !== auth()->id())
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Zone de Danger
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Actions irréversibles. Procédez avec précaution.
                    </p>
                    
                    @if($utilisateur->role === 'gestionnaire' && $utilisateur->cooperativeGeree)
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Cet utilisateur gère une coopérative. Retirez-le d'abord de sa coopérative avant suppression.
                        </div>
                    @else
                        <form method="POST" 
                              action="{{ route('direction.utilisateurs.destroy', $utilisateur) }}" 
                              onsubmit="return confirm('ATTENTION: Cette action est irréversible. Êtes-vous absolument sûr de vouloir supprimer cet utilisateur ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="fas fa-trash me-1"></i>
                                Supprimer définitivement
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection