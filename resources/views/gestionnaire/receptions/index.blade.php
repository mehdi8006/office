@extends('gestionnaire.layouts.app')

@section('title', 'Réceptions de Lait - Aujourd\'hui')
@section('page-title')
    Réceptions de Lait - {{ today()->format('d/m/Y') }}
    <span class="badge bg-primary ms-2">{{ $receptions->total() }} réception(s)</span>
@endsection

@section('page-actions')
    <a href="{{ route('gestionnaire.receptions.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Nouvelle Réception
    </a>
@endsection

@section('content')
<!-- Receptions Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Réceptions d'Aujourd'hui
                <span class="badge bg-success ms-2">{{ $receptions->total() }} réception(s)</span>
            </h5>
            
            <div class="d-flex align-items-center">
                <span class="text-muted me-2">Coopérative :</span>
                <span class="badge bg-primary fs-6">{{ $cooperative->nom_cooperative }}</span>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        @if($receptions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Matricule</th>
                            <th>Membre Éleveur</th>
                            <th>Quantité</th>
                            <th>Heure</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receptions as $reception)
                            <tr>
                                <td>
                                    <code class="fs-6">{{ $reception->matricule_reception }}</code>
                                </td>
                                
                                <td>
                                    <div>
                                        <strong>{{ $reception->membre->nom_complet }}</strong>
                                        <br>
                                        <small class="text-muted">CIN: {{ $reception->membre->numero_carte_nationale }}</small>
                                    </div>
                                </td>
                                
                                <td>
                                    <span class="text-primary fw-bold">
                                        {{ number_format($reception->quantite_litres, 2) }} L
                                    </span>
                                </td>
                                
                                <td>
                                    <div>
                                        <strong>{{ $reception->created_at->format('H:i') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $reception->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- Edit Button -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal{{ $reception->id_reception }}"
                                                title="Modifier la quantité">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <!-- Delete Button -->
                                        <form action="{{ route('gestionnaire.receptions.destroy', $reception) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette réception ?\n\nMatricule: {{ $reception->matricule_reception }}\nMembre: {{ $reception->membre->nom_complet }}\nQuantité: {{ number_format($reception->quantite_litres, 2) }} L\n\nCette action mettra à jour automatiquement le stock.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Supprimer cette réception">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal for each reception -->
                            <div class="modal fade" id="editModal{{ $reception->id_reception }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-edit me-2"></i>Modifier la Quantité
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('gestionnaire.receptions.update', $reception) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted">Matricule</label>
                                                        <p class="fw-bold">{{ $reception->matricule_reception }}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted">Membre</label>
                                                        <p class="fw-bold">{{ $reception->membre->nom_complet }}</p>
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted">Quantité Actuelle</label>
                                                        <p class="text-primary fw-bold">{{ number_format($reception->quantite_litres, 2) }} L</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="quantite_litres{{ $reception->id_reception }}" class="form-label">
                                                            Nouvelle Quantité <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="number" 
                                                                   class="form-control" 
                                                                   id="quantite_litres{{ $reception->id_reception }}" 
                                                                   name="quantite_litres" 
                                                                   value="{{ $reception->quantite_litres }}"
                                                                   min="0.1"
                                                                   max="9999.99"
                                                                   step="0.01"
                                                                   required>
                                                            <span class="input-group-text">L</span>
                                                        </div>
                                                        <small class="text-muted">Ex: 25.50</small>
                                                    </div>
                                                </div>

                                                <div class="alert alert-info mt-3">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    <strong>Information :</strong> La modification de la quantité mettra automatiquement à jour le stock du jour.
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

            <!-- Pagination -->
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Affichage de {{ $receptions->firstItem() }} à {{ $receptions->lastItem() }} 
                        sur {{ $receptions->total() }} réceptions
                    </div>
                    <div>
                        {{ $receptions->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-clipboard text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Aucune réception aujourd'hui</h5>
                <p class="text-muted">Commencez par enregistrer la première réception de lait de la journée.</p>
                <a href="{{ route('gestionnaire.receptions.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Nouvelle Réception
                </a>
            </div>
        @endif
    </div>
</div>
@endsection