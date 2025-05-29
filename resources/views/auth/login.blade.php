<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - SGCCL</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: 
            linear-gradient(rgba(170, 170, 170, 0.5)),
            url(vache-noire-et-blanche-paissant-sur-le-paturage-pendant-la-journee.jpg);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;

            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

       

        .login-container {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(10px);
            padding: 25px 20px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 420px;
            text-align: center;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo-section {
            margin-bottom: 10px;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 5px;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
        }

        .logo-icon i {
            font-size: 30px;
            color: white;
        }

        .login-title {
            color: #2d3436;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            color: #636e72;
            font-size: 15px;
            font-weight: 400;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: right;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            margin-right: 5px;
            color: #2d3436;
            font-weight: 500;
            font-size: 14px;
        }

        .input-container {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 400;
            transition: all 0.3s ease;
            direction: rtl;
            background: #ffffff;
        }

        .form-input:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
            background: #ffffff;
        }

        .form-input::placeholder {
            color: #adb5bd;
            font-weight: 400;
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s ease;
            padding: 5px;
        }

        .password-toggle:hover {
            color: #28a745;
        }

        .form-input[type="password"] {
            padding-left: 55px;
        }

        .forgot-password {
            text-align: left;
            margin-top: 10px;
        }

        .forgot-password a {
            color: #28a745;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #1e7e34;
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.25);
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(40, 167, 69, 0.35);
            background: linear-gradient(135deg, #1e7e34 0%, #17a2b8 100%);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .footer-text {
            margin-top: 15px;
            color: #6c757d;
            font-size: 13px;
            font-weight: 400;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 40px 30px;
                margin: 10px;
                max-width: 90%;
            }
            
            .login-title {
                font-size: 28px;
            }

            .logo-icon {
                width: 70px;
                height: 70px;
            }

            .logo-icon i {
                font-size: 30px;
            }
        }

        /* Loading animation */
        .loading {
            position: relative;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <div class="logo-icon">
                <i class="fas fa-seedling"></i>
            </div>
            <h1 class="login-title">SGCCL</h1>
            <p class="login-subtitle">أدخل بياناتك للوصول إلى حسابك</p>
        </div>

        <form class="login-form" >
            <div class="form-group">
                <label for="matricule" class="form-label">رقم التسجيل</label>
                <div class="input-container">
                    <input type="text" id="matricule" name="matricule" class="form-input" placeholder="أدخل رقم التسجيل" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">كلمة المرور</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" class="form-input" placeholder="أدخل كلمة المرور" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
                <div class="forgot-password">
                    <a href="#">نسيت كلمة المرور؟</a>
                </div>
            </div>

            <button type="submit" class="login-button" id="loginBtn">
                <span id="buttonText">دخول</span>
            </button>
        </form>

        <div class="footer-text">
            نظام إدارة التعاونيات الفلاحية
        </div>
    </div>

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

        
           
    </script>
</body>
</html>