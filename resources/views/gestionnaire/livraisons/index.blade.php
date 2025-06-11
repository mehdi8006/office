@extends('gestionnaire.layouts.app')

@section('title', 'Livraisons vers l\'Usine')
@section('page-title', 'Livraisons vers l\'Usine')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('gestionnaire.livraisons.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Nouvelle Livraison
        </a>
    </div>
@endsection

@section('content')
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

<!-- Section Historique des Livraisons -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-history me-2"></i>Historique des Livraisons Validées
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('gestionnaire.livraisons.download-livraisons-validees') }}" method="GET" id="historiqueForm">
            <!-- Champs cachés pour les dates -->
            <input type="hidden" name="date_debut" id="date_debut_hidden">
            <input type="hidden" name="date_fin" id="date_fin_hidden">
            
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="mois_historique" class="form-label">
                            <i class="fas fa-calendar-alt me-1"></i>Mois
                        </label>
                        <select class="form-select" id="mois_historique" required>
                            <option value="">Sélectionner un mois</option>
                            <option value="1" {{ now()->month == 1 ? 'selected' : '' }}>Janvier</option>
                            <option value="2" {{ now()->month == 2 ? 'selected' : '' }}>Février</option>
                            <option value="3" {{ now()->month == 3 ? 'selected' : '' }}>Mars</option>
                            <option value="4" {{ now()->month == 4 ? 'selected' : '' }}>Avril</option>
                            <option value="5" {{ now()->month == 5 ? 'selected' : '' }}>Mai</option>
                            <option value="6" {{ now()->month == 6 ? 'selected' : '' }}>Juin</option>
                            <option value="7" {{ now()->month == 7 ? 'selected' : '' }}>Juillet</option>
                            <option value="8" {{ now()->month == 8 ? 'selected' : '' }}>Août</option>
                            <option value="9" {{ now()->month == 9 ? 'selected' : '' }}>Septembre</option>
                            <option value="10" {{ now()->month == 10 ? 'selected' : '' }}>Octobre</option>
                            <option value="11" {{ now()->month == 11 ? 'selected' : '' }}>Novembre</option>
                            <option value="12" {{ now()->month == 12 ? 'selected' : '' }}>Décembre</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="annee_historique" class="form-label">
                            <i class="fas fa-calendar me-1"></i>Année
                        </label>
                        <select class="form-select" id="annee_historique" required>
                            <option value="">Sélectionner une année</option>
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="inclure_details_historique" 
                                   name="inclure_details" 
                                   value="1" 
                                   checked>
                            <label class="form-check-label" for="inclure_details_historique">
                                Inclure les détails
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <button type="submit" class="btn btn-info w-100">
                            <i class="fas fa-download me-2"></i>Télécharger PDF
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="mt-3">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Ce rapport contient toutes les livraisons validées pour le mois sélectionné.
            </small>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const moisSelect = document.getElementById('mois_historique');
    const anneeSelect = document.getElementById('annee_historique');
    const dateDebutInput = document.getElementById('date_debut_hidden');
    const dateFinInput = document.getElementById('date_fin_hidden');
    const form = document.getElementById('historiqueForm');
    
    // Fonction pour calculer les dates de début et fin du mois sélectionné
    function updateDatesFromMonthYear() {
        const mois = moisSelect.value;
        const annee = anneeSelect.value;
        
        if (mois && annee) {
            // Premier jour du mois
            const dateDebut = new Date(annee, mois - 1, 1);
            // Dernier jour du mois
            const dateFin = new Date(annee, mois, 0);
            
            // Mettre à jour les champs cachés
            dateDebutInput.value = dateDebut.toISOString().split('T')[0];
            dateFinInput.value = dateFin.toISOString().split('T')[0];
            
            console.log('Dates mises à jour:', {
                mois: mois,
                annee: annee,
                date_debut: dateDebutInput.value,
                date_fin: dateFinInput.value
            });
        } else {
            dateDebutInput.value = '';
            dateFinInput.value = '';
        }
    }
    
    // Mettre à jour les dates quand le mois ou l'année change
    moisSelect.addEventListener('change', updateDatesFromMonthYear);
    anneeSelect.addEventListener('change', updateDatesFromMonthYear);
    
    // Initialiser les dates au chargement de la page
    updateDatesFromMonthYear();
    
    // Validation du formulaire avant soumission
    form.addEventListener('submit', function(e) {
        const mois = moisSelect.value;
        const annee = anneeSelect.value;
        
        if (!mois || !annee) {
            e.preventDefault();
            alert('Veuillez sélectionner un mois et une année.');
            return false;
        }
        
        // Vérifier que les dates cachées sont bien remplies
        if (!dateDebutInput.value || !dateFinInput.value) {
            e.preventDefault();
            alert('Erreur: les dates ne sont pas correctement calculées. Veuillez réessayer.');
            return false;
        }
        
        console.log('Formulaire soumis avec:', {
            date_debut: dateDebutInput.value,
            date_fin: dateFinInput.value,
            inclure_details: document.getElementById('inclure_details_historique').checked
        });
        
        return true;
    });
});
</script>
@endpush