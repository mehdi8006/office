<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SGCCL</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: whitesmoke;
            
            height: 100vh;
            overflow-x: hidden;
        }

        .login-wrapper {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
            flex-shrink: 0;
        }

        .logo-section {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .logo-icon i {
            font-size: 28px;
            color: white;
        }

        .btn-login {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.25);
            transition: all 0.3s ease;
            height: 48px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(40, 167, 69, 0.35);
            background: linear-gradient(135deg, #1e7e34 0%, #17a2b8 100%);
        }

        .form-control {
            height: 48px;
            border-radius: 12px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: #28a745;
        }

        .password-field {
            padding-right: 50px !important;
        }

        @media (max-width: 768px) {
            .login-container {
                max-width: 350px;
                margin: 10px;
            }
            
            .logo-icon {
                width: 60px;
                height: 60px;
            }
            
            .logo-icon i {
                font-size: 24px;
            }
        }

        @media (max-width: 576px) {
            .login-wrapper {
                padding: 10px;
            }
            
            .login-container {
                max-width: 320px;
                padding: 2rem 1.5rem !important;
            }
            
            .form-control {
                height: 44px;
            }
            
            .btn-login {
                height: 44px;
            }
        }

        @media (max-width: 360px) {
            .login-container {
                max-width: 300px;
                padding: 1.5rem 1rem !important;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container p-4">
            <!-- Logo Section -->
            <div class="text-center mb-4">
    <div class="logo-section mb-3">
        <div class="logo-icon">
            <img src="{{ asset('image/bg.jpg') }}" alt="Logo" style="width: 100px; height: 60px; border-radius: 50%;">
        </div>
        <h1 class="h2 fw-bold text-dark mb-0">SGCCL</h1>
    </div>
    <p class="text-muted mb-0">Connectez-vous à votre compte</p>
</div>


                    <!-- Success Message -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Error Messages -->
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Erreur dans les données saisies
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}" novalidate>
                        @csrf
                        
                        <!-- Matricule Field -->
                        <div class="mb-3">
                            <label for="matricule" class="form-label fw-medium">
                                <i class="fas fa-id-card me-2 text-success"></i>
                                Numéro d'inscription
                            </label>
                            <input type="text" 
                                   id="matricule" 
                                   name="matricule" 
                                   class="form-control @error('matricule') is-invalid @enderror" 
                                   placeholder="Entrez votre numéro d'inscription" 
                                   value="{{ old('matricule') }}" 
                                   required>
                            @error('matricule')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-medium">
                                <i class="fas fa-lock me-2 text-success"></i>
                                Mot de passe
                            </label>
                            <div class="position-relative">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-control password-field @error('password') is-invalid @enderror" 
                                       placeholder="Entrez votre mot de passe" 
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        

                        <!-- Login Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-login text-white fw-semibold" id="loginBtn">
                                <span id="buttonText">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Se connecter
                                </span>
                                <span id="loadingText" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Connexion...
                                </span>
                            </button>
                        </div>
                    </form>

                    <!-- Footer -->
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            Système de Gestion des Coopératives Agricoles
                        </small>
                    </div>
                </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
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
</body>
</html>