@extends('direction.layouts.app')

@section('title', 'Gestion des Utilisateurs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('direction.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Utilisateurs</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Gestion des Utilisateurs</h1>
                <a href="{{ route('direction.utilisateurs.create') }}" class="btn btn-success">
                    <i class="fas fa-user-plus"></i> Nouvel Utilisateur
                </a>
            </div>

            <!-- Filtres et Recherche -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('direction.utilisateurs.index') }}">
                        <div class="row g-3">
                            <!-- Recherche -->
                            <div class="col-md-4">
                                <label for="search" class="form-label">Rechercher</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="search" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Nom, email ou matricule...">
                            </div>

                            <!-- Filtre par rôle -->
                            <div class="col-md-3">
                                <label for="role" class="form-label">Rôle</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="">Tous les rôles</option>
                                    <option value="direction" {{ request('role') == 'direction' ? 'selected' : '' }}>Direction</option>
                                    <option value="gestionnaire" {{ request('role') == 'gestionnaire' ? 'selected' : '' }}>Gestionnaire</option>
                                    <option value="usva" {{ request('role') == 'usva' ? 'selected' : '' }}>USVA</option>
                                </select>
                            </div>

                            <!-- Filtre par statut -->
                            <div class="col-md-3">
                                <label for="statut" class="form-label">Statut</label>
                                <select class="form-select" id="statut" name="statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                                    <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                                </select>
                            </div>

                            <!-- Boutons -->
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="btn-group w-100">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filtrer
                                    </button>
                                    <a href="{{ route('direction.utilisateurs.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistiques rapides -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $stats['total'] }}</h4>
                            <small>Total Utilisateurs</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $stats['direction'] }}</h4>
                            <small>Direction</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $stats['gestionnaires'] }}</h4>
                            <small>Gestionnaires</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $stats['usva'] }}</h4>
                            <small>USVA</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau des utilisateurs -->
            <div class="card">
                <div class="card-body p-0">
                    @if($utilisateurs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Matricule</th>
                                        <th>Utilisateur</th>
                                        <th>Contact</th>
                                        <th>Rôle</th>
                                        <th>Statut</th>
                                        <th>Assignation</th>
                                        <th>Créé le</th>
                                        <th width="200px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($utilisateurs as $utilisateur)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">{{ $utilisateur->matricule }}</span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $utilisateur->nom_complet }}</strong>
                                                    @if($utilisateur->id_utilisateur === auth()->id())
                                                        <span class="badge bg-warning text-dark ms-1">Vous</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-envelope me-1"></i>
                                                        {{ $utilisateur->email }}
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-phone me-1"></i>
                                                        {{ $utilisateur->telephone }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $roleColors = [
                                                        'direction' => 'danger',
                                                        'gestionnaire' => 'primary',
                                                        'usva' => 'info'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $roleColors[$utilisateur->role] ?? 'secondary' }}">
                                                    {{ ucfirst($utilisateur->role) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($utilisateur->statut == 'actif')
                                                    <span class="badge bg-success">Actif</span>
                                                @else
                                                    <span class="badge bg-warning">Inactif</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($utilisateur->role === 'gestionnaire')
                                                    @if($utilisateur->cooperativeGeree)
                                                        <div>
                                                            <i class="fas fa-building text-success me-1"></i>
                                                            <small class="text-success">
                                                                {{ Str::limit($utilisateur->cooperativeGeree->nom_cooperative, 20) }}
                                                            </small>
                                                        </div>
                                                    @else
                                                        <small class="text-muted">
                                                            <i class="fas fa-minus"></i> Non assigné
                                                        </small>
                                                    @endif
                                                @else
                                                    <small class="text-muted">-</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $utilisateur->created_at->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <!-- Voir -->
                                                    <a href="{{ route('direction.utilisateurs.show', $utilisateur) }}" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       title="Voir les détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    <!-- Modifier -->
                                                    @if($utilisateur->id_utilisateur !== auth()->id())
                                                        <a href="{{ route('direction.utilisateurs.edit', $utilisateur) }}" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif

                                                    <!-- Actions selon le statut -->
                                                    @if($utilisateur->id_utilisateur !== auth()->id())
                                                        @if($utilisateur->statut == 'actif')
                                                            <form method="POST" 
                                                                  action="{{ route('direction.utilisateurs.deactivate', $utilisateur) }}" 
                                                                  style="display: inline-block;"
                                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver cet utilisateur ?')">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" 
                                                                        class="btn btn-sm btn-outline-warning" 
                                                                        title="Désactiver">
                                                                    <i class="fas fa-pause"></i>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <form method="POST" 
                                                                  action="{{ route('direction.utilisateurs.activate', $utilisateur) }}" 
                                                                  style="display: inline-block;">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" 
                                                                        class="btn btn-sm btn-outline-success" 
                                                                        title="Activer">
                                                                    <i class="fas fa-play"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif

                                                    <!-- Menu actions -->
                                                    <div class="btn-group" role="group">
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                                data-bs-toggle="dropdown" 
                                                                title="Plus d'actions">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a class="dropdown-item" 
                                                                   href="{{ route('direction.utilisateurs.show', $utilisateur) }}">
                                                                    <i class="fas fa-eye me-2"></i>
                                                                    Voir détails
                                                                </a>
                                                            </li>
                                                            @if($utilisateur->id_utilisateur !== auth()->id())
                                                                <li>
                                                                    <a class="dropdown-item" 
                                                                       href="{{ route('direction.utilisateurs.reset-password', $utilisateur) }}">
                                                                        <i class="fas fa-key me-2"></i>
                                                                        Réinitialiser mot de passe
                                                                    </a>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <form method="POST" 
                                                                          action="{{ route('direction.utilisateurs.destroy', $utilisateur) }}" 
                                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" 
                                                                                class="dropdown-item text-danger">
                                                                            <i class="fas fa-trash me-2"></i>
                                                                            Supprimer
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">
                                        Affichage de {{ $utilisateurs->firstItem() }} à {{ $utilisateurs->lastItem() }} 
                                        sur {{ $utilisateurs->total() }} résultats
                                    </small>
                                </div>
                                <div>
                                    {{ $utilisateurs->links() }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun utilisateur trouvé</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['search', 'role', 'statut']))
                                    Aucun résultat ne correspond à vos critères de recherche.
                                @else
                                    Commencez par créer votre premier utilisateur.
                                @endif
                            </p>
                            @if(!request()->hasAny(['search', 'role', 'statut']))
                                <a href="{{ route('direction.utilisateurs.create') }}" class="btn btn-success">
                                    <i class="fas fa-user-plus"></i> Créer un utilisateur
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection