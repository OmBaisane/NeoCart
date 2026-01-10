<?php
// admin/dashboard.php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit();
}

// Get statistics
$prod_cnt = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'] ?? 0;
$order_cnt = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'] ?? 0;
$user_cnt = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'] ?? 0;
$revenue = $conn->query("SELECT SUM(total_amount) AS total FROM orders WHERE status = 'confirmed'")->fetch_assoc()['total'] ?? 0;

// Get recent orders with user names
$recent_orders = $conn->query("
    SELECT o.id, o.user_id, u.name as user_name, o.total_amount, o.created_at, o.status 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");

// Get recent products
$recent_products = $conn->query("SELECT id, name, price, image FROM products ORDER BY id DESC LIMIT 4");
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard - NeoCart</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/font-awesome/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/animate.min.css">
  <style>
    body{
      margin: 0;
      padding: 0;
    }
  </style>
</head>
<body>

<div class="container mt-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h3 class="mb-1 magictime spaceInRight">
        <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
      </h3>
      <p class="text-muted mb-0">Welcome back, <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>!</p>
    </div>
    <div class="d-flex gap-2">
      <a href="products.php" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-box me-1"></i>Products
      </a>
      <a href="orders.php" class="btn btn-outline-success btn-sm">
        <i class="fas fa-shopping-bag me-1"></i>Orders
      </a>
      <a href="users.php" class="btn btn-outline-info btn-sm">
        <i class="fas fa-users me-1"></i>Users
      </a>
      <a href="logout.php" class="btn btn-outline-danger btn-sm">
        <i class="fas fa-sign-out-alt me-1"></i>Logout
      </a>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card text-white bg-primary shadow magictime tinUp">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="card-title">Total Products</h6>
              <h3 class="mb-0"><?php echo number_format($prod_cnt); ?></h3>
            </div>
            <i class="fas fa-box fa-2x opacity-75"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card text-white bg-success shadow magictime tinUp">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="card-title">Total Orders</h6>
              <h3 class="mb-0"><?php echo number_format($order_cnt); ?></h3>
            </div>
            <i class="fas fa-shopping-bag fa-2x opacity-75"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card text-white bg-info shadow magictime tinUp">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="card-title">Total Users</h6>
              <h3 class="mb-0"><?php echo number_format($user_cnt); ?></h3>
            </div>
            <i class="fas fa-users fa-2x opacity-75"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card text-white bg-warning shadow magictime tinUp">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="card-title">Total Revenue</h6>
              <h3 class="mb-0">₹<?php echo number_format($revenue, 2); ?></h3>
            </div>
            <i class="fas fa-rupee-sign fa-2x opacity-75"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-lg-8">
      <div class="card shadow magictime puffIn">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Orders</h5>
          <a href="orders.php" class="btn btn-sm btn-light">View All</a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Order ID</th>
                  <th>Customer</th>
                  <th>Amount</th>
                  <th>Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                  <?php while($order = $recent_orders->fetch_assoc()): ?>
                    <tr>
                      <td><strong>#<?php echo $order['id']; ?></strong></td>
                      <td><?php echo htmlspecialchars($order['user_name'] ?? 'User #' . $order['user_id']); ?></td>
                      <td class="fw-bold">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                      <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                      <td>
                        <span class="badge bg-<?php 
                          echo $order['status'] === 'confirmed' ? 'success' : 'warning';
                        ?>">
                          <?php echo ucfirst($order['status']); ?>
                        </span>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                      <i class="fas fa-shopping-bag fa-2x mb-2"></i><br>
                      No orders found
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Products -->
    <div class="col-lg-4">
      <div class="card shadow magictime puffIn">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0"><i class="fas fa-cube me-2"></i>Recent Products</h5>
        </div>
        <div class="card-body">
          <?php if ($recent_products && $recent_products->num_rows > 0): ?>
            <?php while($product = $recent_products->fetch_assoc()): ?>
              <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                <?php if (!empty($product['image'])): ?>
                  <img src="../assets/images/products/<?php echo $product['image']; ?>" 
                       alt="<?php echo htmlspecialchars($product['name']); ?>"
                       class="rounded me-3" style="width: 40px; height: 40px; object-fit: cover;">
                <?php else: ?>
                  <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                       style="width: 40px; height: 40px;">
                    <i class="fas fa-cube text-muted"></i>
                  </div>
                <?php endif; ?>
                <div class="flex-grow-1">
                  <h6 class="mb-0 small"><?php echo htmlspecialchars($product['name']); ?></h6>
                  <small class="text-muted">₹<?php echo number_format($product['price'], 2); ?></small>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-muted text-center mb-0">No products found</p>
          <?php endif; ?>
          <div class="text-center mt-2">
            <a href="products.php" class="btn btn-sm btn-outline-success">Manage Products</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>