@extends('direction.layouts.app')

@section('title', 'Télécharger la Liste des Coopératives')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Télécharger la Liste des Coopératives</h1>
                <a href="{{ route('direction.cooperatives.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>

            <!-- Messages d'alerte -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Statistiques générales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $stats['total_cooperatives'] }}</h4>
                            <p class="mb-0">Total Coopératives</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $stats['cooperatives_actives'] }}</h4>
                            <p class="mb-0">Actives</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $stats['cooperatives_inactives'] }}</h4>
                            <p class="mb-0">Inactives</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $stats['sans_responsable'] }}</h4>
                            <p class="mb-0">Sans Responsable</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire de téléchargement -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-pdf me-2 text-danger"></i>
                        Générer le Rapport PDF
                    </h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('direction.cooperatives.download.pdf') }}">
                        @csrf

                        <!-- Filtres de base -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="statut" class="form-label">
                                    <i class="fas fa-toggle-on text-primary me-1"></i>
                                    Filtrer par Statut
                                </label>
                                <select class="form-select @error('statut') is-invalid @enderror" 
                                        id="statut" 
                                        name="statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="actif" {{ old('statut') == 'actif' ? 'selected' : '' }}>
                                        Actives uniquement
                                    </option>
                                    <option value="inactif" {{ old('statut') == 'inactif' ? 'selected' : '' }}>
                                        Inactives uniquement
                                    </option>
                                </select>
                                @error('statut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="responsable_filter" class="form-label">
                                    <i class="fas fa-user-tie text-primary me-1"></i>
                                    Filtrer par Responsable
                                </label>
                                <select class="form-select @error('responsable_filter') is-invalid @enderror" 
                                        id="responsable_filter" 
                                        name="responsable_filter">
                                    <option value="">Toutes les coopératives</option>
                                    <option value="avec" {{ old('responsable_filter') == 'avec' ? 'selected' : '' }}>
                                        Avec responsable assigné
                                    </option>
                                    <option value="sans" {{ old('responsable_filter') == 'sans' ? 'selected' : '' }}>
                                        Sans responsable assigné
                                    </option>
                                </select>
                                @error('responsable_filter')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Filtre par gestionnaire spécifique -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="responsable_id" class="form-label">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    Filtrer par Gestionnaire Spécifique (optionnel)
                                </label>
                                <select class="form-select @error('responsable_id') is-invalid @enderror" 
                                        id="responsable_id" 
                                        name="responsable_id">
                                    <option value="">Tous les gestionnaires</option>
                                    @foreach($gestionnaires as $gestionnaire)
                                        <option value="{{ $gestionnaire->id_utilisateur }}" 
                                                {{ old('responsable_id') == $gestionnaire->id_utilisateur ? 'selected' : '' }}>
                                            {{ $gestionnaire->nom_complet }} ({{ $gestionnaire->matricule }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('responsable_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Sélectionnez un gestionnaire pour voir uniquement les coopératives qu'il gère.
                                </div>
                            </div>
                        </div>

                        <!-- Filtre par période -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="date_debut" class="form-label">
                                    <i class="fas fa-calendar text-primary me-1"></i>
                                    Date de Création - Début
                                </label>
                                <input type="date" 
                                       class="form-control @error('date_debut') is-invalid @enderror" 
                                       id="date_debut" 
                                       name="date_debut" 
                                       value="{{ old('date_debut') }}">
                                @error('date_debut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="date_fin" class="form-label">
                                    <i class="fas fa-calendar text-primary me-1"></i>
                                    Date de Création - Fin
                                </label>
                                <input type="date" 
                                       class="form-control @error('date_fin') is-invalid @enderror" 
                                       id="date_fin" 
                                       name="date_fin" 
                                       value="{{ old('date_fin') }}">
                                @error('date_fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Options d'affichage -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">
                                    <i class="fas fa-cog text-primary me-1"></i>
                                    Options d'Affichage
                                </label>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="include_membres" 
                                           name="include_membres" 
                                           value="1"
                                           {{ old('include_membres') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="include_membres">
                                        <i class="fas fa-users me-1"></i>
                                        Inclure le nombre de membres actifs pour chaque coopérative
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Information sur le contenu -->
                        <div class="alert alert-info" role="alert">
                            <h6><i class="fas fa-info-circle me-2"></i>Contenu du Rapport PDF :</h6>
                            <ul class="mb-0">
                                <li>Informations complètes de chaque coopérative (matricule, nom, contact, adresse)</li>
                                <li>Responsable assigné (gestionnaire) avec ses coordonnées</li>
                                <li>Statut et date de création de la coopérative</li>
                                <li>Statistiques récapitulatives selon les filtres appliqués</li>
                                <li>Nombre de membres actifs (si option sélectionnée)</li>
                                <li>Date et utilisateur de génération du rapport</li>
                            </ul>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('direction.cooperatives.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-file-pdf me-1"></i>
                                Générer et Télécharger PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Gestionnaires disponibles -->
            @if($gestionnaires->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>
                            Gestionnaires Disponibles ({{ $gestionnaires->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($gestionnaires as $gestionnaire)
                                <div class="col-md-6 mb-2">
                                    <div class="border rounded p-2">
                                        <strong>{{ $gestionnaire->nom_complet }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $gestionnaire->matricule }} | {{ $gestionnaire->email }}
                                        </small>
                                        @if($gestionnaire->cooperativeGeree)
                                            <br>
                                            <small class="text-info">
                                                <i class="fas fa-building me-1"></i>
                                                Gère: {{ $gestionnaire->cooperativeGeree->nom_cooperative }}
                                            </small>
                                        @else
                                            <br>
                                            <small class="text-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Aucune coopérative assignée
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

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
                            <form method="POST" action="{{ route('direction.cooperatives.download.pdf') }}">
                                @csrf
                                <input type="hidden" name="statut" value="actif">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-download me-1"></i>
                                    Toutes les Actives
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form method="POST" action="{{ route('direction.cooperatives.download.pdf') }}">
                                @csrf
                                <input type="hidden" name="responsable_filter" value="sans">
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-download me-1"></i>
                                    Sans Responsable
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form method="POST" action="{{ route('direction.cooperatives.download.pdf') }}">
                                @csrf
                                <input type="hidden" name="include_membres" value="1">
                                <button type="submit" class="btn btn-info w-100">
                                    <i class="fas fa-download me-1"></i>
                                    Toutes avec Membres
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection