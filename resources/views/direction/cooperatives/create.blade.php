@extends('direction.layouts.app')

@section('title', 'Créer une Coopérative')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Créer une Nouvelle Coopérative</h1>
                <a href="{{ route('direction.cooperatives.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>

            <!-- Formulaire -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building me-2"></i>
                        Informations de la Coopérative
                    </h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('direction.cooperatives.store') }}">
                        @csrf

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
                                       value="{{ old('nom_cooperative') }}" 
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
                                       value="{{ old('email') }}" 
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
                                       value="{{ old('telephone') }}" 
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
                                          required>{{ old('adresse') }}</textarea>
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
                                    <option value="">Sélectionner un gestionnaire</option>
                                    @foreach($gestionnaires as $gestionnaire)
                                        <option value="{{ $gestionnaire->id_utilisateur }}" 
                                                {{ old('responsable_id') == $gestionnaire->id_utilisateur ? 'selected' : '' }}>
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
                                    Seuls les gestionnaires sans coopérative assignée sont affichés.
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

                        <!-- Information supplémentaire -->
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Information :</strong> Le matricule de la coopérative sera généré automatiquement lors de la création.
                        </div>

                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('direction.cooperatives.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>
                                Créer la Coopérative
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Gestionnaires disponibles -->
            @if($gestionnaires->count() == 0)
                <div class="alert alert-warning mt-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention :</strong> Aucun gestionnaire disponible pour être assigné à cette coopérative. 
                    Tous les gestionnaires existants ont déjà une coopérative assignée ou sont inactifs.
                </div>
            @else
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