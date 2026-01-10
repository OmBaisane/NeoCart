<?php
// Admin Header - Security Check
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - NeoCart</title>
  
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/font-awesome/css/all.min.css" rel="stylesheet">
  <link href="../assets/css/animate.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">

  <style>
    body{
      margin: 0;
      padding: 0;
    }
    .admin-navbar {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .admin-content {
      background: #f8f9fa;
      min-height: calc(100vh - 76px);
      padding: 20px;
    }
    .stat-card {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      transition: transform 0.3s ease;
    }
    .stat-card:hover {
      transform: translateY(-5px);
    }
    
    /* Active link styling */
    .navbar-nav .nav-link.active {
      background: rgba(255,255,255,0.2);
      border-radius: 5px;
    }
  </style>
</head>
<body>
  
  <!-- Admin Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark admin-navbar">
    <div class="container-fluid">
      <a class="navbar-brand" href="dashboard.php">
        <i class="fas fa-cog me-2"></i>
        <strong>NeoCart Admin</strong>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
      data-bs-target="#adminNavbar">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="adminNavbar">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
               href="dashboard.php">
              <i class="fas fa-tachometer-alt me-1"></i>Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" 
               href="products.php">
              <i class="fas fa-box me-1"></i>Products
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" 
               href="orders.php">
              <i class="fas fa-shopping-bag me-1"></i>Orders
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" 
               href="users.php">
              <i class="fas fa-users me-1"></i>Users
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-warning" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
              <i class="fas fa-user-shield me-1"></i><?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <!-- Main Content -->
      <div class="col-12 admin-content">