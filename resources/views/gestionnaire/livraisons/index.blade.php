@extends('gestionnaire.layouts.app')

@section('title', 'Livraisons vers l\'Usine')
@section('page-title', 'Livraisons vers l\'Usine')

@section('page-actions')
    <div class="btn-group">
        <button type="button" 
                class="btn btn-outline-info" 
                data-bs-toggle="modal" 
                data-bs-target="#downloadModal">
            <i class="fas fa-download me-2"></i>Télécharger PDF
        </button>
        <a href="{{ route('gestionnaire.livraisons.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Nouvelle Livraison
        </a>
    </div>
@endsection

@section('content')
<!-- Summary Card for Validated Deliveries -->
@php
    $livraisonsValidees = \App\Models\LivraisonUsine::where('id_cooperative', $cooperative->id_cooperative)
        ->where('statut', 'validee')
        ->whereBetween('date_livraison', [now()->subDays(30), now()])
        ->get();
    $totalQuantiteValidee = $livraisonsValidees->sum('quantite_litres');
    $nombreLivraisonsValidees = $livraisonsValidees->count();
@endphp

@if($nombreLivraisonsValidees > 0)
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-1">{{ number_format($totalQuantiteValidee, 2) }} L</h4>
                        <p class="mb-0">Livraisons validées (30 derniers jours)</p>
                    </div>
                    <div>
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-1">{{ $nombreLivraisonsValidees }}</h4>
                        <p class="mb-0">Nombre de livraisons validées</p>
                    </div>
                    <div>
                        <i class="fas fa-truck fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Livraisons Planifiées Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Liste des Livraisons Planifiées
                <span class="badge bg-warning ms-2">{{ $livraisons->where('statut', 'planifiee')->count() }} livraison(s)</span>
            </h5>
        </div>
    </div>
    
    <div class="card-body p-0">
        @php
            $livraisonsPlanifiees = $livraisons->where('statut', 'planifiee');
        @endphp
        
        @if($livraisonsPlanifiees->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Quantité</th>
                            <th>Créée le</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($livraisonsPlanifiees as $livraison)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $livraison->date_livraison->format('d/m/Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $livraison->date_livraison->translatedFormat('l') }}</small>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="fw-bold text-primary">{{ $livraison->quantite_formattee }}</span>
                                </td>
                                
                                <td>
                                    <div>
                                        <small>{{ $livraison->created_at->format('d/m/Y H:i') }}</small>
                                        <br>
                                        <small class="text-muted">{{ $livraison->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- Modifier -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary" 
                                                title="Modifier la livraison"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal{{ $livraison->id_livraison }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Valider -->
                                        <form action="{{ route('gestionnaire.livraisons.validate', $livraison) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Valider cette livraison ?\n\nQuantité: {{ $livraison->quantite_formattee }}')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-success" 
                                                    title="Valider la livraison">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>

                                        <!-- Supprimer -->
                                        <form action="{{ route('gestionnaire.livraisons.destroy', $livraison) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Supprimer cette livraison ?\n\nQuantité: {{ $livraison->quantite_formattee }}\n\nLe stock sera automatiquement restauré.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Supprimer la livraison">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal de modification -->
                            <div class="modal fade" id="editModal{{ $livraison->id_livraison }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Modifier la Livraison</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('gestionnaire.livraisons.update', $livraison) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Date de Livraison</label>
                                                    <input type="date" 
                                                           class="form-control" 
                                                           name="date_livraison" 
                                                           value="{{ $livraison->date_livraison->format('Y-m-d') }}" 
                                                           required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Quantité (Litres)</label>
                                                    <input type="number" 
                                                           class="form-control" 
                                                           name="quantite_litres" 
                                                           value="{{ $livraison->quantite_litres }}" 
                                                           min="0.1" 
                                                           step="0.01" 
                                                           required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save me-2"></i>Enregistrer
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-truck text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Aucune livraison planifiée</h5>
                <p class="text-muted">Aucune livraison planifiée pour le moment.</p>
                <a href="{{ route('gestionnaire.livraisons.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Créer une livraison
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Modal de téléchargement PDF -->
<div class="modal fade" id="downloadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-download me-2"></i>Télécharger PDF des Livraisons Validées
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('gestionnaire.livraisons.download-livraisons-validees') }}" method="GET">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Sélectionnez la période pour télécharger le rapport des livraisons validées.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_debut" class="form-label">Date de début</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="date_debut"
                                       name="date_debut" 
                                       value="{{ now()->subDays(30)->format('Y-m-d') }}" 
                                       required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_fin" class="form-label">Date de fin</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="date_fin"
                                       name="date_fin" 
                                       value="{{ now()->format('Y-m-d') }}" 
                                       required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="inclure_details" 
                                   name="inclure_details" 
                                   value="1" 
                                   checked>
                            <label class="form-check-label" for="inclure_details">
                                Inclure les détails par livraison
                            </label>
                        </div>
                    </div>

                    <!-- Aperçu des données -->
                    <div class="bg-light p-3 rounded">
                        <h6 class="mb-2">Aperçu (30 derniers jours):</h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <strong>{{ $nombreLivraisonsValidees }}</strong>
                                <br>
                                <small class="text-muted">Livraisons</small>
                            </div>
                            <div class="col-6">
                                <strong>{{ number_format($totalQuantiteValidee, 2) }} L</strong>
                                <br>
                                <small class="text-muted">Total Quantité</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-file-pdf me-2"></i>Télécharger PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation des dates dans le modal
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');

    function validateDates() {
        if (dateDebut.value && dateFin.value) {
            if (dateDebut.value > dateFin.value) {
                dateFin.setCustomValidity('La date de fin doit être postérieure à la date de début');
            } else {
                dateFin.setCustomValidity('');
            }
        }
    }

    dateDebut.addEventListener('change', validateDates);
    dateFin.addEventListener('change', validateDates);

    // Pré-remplir avec des raccourcis
    document.querySelectorAll('[data-period]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const period = this.dataset.period;
            const today = new Date();
            
            switch(period) {
                case 'week':
                    dateDebut.value = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                    break;
                case 'month':
                    dateDebut.value = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                    break;
                case 'quarter':
                    const quarterStart = new Date(today.getFullYear(), Math.floor(today.getMonth() / 3) * 3, 1);
                    dateDebut.value = quarterStart.toISOString().split('T')[0];
                    break;
            }
            dateFin.value = today.toISOString().split('T')[0];
            validateDates();
        });
    });
});
</script>
@endpush