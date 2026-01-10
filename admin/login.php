<?php
// admin/login.php
session_start();
require_once __DIR__ . '/../config/db.php';

// Secure admin credentials
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

// Already logged in check
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Rate limiting
    if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 5) {
        $errors[] = "Too many failed attempts. Please try again later.";
    }
    
    // Validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    
    if (empty($errors)) {
        if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
            // Successful login
            $_SESSION['is_admin'] = true;
            $_SESSION['admin_name'] = 'Administrator';
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['login_attempts'] = 0;
            
            header('Location: dashboard.php');
            exit();
        } else {
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            $errors[] = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - NeoCart</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body{
            margin: 0;
            padding: 0;
        }
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
            transition: transform 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
        }
        .login-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 10px 10px;
            animation: float 20s linear infinite;
        }
        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-10px, -10px) rotate(360deg); }
        }
        .login-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: inline-block;
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .login-body {
            padding: 2.5rem 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        .form-control {
            border-radius: 12px;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            height: calc(3.5rem + 2px);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 5;
            font-size: 1.1rem;
        }
        .form-label {
            position: absolute;
            left: 3rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            transition: all 0.3s ease;
            pointer-events: none;
            background: white;
            padding: 0 0.5rem;
            margin: 0;
        }
        .form-control:focus + .form-label,
        .form-control:not(:placeholder-shown) + .form-label {
            top: 0;
            font-size: 0.875rem;
            color: #667eea;
            background: white;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        .security-notice {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 1px solid #ffecb5;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
            text-align: center;
        }
        
        /* Mobile Responsive */
        @media (max-width: 576px) {
            .login-container {
                padding: 15px;
            }
            .login-card {
                border-radius: 15px;
            }
            .login-header {
                padding: 2rem 1.5rem;
            }
            .login-body {
                padding: 2rem 1.5rem;
            }
            .login-icon {
                font-size: 2.5rem;
            }
            .btn-login {
                padding: 12px;
                font-size: 1rem;
            }
        }
        
        @media (max-width: 400px) {
            .login-header {
                padding: 1.5rem 1rem;
            }
            .login-body {
                padding: 1.5rem 1rem;
            }
            .form-control {
                padding: 0.875rem 0.875rem 0.875rem 2.5rem;
            }
            .input-icon {
                left: 12px;
            }
            .form-label {
                left: 2.5rem;
            }
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <!-- Header -->
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h3 class="mb-2 fw-bold">Admin Access</h3>
            <p class="mb-0 opacity-90">NeoCart Management System</p>
        </div>
        
        <!-- Login Form -->
        <div class="login-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div>
                            <strong>Authentication Failed</strong>
                            <div class="small mt-1"><?php echo htmlspecialchars(implode(', ', $errors)); ?></div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 3): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-shield-alt me-2"></i>
                    <strong>Security Alert:</strong> Multiple failed login attempts detected.
                </div>
            <?php endif; ?>

            <form method="post" id="loginForm">
                <div class="form-group">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder=" " value="<?php echo htmlspecialchars($username); ?>" 
                           required autofocus autocomplete="username">
                    <label for="username" class="form-label">Username</label>
                </div>
                
                <div class="form-group">
                    <i class="fas fa-key input-icon"></i>
                    <input type="password" name="password" class="form-control" 
                          placeholder="password" required autocomplete="current-password">
                    <label for="password" class="form-label">Password</label>
                </div>

                <button type="submit" class="btn btn-login text-white w-100 py-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In to Dashboard
                </button>
            </form>

            <!-- Security Notice -->
            <div class="security-notice">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="fas fa-shield-alt text-warning me-2"></i>
                    <small class="text-dark">
                        <strong>Restricted Access:</strong> Authorized personal only
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    
    if (!username || !password) {
        e.preventDefault();
        return false;
    }
    
    // Add loading state
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Authenticating...';
    btn.disabled = true;
    
    // Re-enable button after 3 seconds in case of error
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }, 3000);
});

// Floating label functionality
document.querySelectorAll('.form-control').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.classList.add('focused');
    });
    
    input.addEventListener('blur', function() {
        if (!this.value) {
            this.parentElement.classList.remove('focused');
        }
    });
    
    // Check on page load if there's existing value
    if (input.value) {
        input.parentElement.classList.add('focused');
    }
});
</script>
</body>
</html>