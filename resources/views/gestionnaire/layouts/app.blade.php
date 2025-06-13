<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SGCCL - Gestionnaire')</title>
    
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

        /* Header */
        .main-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1040;
            height: 60px;
        }

        .main-header .navbar-brand {
            font-weight: 700;
            color: #28a745 !important;
            font-size: 1.5rem;
        }

        /* Sidebar Desktop */
        .sidebar-desktop {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            position: fixed;
            top: 60px;
            left: 0;
            width: 250px;
            height: calc(100vh - 60px);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1030;
            overflow-y: auto;
        }

        /* Sidebar Mobile (Offcanvas) */
        .sidebar-mobile {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            width: 280px;
        }

        /* Navigation Links */
        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.85);
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 2px 8px;
            padding: 12px 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .sidebar-nav .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.15);
            transform: translateX(3px);
        }

        .sidebar-nav .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.25);
            font-weight: 600;
        }

        .sidebar-nav .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 12px;
            font-size: 1rem;
        }

        /* Logo Section */
        .sidebar-logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar-logo h4 {
            color: white;
            font-weight: 700;
            margin: 0;
            font-size: 1.5rem;
        }

        .sidebar-logo small {
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
        }

        /* Main Content */
        .main-content {
            margin-top: 60px;
            margin-left: 0;
            padding: 20px;
            min-height: calc(100vh - 60px);
            transition: margin-left 0.3s ease;
        }

        /* Desktop Layout */
        @media (min-width: 992px) {
            .main-content {
                margin-left: 250px;
            }
            
            .sidebar-desktop {
                display: block !important;
            }
            
            .mobile-toggle-btn {
                display: none !important;
            }
        }

        /* Mobile Layout */
        @media (max-width: 991.98px) {
            .sidebar-desktop {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }

        /* Cards and Components */
        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-radius: 12px;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #1e7e34 0%, #17a2b8 100%);
            transform: translateY(-1px);
        }

        .stats-card {
            border-left: 4px solid #28a745;
            transition: transform 0.2s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
        }

        .action-buttons .btn {
            margin: 0 2px;
            padding: 0.375rem 0.75rem;
        }

        .toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1050;
        }

        /* Page Header */
        .page-header {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .page-header h1 {
            margin: 0;
            color: #2c3e50;
            font-weight: 600;
        }

        /* Logout Button */
        .logout-btn {
            color: rgba(255,255,255,0.85) !important;
            border: 1px solid rgba(255,255,255,0.2);
            background: transparent;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            color: white !important;
            background: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.5);
        }

        /* Scrollbar for sidebar */
        .sidebar-desktop::-webkit-scrollbar,
        .sidebar-mobile::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-desktop::-webkit-scrollbar-track,
        .sidebar-mobile::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }

        .sidebar-desktop::-webkit-scrollbar-thumb,
        .sidebar-mobile::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 3px;
        }

        .sidebar-desktop::-webkit-scrollbar-thumb:hover,
        .sidebar-mobile::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.5);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <nav class="navbar navbar-expand-lg navbar-light h-100">
            <div class="container-fluid">
                <!-- Mobile Toggle Button -->
                <button class="btn btn-outline-success mobile-toggle-btn d-lg-none me-3" 
                        type="button" 
                        data-bs-toggle="offcanvas" 
                        data-bs-target="#sidebarMobile">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Brand -->
                <a class="navbar-brand d-flex align-items-center" href="{{ route('gestionnaire.dashboard') }}">
                    
                    SGCCL
                </a>

                <!-- User Info -->
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" 
                           id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i>
                            <span class="d-none d-md-inline">{{ Auth::user()->nom_complet ?? 'Gestionnaire' }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">{{ Auth::user()->nom_complet ?? 'Gestionnaire' }}</h6></li>
                            <li><span class="dropdown-item-text small text-muted">{{ Auth::user()->email ?? '' }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Sidebar Desktop -->
    <nav class="sidebar-desktop d-none d-lg-block">
        <div class="sidebar-logo">
            <div class="d-flex align-items-center justify-content-center mb-2">
            <img src="{{ asset('image/bg.jpg') }}" alt="Logo" style="width: 100px; height: 85px; border-radius: 50%;">
                
            </div>
            <small>Gestionnaire</small>
        </div>

        <ul class="nav flex-column sidebar-nav">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('gestionnaire.dashboard') ? 'active' : '' }}" 
                   href="{{ route('gestionnaire.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i>
                    Tableau de bord
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('gestionnaire.membres.*') ? 'active' : '' }}" 
                   href="{{ route('gestionnaire.membres.index') }}">
                    <i class="fas fa-users"></i>
                    Membres Éleveurs
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('gestionnaire.receptions.*') ? 'active' : '' }}" 
                   href="{{ route('gestionnaire.receptions.index') }}">
                    <i class="fas fa-tint"></i>
                    Réceptions de Lait
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('gestionnaire.stock.*') ? 'active' : '' }}" 
                   href="{{ route('gestionnaire.stock.index') }}">
                    <i class="fas fa-warehouse"></i>
                    Gestion du Stock
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('gestionnaire.livraisons.*') ? 'active' : '' }}" 
                   href="{{ route('gestionnaire.livraisons.index') }}">
                    <i class="fas fa-truck"></i>
                    Livraisons Usine
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('gestionnaire.paiements.*') && !request()->routeIs('gestionnaire.paiements-eleveurs.*') ? 'active' : '' }}" 
                   href="{{ route('gestionnaire.paiements.index') }}">
                    <i class="fas fa-money-bill"></i>
                    Paiements Usine
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('gestionnaire.paiements-eleveurs.*') ? 'active' : '' }}" 
                   href="{{ route('gestionnaire.paiements-eleveurs.index') }}">
                    <i class="fas fa-hand-holding-usd"></i>
                    Paiements Éleveurs
                </a>
            </li>

            <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 16px;">
            
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}" class="px-3">
                    @csrf
                    <button type="submit" class="btn btn-outline-light logout-btn w-100 text-start">
                        <i class="fas fa-sign-out-alt me-3"></i>
                        Déconnexion
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Sidebar Mobile (Offcanvas) -->
    <div class="offcanvas offcanvas-start sidebar-mobile" tabindex="-1" id="sidebarMobile">
        <div class="offcanvas-header border-bottom" style="border-color: rgba(255,255,255,0.2) !important;">
            <div class="sidebar-logo w-100">
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <i class="fas fa-seedling text-white me-2" style="font-size: 1.8rem;"></i>
                    <h4>SGCCL</h4>
                </div>
                <small>Gestionnaire</small>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        
        <div class="offcanvas-body p-0">
            <ul class="nav flex-column sidebar-nav">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('gestionnaire.dashboard') ? 'active' : '' }}" 
                       href="{{ route('gestionnaire.dashboard') }}"
                       data-bs-dismiss="offcanvas">
                        <i class="fas fa-tachometer-alt"></i>
                        Tableau de bord
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('gestionnaire.membres.*') ? 'active' : '' }}" 
                       href="{{ route('gestionnaire.membres.index') }}"
                       data-bs-dismiss="offcanvas">
                        <i class="fas fa-users"></i>
                        Membres Éleveurs
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('gestionnaire.receptions.*') ? 'active' : '' }}" 
                       href="{{ route('gestionnaire.receptions.index') }}"
                       data-bs-dismiss="offcanvas">
                        <i class="fas fa-tint"></i>
                        Réceptions de Lait
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('gestionnaire.stock.*') ? 'active' : '' }}" 
                       href="{{ route('gestionnaire.stock.index') }}"
                       data-bs-dismiss="offcanvas">
                        <i class="fas fa-warehouse"></i>
                        Gestion du Stock
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('gestionnaire.livraisons.*') ? 'active' : '' }}" 
                       href="{{ route('gestionnaire.livraisons.index') }}"
                       data-bs-dismiss="offcanvas">
                        <i class="fas fa-truck"></i>
                        Livraisons Usine
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('gestionnaire.paiements.*') && !request()->routeIs('gestionnaire.paiements-eleveurs.*') ? 'active' : '' }}" 
                       href="{{ route('gestionnaire.paiements.index') }}"
                       data-bs-dismiss="offcanvas">
                        <i class="fas fa-money-bill"></i>
                        Paiements Usine
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('gestionnaire.paiements-eleveurs.*') ? 'active' : '' }}" 
                       href="{{ route('gestionnaire.paiements-eleveurs.index') }}"
                       data-bs-dismiss="offcanvas">
                        <i class="fas fa-hand-holding-usd"></i>
                        Paiements Éleveurs
                    </a>
                </li>

                <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 16px;">
                
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}" class="px-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-light logout-btn w-100 text-start">
                            <i class="fas fa-sign-out-alt me-3"></i>
                            Déconnexion
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                <h1>@yield('page-title', 'Dashboard')</h1>
                <div class="btn-toolbar">
                    <div class="btn-group me-2">
                        @yield('page-actions')
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Erreurs détectées :</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </main>

    <!-- Toast Container -->
    <div class="toast-container"></div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Base JavaScript -->
    <script>
        // CSRF Token setup for AJAX
        document.addEventListener('DOMContentLoaded', function() {
            const token = document.querySelector('meta[name="csrf-token"]');
            if (token) {
                window.axios = window.axios || {};
                window.axios.defaults = window.axios.defaults || {};
                window.axios.defaults.headers = window.axios.defaults.headers || {};
                window.axios.defaults.headers.common = window.axios.defaults.headers.common || {};
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
            }

            // Close mobile sidebar when clicking on nav links
            const sidebarLinks = document.querySelectorAll('#sidebarMobile .nav-link');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('sidebarMobile'));
                    if (offcanvas) {
                        offcanvas.hide();
                    }
                });
            });
        });

        // Show toast notification
        function showToast(message, type = 'success') {
            const toastContainer = document.querySelector('.toast-container');
            const toastId = 'toast-' + Date.now();
            
            const toastHtml = `
                <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header">
                        <i class="fas fa-${type === 'success' ? 'check-circle text-success' : 'exclamation-circle text-danger'} me-2"></i>
                        <strong class="me-auto">${type === 'success' ? 'Succès' : 'Erreur'}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
            
            // Remove toast element after it's hidden
            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
        }

        // Confirm action with custom message
        function confirmAction(message, callback) {
            if (confirm(message)) {
                callback();
            }
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>

    @stack('scripts')
</body>
</html>