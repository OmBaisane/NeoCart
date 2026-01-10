<?php
// includes/header.php

// Start session at the very top
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// Base Path Handling
$uri = $_SERVER['REQUEST_URI'];
$base = '';

if (strpos($uri, '/pages') !== false || strpos($uri, '/admin') !== false) {
    $base = '../';
}

// Cart Count
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT SUM(quantity) as cnt FROM cart WHERE cart_user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $cart_count = $res['cnt'] ? (int)$res['cnt'] : 0;
}

// Determine user login status for CSS class
$user_logged_in = (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeoCart - Modern Shopping Experience</title>

    <!-- Favicon -->
    <link rel="icon" href="<?php echo $base; ?>assets/images/Logo.png">

    <!-- CSS Files -->
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/animate.min.css">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css">
</head>

<body class="<?php echo $user_logged_in ? 'user-logged-in' : ''; ?>">
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-gradient fixed-top shadow">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $base; ?>index.php">
                <img src="<?php echo $base; ?>assets/images/Logo.png" alt="NeoCart" height="30" class="me-2">
                <strong>NeoCart</strong>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base; ?>index.php">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base; ?>pages/products.php">
                            <i class="fas fa-shopping-bag me-1"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base; ?>about.php">
                            <i class="fas fa-info-circle me-1"></i> About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base; ?>contact.php">
                            <i class="fas fa-phone me-1"></i> Contact
                        </a>
                    </li>
                </ul>

                <!-- Search - SINGLE INSTANCE -->
                <div class="col-md-4 me-3">
                    <form action="<?php echo $base; ?>NeoCart/pages/products.php" method="GET" id="headerSearchForm">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search products..." id="headerSearchInput"
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                autocomplete="off">
                            <button class="btn btn-outline-light" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- User Section -->
                <ul class="navbar-nav">

                    <!-- Cart Icon -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?php echo $base; ?>pages/cart.php">
                            <i class="fas fa-shopping-cart"></i> Cart
                            <?php if ($cart_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge bg-danger" id="cartCount">
                                    <?php echo $cart_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <?php if ($user_logged_in): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i> <?php echo $_SESSION['user_name']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo $base; ?>pages/profile.php">
                                        <i class="fas fa-user-circle me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="<?php echo $base; ?>pages/orders.php">
                                        <i class="fas fa-shopping-bag me-2"></i>Orders</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?php echo $base; ?>logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base; ?>pages/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base; ?>pages/register.php">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid" style="margin-top: 80px;">

        <!-- Bootstrap Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
                <?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
                <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>