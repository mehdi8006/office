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

    <!-- Right Column - Statistics & History -->
    <div class="col-lg-8">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card text-center stats-card h-100">
                    <div class="card-body">
                        <i class="fas fa-clipboard-list text-primary mb-2" style="font-size: 2rem;"></i>
                        <h4 class="mb-1">{{ number_format($stats['total_receptions']) }}</h4>
                        <small class="text-muted">Total Réceptions</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center stats-card h-100">
                    <div class="card-body">
                        <i class="fas fa-tint text-info mb-2" style="font-size: 2rem;"></i>
                        <h4 class="mb-1">{{ number_format($stats['total_quantite'], 1) }}L</h4>
                        <small class="text-muted">Total Livré</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center stats-card h-100">
                    <div class="card-body">
                        <i class="fas fa-chart-line text-success mb-2" style="font-size: 2rem;"></i>
                        <h4 class="mb-1">{{ number_format($stats['moyenne_mensuelle'] ?? 0, 1) }}L</h4>
                        <small class="text-muted">Moyenne/Réception</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card text-center stats-card h-100">
                    <div class="card-body">
                        <i class="fas fa-calendar-check text-warning mb-2" style="font-size: 2rem;"></i>
                        <h4 class="mb-1">
                            @if($stats['derniere_reception'])
                                {{ $stats['derniere_reception']->diffForHumans() }}
                            @else
                                N/A
                            @endif
                        </h4>
                        <small class="text-muted">Dernière Réception</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Chart -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Évolution des Livraisons (12 derniers mois)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="100"></canvas>
            </div>
        </div>

        <!-- Reception History -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Historique des Réceptions
                    <span class="badge bg-primary ms-2">{{ $receptions->total() }} réception(s)</span>
                </h5>
            </div>
            <div class="card-body p-0">
                @if($receptions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Matricule</th>
                                    <th>Quantité</th>
                                    <th>Coopérative</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($receptions as $reception)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $reception->date_reception->format('d/m/Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $reception->date_reception->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <code>{{ $reception->matricule_reception }}</code>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                {{ number_format($reception->quantite_litres, 2) }} L
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $reception->cooperative->nom_cooperative }}</small>
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
                                Affichage de {{ $receptions->firstItem() }} à {{ $receptions->lastItem() }} 
                                sur {{ $receptions->total() }} réceptions
                            </div>
                            <div>
                                {{ $receptions->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">Aucune réception enregistrée</h5>
                        <p class="text-muted">Ce membre n'a encore effectué aucune livraison de lait.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Monthly Chart
const monthlyData = @json($monthlyData);
const ctx = document.getElementById('monthlyChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: monthlyData.map(item => item.label),
        datasets: [{
            label: 'Quantité (Litres)',
            data: monthlyData.map(item => item.quantite),
            backgroundColor: 'rgba(40, 167, 69, 0.8)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 1,
            borderRadius: 4,
        }, {
            label: 'Nombre de réceptions',
            data: monthlyData.map(item => item.receptions),
            type: 'line',
            borderColor: 'rgba(255, 193, 7, 1)',
            backgroundColor: 'rgba(255, 193, 7, 0.2)',
            yAxisID: 'y1',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Quantité (Litres)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Nombre de réceptions'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        },
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        if (context.datasetIndex === 0) {
                            return `Quantité: ${context.parsed.y.toFixed(2)} L`;
                        } else {
                            return `Réceptions: ${context.parsed.y}`;
                        }
                    }
                }
            }
        }
    }
});

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