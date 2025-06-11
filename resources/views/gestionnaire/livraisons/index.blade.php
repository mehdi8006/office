@extends('gestionnaire.layouts.app')

@section('title', 'Livraisons vers l\'Usine')
@section('page-title', 'Livraisons vers l\'Usine')

@section('page-actions')
    <a href="{{ route('gestionnaire.livraisons.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Nouvelle Livraison
    </a>
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
@endsection