@extends('direction.layouts.app')

@section('title', 'Dashboard Direction')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Dashboard Direction</h1>
            <p class="text-muted">Accès rapide aux fonctions principales</p>
        </div>
        <div class="text-muted">
            <i class="fas fa-calendar-alt me-1"></i>
            {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row justify-content-center">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-primary h-100">
                <div class="card-body text-center d-flex flex-column">
                    <i class="fas fa-building fa-3x text-primary mb-3"></i>
                    <h4>Gestion des Coopératives</h4>
                    <p class="text-muted flex-grow-1">Créer, modifier et gérer les coopératives agricoles</p>
                    <a href="{{ route('direction.cooperatives.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-arrow-right me-2"></i>
                        Accéder
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-info h-100">
                <div class="card-body text-center d-flex flex-column">
                    <i class="fas fa-users fa-3x text-info mb-3"></i>
                    <h4>Gestion des Utilisateurs</h4>
                    <p class="text-muted flex-grow-1">Gérer tous les utilisateurs du système</p>
                    <a href="{{ route('direction.utilisateurs.index') }}" class="btn btn-info btn-lg">
                        <i class="fas fa-arrow-right me-2"></i>
                        Accéder
                    </a>
                </div>
            </div>
        </div>
       
    </div>
</div>
@endsection