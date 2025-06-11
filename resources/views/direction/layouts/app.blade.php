<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SGCCL - Direction')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        
        .navbar-brand {
            font-weight: 600;
        }
        
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
            transition: all 0.2s;
        }
        
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            font-size: 0.875rem;
            color: #495057;
            background-color: #f8f9fa;
        }
        
        .badge {
            font-weight: 500;
        }
        
        .btn {
            font-weight: 500;
        }
        
        .alert {
            border: none;
            border-radius: 0.5rem;
        }

        .nav-link {
            border-radius: 0.375rem;
            margin: 0 0.125rem;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .progress {
            border-radius: 0.5rem;
        }

        .list-group-item {
            border-left: none;
            border-right: none;
        }

        .list-group-item:first-child {
            border-top: none;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        /* Custom navbar styling */
        .navbar-nav .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('direction.dashboard') }}">
                <i class="fas fa-seedling me-2"></i>
                SGCCL <small class="opacity-75">Direction</small>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @auth
                        @if(auth()->user()->role === 'direction')
                            <!-- Dashboard -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('direction.dashboard') ? 'active' : '' }}" 
                                   href="{{ route('direction.dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-1"></i>
                                    Dashboard
                                </a>
                            </li>

                            <!-- Coopératives -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->routeIs('direction.cooperatives.*') ? 'active' : '' }}" 
                                   href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-building me-1"></i>
                                    Coopératives
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('direction.cooperatives.index') }}">
                                            <i class="fas fa-list me-2"></i>
                                            Toutes les coopératives
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('direction.cooperatives.create') }}">
                                            <i class="fas fa-plus me-2"></i>
                                            Nouvelle coopérative
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('direction.cooperatives.index') }}?statut=actif">
                                            <i class="fas fa-check-circle me-2 text-success"></i>
                                            Actives
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('direction.cooperatives.index') }}?statut=inactif">
                                            <i class="fas fa-pause-circle me-2 text-warning"></i>
                                            Inactives
                                        </a>
                                    </li>
                                     <li>
                                        <a class="dropdown-item" href="{{ route('direction.cooperatives.index') }}?responsable=">
                                            <i class="fas fa-user-times me-2 text-danger"></i>
                                            Sans responsable
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('direction.cooperatives.download') }}">
                                            <i class="fas fa-file-pdf me-2 text-danger"></i>
                                            Télécharger PDF
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <!-- Utilisateurs -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->routeIs('direction.utilisateurs.*') ? 'active' : '' }}" 
                                   href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-users me-1"></i>
                                    Utilisateurs
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('direction.utilisateurs.index') }}">
                                            <i class="fas fa-list me-2"></i>
                                            Tous les utilisateurs
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('direction.utilisateurs.create') }}">
                                            <i class="fas fa-user-plus me-2"></i>
                                            Nouvel utilisateur
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('direction.utilisateurs.index') }}?role=direction">
                                            <i class="fas fa-crown me-2 text-warning"></i>
                                            Direction
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('direction.utilisateurs.index') }}?role=gestionnaire">
                                            <i class="fas fa-user-tie me-2 text-primary"></i>
                                            Gestionnaires
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('direction.utilisateurs.index') }}?role=usva">
                                            <i class="fas fa-industry me-2 text-info"></i>
                                            USVA
                                        </a>
                                    </li>
                                    
                                </ul>
                            </li>
                        @endif
                        
                        @if(auth()->user()->role === 'gestionnaire')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('gestionnaire.dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-1"></i>
                                    Dashboard
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
                
                <!-- User menu -->
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                {{ auth()->user()->nom_complet }}
                                <span class="badge bg-secondary ms-1">{{ ucfirst(auth()->user()->role) }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <span class="dropdown-item-text">
                                        <div class="d-flex flex-column">
                                            <strong>{{ auth()->user()->nom_complet }}</strong>
                                            <small class="text-muted">{{ auth()->user()->email }}</small>
                                            <small class="text-muted">{{ auth()->user()->matricule }}</small>
                                        </div>
                                    </span>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                 <li>
                                    <a class="dropdown-item" href="{{ route('direction.utilisateurs.show', auth()->user()) }}">
                                        <i class="fas fa-user me-2"></i>
                                        Mon profil
                                    </a>
                                </li>
                               
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>
                                            Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>
                                Connexion
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    @hasSection('breadcrumb')
    <div class="bg-light border-bottom">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 py-2">
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <main class="py-4">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="container-fluid">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container-fluid">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="container-fluid">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="container-fluid">
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 mt-5 border-top">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 text-md-start">
                    <small>
                        © {{ date('Y') }} SGCCL - Système de Gestion des Coopératives Agricoles
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>
                        <i class="fas fa-user me-1"></i>
                        Connecté en tant que {{ auth()->user()->role ?? 'Invité' }}
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto-dismiss alerts -->
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Add active class to current page nav items
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
            
            navLinks.forEach(function(link) {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>

    @stack('scripts')
</body>
</html>