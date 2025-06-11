@extends('direction.layouts.app')

@section('title', 'Gestion des Coopératives')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
          
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Gestion des Coopératives</h1>
                <div>
                    <a href="{{ route('direction.cooperatives.download') }}" class="btn btn-outline-danger me-2">
                        <i class="fas fa-file-pdf"></i> Télécharger PDF
                    </a>
                    <a href="{{ route('direction.cooperatives.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Nouvelle Coopérative
                    </a>
                </div>
            </div>

           

            <!-- Filtres et Recherche -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('direction.cooperatives.index') }}">
                        <div class="row g-3">
                            <!-- Recherche -->
                            <div class="col-md-4">
                                <label for="search" class="form-label">Rechercher</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="search" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Nom, matricule ou email...">
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

                            <!-- Filtre par responsable -->
                            <div class="col-md-3">
                                <label for="responsable" class="form-label">Responsable</label>
                                <select class="form-select" id="responsable" name="responsable">
                                    <option value="">Tous les responsables</option>
                                    @foreach($gestionnaires as $gestionnaire)
                                        <option value="{{ $gestionnaire->id_utilisateur }}" 
                                                {{ request('responsable') == $gestionnaire->id_utilisateur ? 'selected' : '' }}>
                                            {{ $gestionnaire->nom_complet }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Boutons -->
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="btn-group w-100">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filtrer
                                    </button>
                                    <a href="{{ route('direction.cooperatives.index') }}" class="btn btn-outline-secondary">
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
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $cooperatives->total() }}</h4>
                                    <p class="mb-0">Total Coopératives</p>
                                </div>
                                <i class="fas fa-building fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $cooperatives->where('statut', 'actif')->count() }}</h4>
                                    <p class="mb-0">Actives</p>
                                </div>
                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $cooperatives->where('statut', 'inactif')->count() }}</h4>
                                    <p class="mb-0">Inactives</p>
                                </div>
                                <i class="fas fa-pause-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $cooperatives->whereNull('responsable_id')->count() }}</h4>
                                    <p class="mb-0">Sans Responsable</p>
                                </div>
                                <i class="fas fa-user-times fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau des coopératives -->
            <div class="card">
                <div class="card-body p-0">
                    @if($cooperatives->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Matricule</th>
                                        <th>Nom de la Coopérative</th>
                                        <th>Responsable</th>
                                        <th>Membres Actifs</th>
                                        <th>Statut</th>
                                        <th>Date de Création</th>
                                        <th width="200px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cooperatives as $cooperative)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">{{ $cooperative->matricule }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $cooperative->nom_cooperative }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $cooperative->email }}</small>
                                            </td>
                                            <td>
                                                @if($cooperative->responsable)
                                                    <div>
                                                        <strong>{{ $cooperative->responsable->nom_complet }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $cooperative->responsable->email }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-danger">
                                                        <i class="fas fa-exclamation-triangle"></i> Non assigné
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $cooperative->membresActifs->count() }} membres
                                                </span>
                                            </td>
                                            <td>
                                                @if($cooperative->statut == 'actif')
                                                    <span class="badge bg-success">Actif</span>
                                                @else
                                                    <span class="badge bg-warning">Inactif</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $cooperative->created_at->format('d/m/Y') }}
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <!-- Voir -->
                                                    <a href="{{ route('direction.cooperatives.show', $cooperative) }}" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       title="Voir les détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    <!-- Modifier -->
                                                    <a href="{{ route('direction.cooperatives.edit', $cooperative) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    <!-- Activer/Désactiver -->
                                                    @if($cooperative->statut == 'actif')
                                                        <form method="POST" 
                                                              action="{{ route('direction.cooperatives.deactivate', $cooperative) }}" 
                                                              style="display: inline-block;"
                                                              onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver cette coopérative ?')">
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
                                                              action="{{ route('direction.cooperatives.activate', $cooperative) }}" 
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
                                        Affichage de {{ $cooperatives->firstItem() }} à {{ $cooperatives->lastItem() }} 
                                        sur {{ $cooperatives->total() }} résultats
                                    </small>
                                </div>
                                <div>
                                    {{ $cooperatives->links() }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune coopérative trouvée</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['search', 'statut', 'responsable']))
                                    Aucun résultat ne correspond à vos critères de recherche.
                                @else
                                    Commencez par créer votre première coopérative.
                                @endif
                            </p>
                            @if(!request()->hasAny(['search', 'statut', 'responsable']))
                                <a href="{{ route('direction.cooperatives.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Créer une coopérative
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