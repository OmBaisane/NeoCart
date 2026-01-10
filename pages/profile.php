<?php
// pages/profile.php - COMPLETE FIXED VERSION

// Start session at the VERY TOP
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get user details
$user_stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Get orders count
$orders_stmt = $conn->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_count = $orders_stmt->get_result()->fetch_assoc()['order_count'];

// Update profile if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Basic validation
    if (empty($name) || empty($email)) {
        $error = "Name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email already exists (excluding current user)
        $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $email_check->bind_param("si", $email, $user_id);
        $email_check->execute();

        if ($email_check->get_result()->num_rows > 0) {
            $error = "Email already exists. Please use a different email.";
        } else {
            // Update user details
            $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $name, $email, $user_id);

            if ($update_stmt->execute()) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $success = "Profile updated successfully!";

                // Update password if provided
                if (!empty($new_password)) {
                    if (empty($current_password)) {
                        $error = "Current password is required to set new password.";
                    } else {
                        // Verify current password
                        $verify_stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                        $verify_stmt->bind_param("i", $user_id);
                        $verify_stmt->execute();
                        $user_data = $verify_stmt->get_result()->fetch_assoc();

                        if (password_verify($current_password, $user_data['password'])) {
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $password_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                            $password_stmt->bind_param("si", $hashed_password, $user_id);

                            if ($password_stmt->execute()) {
                                $success .= " Password updated successfully!";
                            } else {
                                $error = "Failed to update password.";
                            }
                        } else {
                            $error = "Current password is incorrect.";
                        }
                    }
                }
            } else {
                $error = "Failed to update profile. Please try again.";
            }
        }
    }

    // Refresh user data
    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();
}

// NOW include header
require_once __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - NeoCart</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/animate.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4 animate__animated animate__fadeIn">
                    <i class="fas fa-user-circle me-2"></i>My Profile
                </h2>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success animate__animated animate__fadeIn">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger animate__animated animate__shakeX">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Stats -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-4x text-primary"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>

                        <div class="row mt-4">
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <h5 class="text-primary mb-1"><?php echo $orders_count; ?></h5>
                                    <small class="text-muted">Orders</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <h5 class="text-success mb-1">
                                        <?php echo date('M Y', strtotime($user['created_at'])); ?>
                                    </h5>
                                    <small class="text-muted">Member Since</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Profile</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" name="name" class="form-control"
                                        value="<?php echo htmlspecialchars($user['name']); ?>"
                                        required autocomplete="name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" name="email" class="form-control"
                                        value="<?php echo htmlspecialchars($user['email']); ?>"
                                        required autocomplete="email">
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="mb-3">Change Password (Optional)</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control"
                                        placeholder="Enter current password" autocomplete="current-password">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control"
                                        placeholder="Enter new password" autocomplete="new-password">
                                </div>
                            </div>

                            <small class="text-muted d-block mb-3">
                                Leave password fields empty if you don't want to change password.
                            </small>

                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4 mb-3">
                                <a href="orders.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-shopping-bag me-2"></i>My Orders
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="cart.php" class="btn btn-outline-success w-100">
                                    <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="../index.php" class="btn btn-outline-info w-100">
                                    <i class="fas fa-home me-2"></i>Continue Shopping
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>