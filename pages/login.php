<?php
// pages/login.php - FIXED VERSION

// START AT VERY TOP - NO SPACES BEFORE PHP TAG
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$redirect = isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : 'products.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    // Input validation
    if (empty($email) || empty($pass)) {
        $errors[] = "Both Email and Password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    } else {
        // SECURE: Prepared statement
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // SECURE: Password verification
            if (password_verify($pass, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $email;
                $_SESSION['logged_in'] = true;
                
                header('Location: ' . $redirect);
                exit();
            } else {
                $errors[] = "Invalid password. Please try again.";
            }
        } else {
            $errors[] = "No account found with this email address.";
        }
        $stmt->close();
    }
}

// NOW INCLUDE HEADER AFTER SESSION HANDLING
require_once __DIR__ . '/../includes/header.php';
?>

<main class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card login-card animate__animated animate__fadeInUp">
                <div class="card-header login-header">
                    <h3 class="text-center mb-0 text-white">Login to NeoCart</h3>
                </div>
                <div class="card-body">
                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger animate__animated animate__shakeX">
                            <?php foreach ($errors as $err): ?>
                                <div><?php echo htmlspecialchars($err); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form method="post" id="loginForm">
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">Email Address</label>
                            <input name="email" id="loginEmail" class="form-control" type="email" 
                                   placeholder="Enter your email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                   required autofocus autocomplete="username">
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="loginPassword" name="password" 
                                   placeholder="Enter your password" required autocomplete="current-password">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <p class="mb-2">
                            <a href="forgot_password.php" class="text-decoration-none">
                                <i class="fas fa-key me-1"></i>Forgot Password?
                            </a>
                        </p>
                        <p class="mb-0">Don't have an account? 
                            <a href="register.php" class="text-decoration-none">Register here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="../assets/js/main.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>