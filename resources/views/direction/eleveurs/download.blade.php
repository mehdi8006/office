@extends('direction.layouts.app')

@section('title', 'Téléchargement Listes Éleveurs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('direction.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('direction.utilisateurs.index') }}">Utilisateurs</a></li>
    <li class="breadcrumb-item active">Téléchargement Éleveurs</li>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Téléchargement des Listes Éleveurs</h1>
                <a href="{{ route('direction.utilisateurs.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Retour aux Utilisateurs
                </a>
            </div>

            <!-- Statistiques des éleveurs -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $stats['total_eleveurs'] }}</h4>
                            <small>Total Éleveurs</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $stats['eleveurs_actifs'] }}</h4>
                            <small>Actifs</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $stats['eleveurs_inactifs'] }}</h4>
                            <small>Inactifs</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $stats['cooperatives_avec_eleveurs'] }}</h4>
                            <small>Coopératives avec Éleveurs</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire de téléchargement -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-download me-2"></i>
                        Options de Téléchargement
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('direction.eleveurs.download.process') }}">
                        @csrf

                        <div class="row">
                            <!-- Format de fichier -->
                            <div class="col-md-6 mb-3">
                                <label for="format" class="form-label">
                                    <i class="fas fa-file text-primary me-1"></i>
                                    Format de Fichier <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('format') is-invalid @enderror" 
                                        id="format" 
                                        name="format" 
                                        required>
                                    <option value="">Sélectionner un format</option>
                                    <option value="csv" {{ old('format') == 'csv' ? 'selected' : '' }}>
                                        CSV (Excel compatible)
                                    </option>
                                    <option value="excel" {{ old('format') == 'excel' ? 'selected' : '' }}>
                                        Excel (.xlsx)
                                    </option>
                                </select>
                                @error('format')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Coopérative -->
                            <div class="col-md-6 mb-3">
                                <label for="cooperative_id" class="form-label">
                                    <i class="fas fa-building text-primary me-1"></i>
                                    Coopérative (Optionnel)
                                </label>
                                <select class="form-select @error('cooperative_id') is-invalid @enderror" 
                                        id="cooperative_id" 
                                        name="cooperative_id">
                                    <option value="">Toutes les coopératives</option>
                                    @foreach($cooperatives as $cooperative)
                                        <option value="{{ $cooperative->id_cooperative }}" 
                                                {{ old('cooperative_id') == $cooperative->id_cooperative ? 'selected' : '' }}>
                                            {{ $cooperative->nom_cooperative }} 
                                            ({{ $cooperative->membresActifs->count() }} éleveurs)
                                        </option>
                                    @endforeach
                                </select>
                                @error('cooperative_id')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Statut -->
                            <div class="col-md-6 mb-3">
                                <label for="statut" class="form-label">
                                    <i class="fas fa-toggle-on text-primary me-1"></i>
                                    Statut (Optionnel)
                                </label>
                                <select class="form-select @error('statut') is-invalid @enderror" 
                                        id="statut" 
                                        name="statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="actif" {{ old('statut') == 'actif' ? 'selected' : '' }}>
                                        Actifs uniquement
                                    </option>
                                    <option value="inactif" {{ old('statut') == 'inactif' ? 'selected' : '' }}>
                                        Inactifs uniquement
                                    </option>
                                    <option value="suppression" {{ old('statut') == 'suppression' ? 'selected' : '' }}>
                                        Supprimés uniquement
                                    </option>
                                </select>
                                @error('statut')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Période d'inscription -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-calendar text-primary me-1"></i>
                                    Période d'Inscription (Optionnel)
                                </label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="date" 
                                               class="form-control @error('date_debut') is-invalid @enderror" 
                                               name="date_debut" 
                                               value="{{ old('date_debut') }}"
                                               placeholder="Date début">
                                        @error('date_debut')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <input type="date" 
                                               class="form-control @error('date_fin') is-invalid @enderror" 
                                               name="date_fin" 
                                               value="{{ old('date_fin') }}"
                                               placeholder="Date fin">
                                        @error('date_fin')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bouton de téléchargement -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('direction.utilisateurs.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-download me-2"></i>
                                Télécharger la Liste
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Informations sur le contenu du fichier -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Contenu du Fichier Exporté
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-list text-success me-1"></i> Données incluses :</h6>
                            <ul class="small">
                                <li>Matricule et nom de la coopérative</li>
                                <li>Nom complet de l'éleveur</li>
                                <li>Email et téléphone</li>
                                <li>Adresse complète</li>
                                <li>Numéro de carte nationale</li>
                                <li>Statut actuel</li>
                                <li>Date d'inscription</li>
                                <li>Raison de suppression (si applicable)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-file-excel text-info me-1"></i> Formats disponibles :</h6>
                            <ul class="small">
                                <li><strong>CSV :</strong> Compatible Excel, séparateur point-virgule</li>
                                <li><strong>Excel :</strong> Fichier .xlsx natif</li>
                                <li>Encodage UTF-8 avec BOM</li>
                                <li>Headers en français</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aperçu des coopératives -->
            @if($cooperatives->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-building me-2"></i>
                        Aperçu des Coopératives ({{ $cooperatives->count() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Coopérative</th>
                                    <th>Matricule</th>
                                    <th>Éleveurs Actifs</th>
                                    <th>Total Membres</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cooperatives->take(10) as $cooperative)
                                <tr>
                                    <td>
                                        <strong>{{ $cooperative->nom_cooperative }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $cooperative->matricule }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $cooperative->membresActifs->count() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $cooperative->membres->count() }}</span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('direction.eleveurs.download.process') }}" style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="format" value="csv">
                                            <input type="hidden" name="cooperative_id" value="{{ $cooperative->id_cooperative }}">
                                            <input type="hidden" name="statut" value="actif">
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Télécharger éleveurs actifs de cette coopérative">
                                                <i class="fas fa-download me-1"></i>
                                                CSV
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($cooperatives->count() > 10)
                    <div class="card-footer text-center">
                        <small class="text-muted">
                            Et {{ $cooperatives->count() - 10 }} autres coopératives...
                        </small>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-sélection format CSV par défaut
    const formatSelect = document.getElementById('format');
    if (!formatSelect.value) {
        formatSelect.value = 'csv';
    }

    // Validation des dates
    const dateDebut = document.querySelector('input[name="date_debut"]');
    const dateFin = document.querySelector('input[name="date_fin"]');

    function validateDates() {
        if (dateDebut.value && dateFin.value) {
            if (new Date(dateDebut.value) > new Date(dateFin.value)) {
                dateFin.setCustomValidity('La date de fin doit être postérieure à la date de début');
            } else {
                dateFin.setCustomValidity('');
            }
        }
    }

    dateDebut.addEventListener('change', validateDates);
    dateFin.addEventListener('change', validateDates);

    // Prévisualisation du nombre d'éleveurs selon les filtres
    const cooperativeSelect = document.getElementById('cooperative_id');
    const statutSelect = document.getElementById('statut');

    function updatePreview() {
        // Cette fonction pourrait être étendue pour faire un appel AJAX
        // et afficher une estimation du nombre d'éleveurs à exporter
    }

    cooperativeSelect.addEventListener('change', updatePreview);
    statutSelect.addEventListener('change', updatePreview);
});
</script>
@endpush
@endsection