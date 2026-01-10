<?php
// pages/reset_password.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';

// Set timezone
date_default_timezone_set('Asia/Kolkata');

$error = '';
$success = '';
$valid_token = false;
$user_id = null;

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $error = "Invalid or missing reset token.";
} else {
    $token = trim($_GET['token']);
    
    // Validate token - Use reset_token_expiry (not expires)
    $stmt = $conn->prepare("SELECT id, reset_token_expiry FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Manual expiry check to avoid timezone issues
        if (strtotime($user['reset_token_expiry']) > time()) {
            $valid_token = true;
            $user_id = $user['id'];
        } else {
            $error = "Reset token has expired. Please request a new link.";
        }
    } else {
        $error = "Invalid reset token. Please request a new reset link.";
    }
    
    // Process password reset
    if ($valid_token && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($password) || empty($confirm_password)) {
            $error = "Please fill in all fields.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Hash new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Update password and clear reset token - Use reset_token_expiry
            $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $success = "
                    <div class='alert alert-success'>
                        <h5><i class='fas fa-check-circle me-2'></i>Password Reset Successful!</h5>
                        <p class='mb-0'>Your password has been updated successfully. You can now login with your new password.</p>
                    </div>
                    <div class='d-grid mt-3'>
                        <a href='login.php' class='btn btn-success btn-lg'>
                            <i class='fas fa-sign-in-alt me-2'></i>Proceed to Login
                        </a>
                    </div>
                ";
                $valid_token = false; // Token used
            } else {
                $error = "Error updating password. Please try again.";
            }
        }
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0 text-center">
                        <i class="fas fa-lock me-2"></i>Reset Your Password
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                        <div class="d-grid mt-3">
                            <a href="forgot_password.php" class="btn btn-primary">
                                <i class="fas fa-key me-2"></i>Request New Reset Link
                            </a>
                        </div>
                    <?php elseif ($success): ?>
                        <?php echo $success; ?>
                    <?php elseif ($valid_token): ?>
                        <p class="text-muted mb-4">
                            Enter your new password below. Make sure it's strong and secure.
                        </p>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter new password" minlength="6" required>
                                <div class="form-text">Minimum 6 characters</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label fw-bold">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Confirm new password" minlength="6" required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save me-2"></i>Reset Password
                                </button>
                                <a href="login.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Login
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($valid_token): ?>
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        This reset link will expire soon
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Password confirmation validation
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Passwords don't match");
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('change', validatePassword);
    confirmPassword.addEventListener('keyup', validatePassword);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>