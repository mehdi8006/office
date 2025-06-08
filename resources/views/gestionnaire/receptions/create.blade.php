@extends('gestionnaire.layouts.app')

@section('title', 'Nouvelle Réception de Lait')
@section('page-title', 'Nouvelle Réception de Lait')

@section('page-actions')
    <a href="{{ route('gestionnaire.receptions.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour aux réceptions
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-plus-circle me-2"></i>Informations de la Réception
                </h5>
                <small class="text-muted">{{ $cooperative->nom_cooperative }} - {{ today()->format('d/m/Y') }}</small>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('gestionnaire.receptions.store') }}">
                    @csrf
                    
                    <!-- Member Selection -->
                    <div class="mb-3">
                        <label for="id_membre" class="form-label">
                            Membre Éleveur <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('id_membre') is-invalid @enderror" 
                                id="id_membre" 
                                name="id_membre" 
                                required>
                            <option value="">-- Sélectionnez un membre --</option>
                            @foreach($membresActifs as $membre)
                                <option value="{{ $membre->id_membre }}" 
                                        {{ old('id_membre') == $membre->id_membre ? 'selected' : '' }}>
                                    {{ $membre->nom_complet }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_membre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Quantity Input -->
                    <div class="mb-4">
                        <label for="quantite_litres" class="form-label">
                            Quantité (Litres) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control @error('quantite_litres') is-invalid @enderror" 
                                   id="quantite_litres" 
                                   name="quantite_litres" 
                                   value="{{ old('quantite_litres') }}"
                                   placeholder="0.00"
                                   min="0.1"
                                   max="9999.99"
                                   step="0.01"
                                   required>
                            <span class="input-group-text">L</span>
                        </div>
                        @error('quantite_litres')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Ex: 25.50</small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('gestionnaire.receptions.index') }}" class="btn btn-secondary">
                            Annuler
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection