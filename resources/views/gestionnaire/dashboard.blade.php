@extends('gestionnaire.layouts.app')

@section('title', 'Dashboard Gestionnaire')
@section('page-title', 'Tableau de Bord')

@section('content')
@if(!Auth::user()->cooperativeGeree)
    <!-- Alert si aucune coopérative assignée -->
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-3" style="font-size: 1.5rem;"></i>
            <div>
                <h6 class="alert-heading mb-1">Configuration Incomplète</h6>
                <p class="mb-0">
                    Aucune coopérative n'est assignée à votre compte. Contactez l'administrateur pour résoudre ce problème.
                    <br><small class="text-muted">Vous ne pourrez pas gérer les membres tant que ce problème n'est pas résolu.</small>
                </p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@else
    <!-- Dashboard normal si coopérative assignée -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-building me-2 text-primary"></i>
                        Ma Coopérative : {{ Auth::user()->cooperativeGeree->nom_cooperative }}
                    </h5>
                    <p class="card-text text-muted">
                        Vous êtes responsable de la gestion des membres éleveurs de cette coopérative.
                    </p>
                    <a href="{{ route('gestionnaire.membres.index') }}" class="btn btn-primary">
                        <i class="fas fa-users me-2"></i>Gérer les Membres
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    @php
        $cooperativeId = Auth::user()->getCooperativeId();
        $statsQuick = [
            'total' => \App\Models\MembreEleveur::where('id_cooperative', $cooperativeId)->count(),
            'actifs' => \App\Models\MembreEleveur::where('id_cooperative', $cooperativeId)->where('statut', 'actif')->count(),
            'inactifs' => \App\Models\MembreEleveur::where('id_cooperative', $cooperativeId)->where('statut', 'inactif')->count(),
        ];
    @endphp

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users text-primary mb-2" style="font-size: 2rem;"></i>
                    <h3 class="mb-1">{{ $statsQuick['total'] }}</h3>
                    <small class="text-muted">Total Membres</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user-check text-success mb-2" style="font-size: 2rem;"></i>
                    <h3 class="mb-1 text-success">{{ $statsQuick['actifs'] }}</h3>
                    <small class="text-muted">Membres Actifs</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user-times text-warning mb-2" style="font-size: 2rem;"></i>
                    <h3 class="mb-1 text-warning">{{ $statsQuick['inactifs'] }}</h3>
                    <small class="text-muted">Membres Inactifs</small>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection