@extends('gestionnaire.layouts.app')

@section('title', 'Détails du Membre - ' . $membre->nom_complet)
@section('page-title')
    Détails du Membre
    <span class="badge bg-{{ $membre->statut_color }} ms-2">{{ $membre->statut_label }}</span>
@endsection

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('gestionnaire.membres.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour à la liste
        </a>
        
        @if($membre->statut !== 'suppression')
            <a href="{{ route('gestionnaire.membres.edit', $membre) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Modifier
            </a>
        @endif
        
        @if($membre->statut === 'actif')
            <form action="{{ route('gestionnaire.membres.deactivate', $membre) }}" 
                  method="POST" 
                  style="display: inline;"
                  onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver ce membre ?')">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-user-times me-2"></i>Désactiver
                </button>
            </form>
        @elseif($membre->statut === 'inactif')
            <form action="{{ route('gestionnaire.membres.activate', $membre) }}" 
                  method="POST" 
                  style="display: inline;"
                  onsubmit="return confirm('Êtes-vous sûr de vouloir activer ce membre ?')">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-user-check me-2"></i>Activer
                </button>
            </form>
        @elseif($membre->statut === 'suppression')
            <form action="{{ route('gestionnaire.membres.restore', $membre) }}" 
                  method="POST" 
                  style="display: inline;"
                  onsubmit="return confirm('Êtes-vous sûr de vouloir restaurer ce membre ? Il sera automatiquement réactivé.')">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-undo me-2"></i>Restaurer
                </button>
            </form>
        @endif
    </div>
@endsection

@section('content')
<!-- Alert pour membre supprimé -->
@if($membre->statut === 'suppression')
    <div class="alert alert-danger d-flex align-items-center mb-4">
        <i class="fas fa-exclamation-triangle me-3" style="font-size: 1.5rem;"></i>
        <div>
            <h6 class="alert-heading mb-1">Membre Supprimé</h6>
            <p class="mb-0">Ce membre a été supprimé du système. Vous pouvez le restaurer en utilisant le bouton "Restaurer" ci-dessus.</p>
        </div>
    </div>
@endif

<div class="row">
    <!-- Left Column - Member Info -->
    <div class="col-lg-4 mb-4">
        <!-- Member Information Card -->
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user me-2"></i>Informations du Membre
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-user text-white" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="mt-3 mb-1">{{ $membre->nom_complet }}</h4>
                    <p class="text-muted">Membre Éleveur</p>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted">Numéro CIN</label>
                        <div class="fw-semibold">{{ $membre->numero_carte_nationale }}</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted">Email</label>
                        <div class="fw-semibold">
                            <i class="fas fa-envelope me-2 text-primary"></i>
                            {{ $membre->email }}
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted">Téléphone</label>
                        <div class="fw-semibold">
                            <i class="fas fa-phone me-2 text-success"></i>
                            {{ $membre->telephone }}
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted">Adresse</label>
                        <div class="fw-semibold">
                            <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                            {{ $membre->adresse }}
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted">Coopérative</label>
                        <div class="fw-semibold">
                            <span class="badge bg-info fs-6">{{ $membre->cooperative->nom_cooperative }}</span>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label text-muted">Date d'inscription</label>
                        <div class="fw-semibold">
                            <i class="fas fa-calendar me-2 text-info"></i>
                            {{ $membre->created_at->format('d/m/Y à H:i') }}
                        </div>
                    </div>

                    @if($membre->statut === 'suppression' && $membre->raison_suppression)
                        <div class="col-12">
                            <label class="form-label text-muted">Raison de suppression</label>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ $membre->raison_suppression }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Download Options -->
    <div class="col-lg-8">
        <!-- Historique des Réceptions Card -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>Historique des Réceptions
                    </h5>
                    <a href="{{ route('gestionnaire.membres.download-receptions', $membre) }}" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-download me-2"></i>Télécharger l'historique
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="text-center py-4">
                    <i class="fas fa-file-download text-primary mb-3" style="font-size: 3rem;"></i>
                    <h6 class="mb-2">Télécharger l'historique complet des réceptions</h6>
                    <p class="text-muted mb-0">
                        Obtenez un fichier PDF détaillé contenant toutes les réceptions de lait 
                        enregistrées pour {{ $membre->nom_complet }}.
                    </p>
                    <hr class="my-3">
                    <div class="row text-center">
                        <div class="col-4">
                            <small class="text-muted d-block">Format</small>
                            <strong>PDF</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Période</small>
                            <strong>Complète</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Tri</small>
                            <strong>Date desc.</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des Paiements Card -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>Historique des Paiements
                    </h5>
                    <a href="{{ route('gestionnaire.membres.download-paiements', $membre) }}" 
                       class="btn btn-outline-success">
                        <i class="fas fa-download me-2"></i>Télécharger l'historique
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="text-center py-4">
                    <i class="fas fa-file-invoice-dollar text-success mb-3" style="font-size: 3rem;"></i>
                    <h6 class="mb-2">Télécharger l'historique complet des paiements</h6>
                    <p class="text-muted mb-0">
                        Obtenez un fichier PDF détaillé contenant tous les paiements effectués 
                        à {{ $membre->nom_complet }} par périodes.
                    </p>
                    <hr class="my-3">
                    <div class="row text-center">
                        <div class="col-4">
                            <small class="text-muted d-block">Format</small>
                            <strong>PDF</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Période</small>
                            <strong>Complète</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Tri</small>
                            <strong>Date desc.</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Toast notification function
function showToast(message, type) {
    // Simple toast notification
    const toastColor = type === 'success' ? 'bg-success' : 'bg-danger';
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white border-0 ${toastColor}`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        document.body.removeChild(toast);
    });
}
</script>
@endpush