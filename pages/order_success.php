<?php
// pages/order_success.php

// Start session only if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// If no order ID, redirect to orders page
if ($order_id === 0) {
    header('Location: orders.php');
    exit();
}

// Get order details from database
$order_stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order = $order_stmt->get_result()->fetch_assoc();

if (!$order) {
    $_SESSION['error'] = "Order not found!";
    header('Location: orders.php');
    exit();
}

// Get order items
$items_stmt = $conn->prepare("SELECT oi.*, p.name, p.image 
                             FROM order_items oi 
                             JOIN products p ON oi.product_id = p.id 
                             WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$order_items = $items_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - NeoCart</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/animate.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Success Message -->
                <div class="card border-success mb-4 animate__animated animate__fadeInUp">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                        <h2 class="text-success mb-3">Order Placed Successfully!</h2>
                        <p class="lead mb-4">Thank you for your purchase. Your order has been confirmed and will be shipped soon.</p>
                        
                        <div class="alert alert-info">
                            <strong>Order ID: #<?php echo $order_id; ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="card mb-4 animate__animated animate__fadeInUp">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Customer Information</h6>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                                <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Shipping Address</h6>
                                <p><?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card mb-4 animate__animated animate__fadeInUp">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Order Items</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php while ($item = $order_items->fetch_assoc()): ?>
                        <div class="p-3 border-bottom">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img src="../assets/images/products/<?php echo $item['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                         class="img-fluid rounded" style="height: 60px; object-fit: cover;">
                                </div>
                                <div class="col-md-4">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                </div>
                                <div class="col-md-2 text-center">
                                    <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                </div>
                                <div class="col-md-2 text-center">
                                    <strong>₹<?php echo number_format($item['price'], 2); ?></strong>
                                </div>
                                <div class="col-md-2 text-end">
                                    <strong>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        
                        <div class="p-3 bg-light">
                            <div class="row">
                                <div class="col-8">
                                    <h5 class="mb-0">Total Amount</h5>
                                </div>
                                <div class="col-4 text-end">
                                    <h5 class="text-success mb-0">₹<?php echo number_format($order['total_amount'], 2); ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center animate__animated animate__fadeInUp">
                    <div class="d-grid gap-2 d-md-block">
                        <a href="orders.php" class="btn btn-primary btn-lg me-md-3">
                            <i class="fas fa-shopping-bag me-2"></i>View All Orders
                        </a>
                        <a href="products.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-shopping-cart me-2"></i>Continue Shopping
                        </a>
                    </div>
                    
                    <div class="mt-4">
                        <p class="text-muted">
                            You will receive an email confirmation shortly. 
                            For any queries, contact our <a href="../contact.php">customer support</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>