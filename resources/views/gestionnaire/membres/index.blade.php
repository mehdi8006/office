@extends('gestionnaire.layouts.app')

@section('title', 'Gestion des Membres Éleveurs')
@section('page-title', 'Membres Éleveurs')

@section('page-actions')
    <a href="{{ route('gestionnaire.membres.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Nouveau Membre
    </a>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Membres</h6>
                        <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-users text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Membres Actifs</h6>
                        <h3 class="mb-0 text-success">{{ number_format($stats['actifs']) }}</h3>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-user-check text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Membres Inactifs</h6>
                        <h3 class="mb-0 text-warning">{{ number_format($stats['inactifs']) }}</h3>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-user-times text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card stats-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Membres Supprimés</h6>
                        <h3 class="mb-0 text-danger">{{ number_format($stats['supprimes']) }}</h3>
                    </div>
                    <div class="ms-3">
                        <i class="fas fa-user-slash text-danger" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-filter me-2"></i>Filtres et Recherche
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('gestionnaire.membres.index') }}" class="row g-3">
            <!-- Search -->
            <div class="col-md-4">
                <label for="search" class="form-label">Recherche</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Nom ou CIN...">
                </div>
            </div>

            <!-- Status Filter -->
            <div class="col-md-3">
                <label for="statut" class="form-label">Statut</label>
                <select class="form-select" id="statut" name="statut">
                    <option value="tous" {{ request('statut') === 'tous' ? 'selected' : '' }}>Tous les statuts</option>
                    <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="inactif" {{ request('statut') === 'inactif' ? 'selected' : '' }}>Inactif</option>
                    <option value="suppression" {{ request('statut') === 'suppression' ? 'selected' : '' }}>Supprimé</option>
                </select>
            </div>

            <!-- Cooperative Filter -->
            <div class="col-md-3">
                <label for="cooperative_id" class="form-label">Coopérative</label>
                <select class="form-select" id="cooperative_id" name="cooperative_id">
                    <option value="">Toutes les coopératives</option>
                    @foreach($cooperatives as $cooperative)
                        <option value="{{ $cooperative->id_cooperative }}" 
                                {{ request('cooperative_id') == $cooperative->id_cooperative ? 'selected' : '' }}>
                            {{ $cooperative->nom_cooperative }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('gestionnaire.membres.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Members Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Liste des Membres
                <span class="badge bg-primary ms-2">{{ $membres->total() }} résultat(s)</span>
            </h5>
            
            <!-- Sort Options -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-sort me-1"></i>Trier par
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'nom_complet', 'sort_order' => 'asc']) }}">
                        <i class="fas fa-sort-alpha-up me-2"></i>Nom (A-Z)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'nom_complet', 'sort_order' => 'desc']) }}">
                        <i class="fas fa-sort-alpha-down me-2"></i>Nom (Z-A)
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => 'desc']) }}">
                        <i class="fas fa-calendar-alt me-2"></i>Plus récent
                    </a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => 'asc']) }}">
                        <i class="fas fa-calendar-alt me-2"></i>Plus ancien
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        @if($membres->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Membre</th>
                            <th>Coopérative</th>
                            <th>Contact</th>
                            <th>Statut</th>
                            <th>Date d'inscription</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($membres as $membre)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $membre->nom_complet }}</strong>
                                        <br>
                                        <small class="text-muted">CIN: {{ $membre->numero_carte_nationale }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $membre->cooperative->nom_cooperative }}</span>
                                </td>
                                <td>
                                    <div>
                                        <i class="fas fa-phone me-1"></i>{{ $membre->telephone }}
                                        <br>
                                        <i class="fas fa-envelope me-1"></i>{{ $membre->email }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $membre->statut_color }}">
                                        {{ $membre->statut_label }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $membre->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <div class="action-buttons d-flex flex-wrap gap-1">
                                        <!-- View -->
                                        <a href="{{ route('gestionnaire.membres.show', $membre) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if($membre->statut !== 'suppression')
                                            <!-- Edit -->
                                            <a href="{{ route('gestionnaire.membres.edit', $membre) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif

                                        @if($membre->statut === 'actif')
                                            <!-- Deactivate -->
                                            <form action="{{ route('gestionnaire.membres.deactivate', $membre) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver ce membre ?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-warning" 
                                                        title="Désactiver">
                                                    <i class="fas fa-user-times"></i>
                                                </button>
                                            </form>
                                        @elseif($membre->statut === 'inactif')
                                            <!-- Activate -->
                                            <form action="{{ route('gestionnaire.membres.activate', $membre) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir activer ce membre ?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-success" 
                                                        title="Activer">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            </form>
                                        @elseif($membre->statut === 'suppression')
                                            <!-- Restore -->
                                            <form action="{{ route('gestionnaire.membres.restore', $membre) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir restaurer ce membre ? Il sera automatiquement réactivé.')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-success" 
                                                        title="Restaurer">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($membre->statut !== 'suppression')
                                            <!-- Delete -->
                                            <button onclick="confirmDelete({{ $membre->id_membre }}, '{{ $membre->nom_complet }}')" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
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
                    <div class="text-muted">
                        Affichage de {{ $membres->firstItem() }} à {{ $membres->lastItem() }} 
                        sur {{ $membres->total() }} résultats
                    </div>
                    <div>
                        {{ $membres->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Aucun membre trouvé</h5>
                <p class="text-muted">Aucun membre ne correspond aux critères de recherche.</p>
                <a href="{{ route('gestionnaire.membres.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Ajouter le premier membre
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Confirmer la suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer le membre <strong id="memberName"></strong> ?</p>
                    <div class="mb-3">
                        <label for="deleteReason" class="form-label">Raison de la suppression <span class="text-danger">*</span></label>
                        <textarea class="form-control" 
                                  id="deleteReason" 
                                  name="raison_suppression" 
                                  rows="3" 
                                  placeholder="Veuillez préciser la raison de la suppression..." 
                                  required></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        Cette action marquera le membre comme supprimé. L'historique sera conservé.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Supprimer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Show delete confirmation modal
function confirmDelete(membreId, memberName) {
    document.getElementById('memberName').textContent = memberName;
    document.getElementById('deleteForm').action = `/gestionnaire/membres/${membreId}`;
    document.getElementById('deleteReason').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

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