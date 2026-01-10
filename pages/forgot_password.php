<?php
// pages/forgot_password.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';

// Set timezone
date_default_timezone_set('Asia/Kolkata');

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            // Check if email exists
            $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Generate reset token (32 characters)
                $token = bin2hex(random_bytes(16));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Check and create columns if they don't exist
                $check_columns = $conn->query("SHOW COLUMNS FROM users LIKE 'reset_token'");
                if ($check_columns->num_rows == 0) {
                    $conn->query("ALTER TABLE users ADD COLUMN reset_token VARCHAR(64) NULL");
                    $conn->query("ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME NULL");
                }
                
                // Store token in database - Use reset_token_expiry (not expires)
                $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                
                if (!$update_stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $update_stmt->bind_param("ssi", $token, $expires, $user['id']);
                
                if ($update_stmt->execute()) {
                    // Create reset link with proper URL encoding
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . urlencode($token);
                    
                    $message = "
                        <div class='alert alert-success'>
                            <h5><i class='fas fa-check-circle me-2'></i>Password Reset Link Sent!</h5>
                            <p class='mb-2'>A password reset link has been generated for your account.</p>
                            <div class='bg-light p-3 rounded mt-3'>
                                <strong>Reset Link:</strong><br>
                                <a href='{$reset_link}' class='small' target='_blank'>{$reset_link}</a>
                            </div>
                            <p class='mt-2 mb-0 small text-muted'>
                                <i class='fas fa-info-circle me-1'></i>
                                This link will expire in 1 hour.
                            </p>
                        </div>
                    ";
                } else {
                    throw new Exception("Execute failed: " . $update_stmt->error);
                }
            } else {
                $error = "No account found with this email address.";
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0 text-center">
                        <i class="fas fa-key me-2"></i>Forgot Password
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($message): ?>
                        <?php echo $message; ?>
                    <?php else: ?>
                        <p class="text-muted mb-4">
                            Enter your email address and we'll send you a link to reset your password.
                        </p>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Enter your registered email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                       required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                                </button>
                                <a href="login.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Login
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="fas fa-shield-alt me-1"></i>
                    Secure password reset process
                </small>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>