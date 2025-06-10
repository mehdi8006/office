@extends('direction.layouts.app')

@section('title', 'Modifier la Coopérative')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Modifier la Coopérative</h1>
                <div>
                    <a href="{{ route('direction.cooperatives.show', $cooperative) }}" class="btn btn-outline-info me-2">
                        <i class="fas fa-eye"></i> Voir les détails
                    </a>
                    <a href="{{ route('direction.cooperatives.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                </div>
            </div>

            <!-- Information sur la coopérative -->
            <div class="alert alert-info mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Matricule :</strong> {{ $cooperative->matricule }}
                    </div>
                    <div class="col-md-6">
                        <strong>Créée le :</strong> {{ $cooperative->created_at->format('d/m/Y à H:i') }}
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
                    <form method="POST" action="{{ route('direction.cooperatives.update', $cooperative) }}">
                        @csrf
                        @method('PUT')

                        <!-- Nom de la coopérative -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="nom_cooperative" class="form-label">
                                    <i class="fas fa-building text-primary me-1"></i>
                                    Nom de la Coopérative <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('nom_cooperative') is-invalid @enderror" 
                                       id="nom_cooperative" 
                                       name="nom_cooperative" 
                                       value="{{ old('nom_cooperative', $cooperative->nom_cooperative) }}" 
                                       placeholder="Ex: Coopérative Laitière de Casablanca"
                                       required>
                                @error('nom_cooperative')
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
                                       value="{{ old('email', $cooperative->email) }}" 
                                       placeholder="exemple@cooperative.ma"
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
                                       value="{{ old('telephone', $cooperative->telephone) }}" 
                                       placeholder="0522-123456"
                                       required>
                                @error('telephone')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Adresse -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="adresse" class="form-label">
                                    <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                    Adresse Complète <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                          id="adresse" 
                                          name="adresse" 
                                          rows="3" 
                                          placeholder="Adresse complète de la coopérative"
                                          required>{{ old('adresse', $cooperative->adresse) }}</textarea>
                                @error('adresse')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Responsable et Statut -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="responsable_id" class="form-label">
                                    <i class="fas fa-user-tie text-primary me-1"></i>
                                    Responsable (Gestionnaire)
                                </label>
                                <select class="form-select @error('responsable_id') is-invalid @enderror" 
                                        id="responsable_id" 
                                        name="responsable_id">
                                    <option value="">Aucun gestionnaire assigné</option>
                                    @foreach($gestionnaires as $gestionnaire)
                                        <option value="{{ $gestionnaire->id_utilisateur }}" 
                                                {{ old('responsable_id', $cooperative->responsable_id) == $gestionnaire->id_utilisateur ? 'selected' : '' }}>
                                            {{ $gestionnaire->nom_complet }} ({{ $gestionnaire->matricule }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('responsable_id')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Gestionnaires disponibles + responsable actuel s'il existe.
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
                                    <option value="actif" {{ old('statut', $cooperative->statut) == 'actif' ? 'selected' : '' }}>
                                        Actif
                                    </option>
                                    <option value="inactif" {{ old('statut', $cooperative->statut) == 'inactif' ? 'selected' : '' }}>
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

                        <!-- Information sur le responsable actuel -->
                        @if($cooperative->responsable)
                            <div class="alert alert-info">
                                <h6><i class="fas fa-user-tie me-2"></i>Responsable actuel :</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Nom :</strong> {{ $cooperative->responsable->nom_complet }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Email :</strong> {{ $cooperative->responsable->email }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Matricule :</strong> {{ $cooperative->responsable->matricule }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Téléphone :</strong> {{ $cooperative->responsable->telephone }}
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <form method="POST" 
                                          action="{{ route('direction.cooperatives.remove-responsable', $cooperative) }}" 
                                          style="display: inline-block;"
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir retirer ce responsable ?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-user-times me-1"></i>
                                            Retirer le responsable
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('direction.cooperatives.index') }}" class="btn btn-outline-secondary">
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
                        <div class="col-md-6">
                            @if($cooperative->statut == 'actif')
                                <form method="POST" 
                                      action="{{ route('direction.cooperatives.deactivate', $cooperative) }}" 
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver cette coopérative ?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="fas fa-pause me-1"></i>
                                        Désactiver la Coopérative
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('direction.cooperatives.activate', $cooperative) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-play me-1"></i>
                                        Activer la Coopérative
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('direction.cooperatives.show', $cooperative) }}" class="btn btn-info w-100">
                                <i class="fas fa-chart-bar me-1"></i>
                                Voir les Statistiques
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gestionnaires disponibles -->
            @if($gestionnaires->count() > 1 || ($gestionnaires->count() == 1 && !$cooperative->responsable))
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>
                            Gestionnaires Disponibles
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($gestionnaires->where('id_utilisateur', '!=', $cooperative->responsable_id) as $gestionnaire)
                                <div class="col-md-6 mb-2">
                                    <div class="border rounded p-2">
                                        <strong>{{ $gestionnaire->nom_complet }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $gestionnaire->matricule }} | {{ $gestionnaire->email }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection