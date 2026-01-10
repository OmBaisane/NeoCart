<?php
// pages/register.php - FIXED VERSION

// Start session at top to avoid header errors
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$registered = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Server-side validation
    if (empty($name) || !preg_match("/^[A-Za-z\s]{3,50}$/", $name)) {
        $errors[] = "Name must be 3-50 characters and contain only letters and spaces.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Only proceed if no errors
    if (empty($errors)) {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Email already registered. Please use a different email.";
        } else {
            // SECURE: Password hashing
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $name, $email, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $registered = true;
                $_SESSION['success'] = "Registration successful! Please login to continue.";
            } else {
                $errors[] = "Registration failed. Please try again later.";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

// Include header after session handling
require_once __DIR__ . '/../includes/header.php';
?>

<main class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card animate__animated animate__fadeInUp">
                <div class="card-header">
                    <h3 class="text-center mb-0">Create Account</h3>
                </div>
                <div class="card-body">
                    <?php if ($registered): ?>
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle me-2"></i>
                            Registration successful! 
                            <a href="login.php" class="alert-link">Click here to login</a>
                        </div>
                    <?php else: ?>
                        <!-- Error Messages -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger animate__animated animate__shakeX">
                                <?php foreach ($errors as $err): ?>
                                    <div><?php echo htmlspecialchars($err); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Registration Form -->
                        <form method="post" id="registerForm">
                            <div class="mb-3">
                                <label for="regName" class="form-label">Full Name</label>
                                <input name="name" id="regName" class="form-control" 
                                       placeholder="Enter your full name" 
                                       value="<?php echo htmlspecialchars($name ?? ''); ?>" 
                                       required autocomplete="name">
                            </div>
                            <div class="mb-3">
                                <label for="regEmail" class="form-label">Email Address</label>
                                <input name="email" id="regEmail" class="form-control" type="email" 
                                       placeholder="Enter your email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                       required autocomplete="email">
                            </div>
                            <div class="mb-3">
                                <label for="regPassword" class="form-label">Password</label>
                                <input name="password" id="regPassword" class="form-control" type="password" 
                                       placeholder="Enter password (min. 6 characters)" 
                                       required autocomplete="new-password">
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirm Password</label>
                                <input name="confirm_password" id="confirmPassword" class="form-control" type="password" 
                                       placeholder="Confirm your password" 
                                       required autocomplete="new-password">
                            </div>
                            <button type="submit" class="btn btn-success w-100 btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <p class="mb-0">Already have an account? 
                                <a href="login.php" class="text-decoration-none">Login here</a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>