@extends('direction.layouts.app')

@section('title', 'Détails de la Coopérative')

@section('content')
<div class="container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">{{ $cooperative->nom_cooperative }}</h1>
        <div>
            <a href="{{ route('direction.cooperatives.edit', $cooperative) }}" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <a href="{{ route('direction.cooperatives.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </div>

    <!-- Messages d'alerte -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Informations générales -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building me-2"></i>
                        Informations Générales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Matricule :</strong>
                            <div class="mt-1">
                                <span class="badge bg-secondary fs-6">{{ $cooperative->matricule }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Statut :</strong>
                            <div class="mt-1">
                                @if($cooperative->statut == 'actif')
                                    <span class="badge bg-success fs-6">Actif</span>
                                @else
                                    <span class="badge bg-warning fs-6">Inactif</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong class="text-muted">Nom de la coopérative :</strong>
                            <div class="mt-1">{{ $cooperative->nom_cooperative }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Email :</strong>
                            <div class="mt-1">
                                <a href="mailto:{{ $cooperative->email }}" class="text-decoration-none">
                                    <i class="fas fa-envelope me-1"></i>
                                    {{ $cooperative->email }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Téléphone :</strong>
                            <div class="mt-1">
                                <a href="tel:{{ $cooperative->telephone }}" class="text-decoration-none">
                                    <i class="fas fa-phone me-1"></i>
                                    {{ $cooperative->telephone }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong class="text-muted">Adresse :</strong>
                            <div class="mt-1">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                {{ $cooperative->adresse }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Date de création :</strong>
                            <div class="mt-1">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $cooperative->created_at->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong class="text-muted">Dernière modification :</strong>
                            <div class="mt-1">
                                <i class="fas fa-clock me-1"></i>
                                {{ $cooperative->updated_at->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Responsable -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-tie me-2"></i>
                        Responsable de la Coopérative
                    </h5>
                    @if($cooperative->responsable)
                        <form method="POST" 
                              action="{{ route('direction.cooperatives.remove-responsable', $cooperative) }}" 
                              style="display: inline-block;"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir retirer ce responsable ?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-user-times me-1"></i>
                                Retirer
                            </button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    @if($cooperative->responsable)
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong class="text-muted">Nom complet :</strong>
                                <div class="mt-1">{{ $cooperative->responsable->nom_complet }}</div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong class="text-muted">Matricule :</strong>
                                <div class="mt-1">
                                    <span class="badge bg-info">{{ $cooperative->responsable->matricule }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong class="text-muted">Email :</strong>
                                <div class="mt-1">
                                    <a href="mailto:{{ $cooperative->responsable->email }}" class="text-decoration-none">
                                        <i class="fas fa-envelope me-1"></i>
                                        {{ $cooperative->responsable->email }}
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong class="text-muted">Téléphone :</strong>
                                <div class="mt-1">
                                    <a href="tel:{{ $cooperative->responsable->telephone }}" class="text-decoration-none">
                                        <i class="fas fa-phone me-1"></i>
                                        {{ $cooperative->responsable->telephone }}
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong class="text-muted">Rôle :</strong>
                                <div class="mt-1">
                                    <span class="badge bg-primary">{{ ucfirst($cooperative->responsable->role) }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong class="text-muted">Statut :</strong>
                                <div class="mt-1">
                                    @if($cooperative->responsable->statut == 'actif')
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-warning">Inactif</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-times fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Aucun responsable assigné</h6>
                            <p class="text-muted mb-3">Cette coopérative n'a pas encore de gestionnaire assigné.</p>
                            <a href="{{ route('direction.cooperatives.edit', $cooperative) }}" class="btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i>
                                Assigner un responsable
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistiques et actions -->
        <div class="col-md-4">
            <!-- Statistiques des membres -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>
                        Statistiques des Membres
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <div class="bg-primary text-white rounded p-3">
                                <h4 class="mb-0">{{ $cooperative->membres->count() }}</h4>
                                <small>Total Membres</small>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="bg-success text-white rounded p-2">
                                <h5 class="mb-0">{{ $cooperative->membresActifs->count() }}</h5>
                                <small>Actifs</small>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="bg-warning text-white rounded p-2">
                                <h5 class="mb-0">{{ $cooperative->membresInactifs->count() }}</h5>
                                <small>Inactifs</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bg-danger text-white rounded p-2">
                                <h5 class="mb-0">{{ $cooperative->membresSupprimes->count() }}</h5>
                                <small>Supprimés</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                        <a href="{{ route('direction.cooperatives.edit', $cooperative) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            Modifier les informations
                        </a>
                        
                        @if($cooperative->statut == 'actif')
                            <form method="POST" 
                                  action="{{ route('direction.cooperatives.deactivate', $cooperative) }}" 
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver cette coopérative ?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-pause me-1"></i>
                                    Désactiver
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('direction.cooperatives.activate', $cooperative) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-play me-1"></i>
                                    Activer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informations système -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations Système
                    </h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-2">
                            <strong>ID Système :</strong> {{ $cooperative->id_cooperative }}
                        </div>
                        <div class="mb-2">
                            <strong>Matricule :</strong> {{ $cooperative->matricule }}
                        </div>
                        <div class="mb-2">
                            <strong>Créée le :</strong> {{ $cooperative->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="mb-2">
                            <strong>Modifiée le :</strong> {{ $cooperative->updated_at->format('d/m/Y H:i') }}
                        </div>
                        @if($cooperative->responsable_id)
                            <div class="mb-2">
                                <strong>ID Responsable :</strong> {{ $cooperative->responsable_id }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des membres (aperçu) -->
    @if($cooperative->membres->count() > 0)
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>
                    Membres de la Coopérative ({{ $cooperative->membres->count() }})
                </h5>
                <span class="badge bg-info">Aperçu - 5 premiers</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nom Complet</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Statut</th>
                                <th>Inscrit le</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cooperative->membres->take(5) as $membre)
                                <tr>
                                    <td>
                                        <strong>{{ $membre->nom_complet }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $membre->numero_carte_nationale }}</small>
                                    </td>
                                    <td>{{ $membre->email }}</td>
                                    <td>{{ $membre->telephone }}</td>
                                    <td>
                                        @if($membre->statut == 'actif')
                                            <span class="badge bg-success">Actif</span>
                                        @elseif($membre->statut == 'inactif')
                                            <span class="badge bg-warning">Inactif</span>
                                        @else
                                            <span class="badge bg-danger">Supprimé</span>
                                        @endif
                                    </td>
                                    <td>{{ $membre->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($cooperative->membres->count() > 5)
                    <div class="card-footer text-center">
                        <small class="text-muted">
                            Et {{ $cooperative->membres->count() - 5 }} autres membres...
                        </small>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection