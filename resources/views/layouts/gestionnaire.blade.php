<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'إدارة التعاونية') - نظام إدارة التعاونيات</title>
    
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
            --dark-color: #212529;
            --light-color: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
            transform: translateX(-5px);
        }

        .sidebar .nav-link i {
            margin-left: 10px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 25px rgba(0,0,0,0.12);
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            border: none;
            padding: 20px;
        }

        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .table {
            border-radius: 8px;
            overflow: hidden;
        }

        .table thead th {
            background-color: #f8f9fa;
            border: none;
            font-weight: 600;
            color: #495057;
        }

        .badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .stats-card .stats-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }

        .stats-card .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
        }

        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: "‹";
            margin: 0 10px;
        }

        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            border-bottom: 1px solid #eee;
            border-radius: 15px 15px 0 0;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .navbar {
            background: white !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-bottom: 1px solid #eee;
        }

        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color) !important;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            .main-content {
                padding: 10px;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-4">
                    <!-- Logo -->
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="fas fa-cow"></i>
                            نظام التعاونيات
                        </h4>
                        <small class="text-white-50">لوحة المدير</small>
                    </div>

                    <!-- User Info -->
                    <div class="text-center mb-4 pb-3 border-bottom border-white-50">
                        <div class="text-white">
                            <i class="fas fa-user-circle fa-2x mb-2"></i>
                            <div>{{ Auth::user()->nom_complet }}</div>
                            <small class="text-white-50">{{ Auth::user()->role }}</small>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('gestionnaire.membres.*') ? 'active' : '' }}" 
                               href="{{ route('gestionnaire.membres.index') }}">
                                <i class="fas fa-users"></i>
                                إدارة المربين
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('gestionnaire.receptions.*') ? 'active' : '' }}" 
                               href="{{ route('gestionnaire.receptions.index') }}">
                                <i class="fas fa-clipboard-list"></i>
                                إدارة الاستلام
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('gestionnaire.stock.*') ? 'active' : '' }}" 
                               href="{{ route('gestionnaire.stock.index') }}">
                                <i class="fas fa-warehouse"></i>
                                إدارة المخزون
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link" href="{{ route('gestionnaire.receptions.create') }}">
                                <i class="fas fa-plus-circle"></i>
                                استلام جديد
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('gestionnaire.membres.create') }}">
                                <i class="fas fa-user-plus"></i>
                                إضافة مربي
                            </a>
                        </li>
                    </ul>

                    <!-- Logout -->
                    <div class="mt-auto pt-4">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link text-white-50 w-100 text-start">
                                <i class="fas fa-sign-out-alt"></i>
                                تسجيل الخروج
                            </button>
                        </form>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                    <div class="container-fluid">
                        <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target=".sidebar">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        
                        <!-- Breadcrumb -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                @yield('breadcrumb')
                            </ol>
                        </nav>

                        <!-- Quick Actions -->
                        <div class="d-flex">
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" 
                                        data-bs-toggle="dropdown">
                                    <i class="fas fa-plus"></i>
                                    إجراءات سريعة
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('gestionnaire.receptions.create') }}">
                                        <i class="fas fa-clipboard-list"></i> استلام جديد
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('gestionnaire.membres.create') }}">
                                        <i class="fas fa-user-plus"></i> إضافة مربي
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('gestionnaire.stock.create-livraison') }}">
                                        <i class="fas fa-truck"></i> تسليم جديد
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>

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

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>هناك أخطاء في النموذج:</strong>
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
        </div>
    </div>

    <!-- Loading Spinner -->
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">جاري التحميل...</span>
        </div>
        <p class="mt-2">جاري التحميل...</p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Common JavaScript -->
    <script>
        // CSRF Token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Loading spinner functions
        function showLoading() {
            $('.loading-spinner').show();
        }

        function hideLoading() {
            $('.loading-spinner').hide();
        }

        // Confirmation dialog
        function confirmAction(message, callback) {
            if (confirm(message)) {
                callback();
            }
        }

        // Status badge helper
        function getStatusBadge(status) {
            const badges = {
                'actif': '<span class="badge bg-success">نشط</span>',
                'inactif': '<span class="badge bg-warning">غير نشط</span>',
                'suppression': '<span class="badge bg-danger">محذوف</span>',
                'planifiee': '<span class="badge bg-warning">مخطط</span>',
                'validee': '<span class="badge bg-info">مؤكد</span>',
                'payee': '<span class="badge bg-success">مدفوع</span>',
                'calcule': '<span class="badge bg-warning">محسوب</span>',
                'paye': '<span class="badge bg-success">مدفوع</span>'
            };
            return badges[status] || '<span class="badge bg-secondary">غير معروف</span>';
        }

        // Format numbers
        function formatNumber(number, decimals = 2) {
            return parseFloat(number).toFixed(decimals).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // Enhanced form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>

    @stack('scripts')
</body>
</html>