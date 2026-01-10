<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ .'/includes/header.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit();
}

// Handle order status update
if (isset($_POST['update_order'])) {
    $order_id = (int)$_POST['order_id'];
    $status = trim($_POST['status']);
    $tracking_number = trim($_POST['tracking_number']);

    $stmt = $conn->prepare("UPDATE orders SET status=?, tracking_number=? WHERE id=?");
    $stmt->bind_param("ssi", $status, $tracking_number, $order_id);
    if($stmt->execute()){
        $_SESSION['success'] = "Order #$order_id updated successfully!";
        header("Location: orders.php?view=$order_id");
        exit();
    } else {
        $_SESSION['error'] = "Error updating order!";
    }
}

// Get order statistics with all statuses
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$confirmed_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'confirmed'")->fetch_assoc()['count'];
$shipped_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'shipped'")->fetch_assoc()['count'];
$delivered_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'")->fetch_assoc()['count'];
?>

<style>
.order-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.order-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
}
.stat-card {
    border-radius: 10px;
    color: white;
    transition: transform 0.3s ease;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.order-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
}
.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}
.tracking-badge {
    background: #e9ecef;
    color: #495057;
    padding: 4px 8px;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.8rem;
}
</style>

<div class="container mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 text-primary">
                <i class="fas fa-shopping-bag me-2"></i>Manage Orders
            </h2>
            <p class="text-muted mb-0">Track and manage customer orders</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-primary">
                <div class="card-body text-center py-4">
                    <i class="fas fa-shopping-bag fa-2x mb-2 opacity-75"></i>
                    <h3 class="mb-0"><?php echo $total_orders; ?></h3>
                    <small>Total Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-warning">
                <div class="card-body text-center py-4">
                    <i class="fas fa-clock fa-2x mb-2 opacity-75"></i>
                    <h3 class="mb-0"><?php echo $pending_orders; ?></h3>
                    <small>Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-info">
                <div class="card-body text-center py-4">
                    <i class="fas fa-shipping-fast fa-2x mb-2 opacity-75"></i>
                    <h3 class="mb-0"><?php echo $shipped_orders; ?></h3>
                    <small>Shipped</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-success">
                <div class="card-body text-center py-4">
                    <i class="fas fa-check-circle fa-2x mb-2 opacity-75"></i>
                    <h3 class="mb-0"><?php echo $delivered_orders; ?></h3>
                    <small>Delivered</small>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (isset($_GET['view'])) {
        $order_id = (int)$_GET['view'];
        
        // Get order details
        $stmt = $conn->prepare("SELECT id, customer_name, customer_email, customer_address, customer_phone, total_amount, created_at, status, tracking_number FROM orders WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if (!$order) {
            echo '<div class="alert alert-danger">Order not found!</div>';
        } else {
    ?>
            <!-- Order Details -->
            <div class="card order-card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>
                        Order #<?php echo $order['id']; ?>
                    </h5>
                    <div>
                        <span class="status-badge bg-<?php 
                            echo $order['status'] === 'delivered' ? 'success' : 
                                 ($order['status'] === 'shipped' ? 'info' : 
                                 ($order['status'] === 'confirmed' ? 'primary' : 
                                 ($order['status'] === 'pending' ? 'warning' : 'secondary')));
                        ?>">
                            <i class="fas <?php 
                                echo $order['status'] === 'delivered' ? 'fa-check-circle' : 
                                     ($order['status'] === 'shipped' ? 'fa-shipping-fast' : 
                                     ($order['status'] === 'confirmed' ? 'fa-thumbs-up' : 
                                     ($order['status'] === 'pending' ? 'fa-clock' : 'fa-question')));
                            ?> me-1"></i>
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                        <?php if (!empty($order['tracking_number'])): ?>
                            <span class="tracking-badge ms-2">
                                <i class="fas fa-barcode me-1"></i><?php echo htmlspecialchars($order['tracking_number']); ?>
                            </span>
                        <?php endif; ?>
                        <span class="badge bg-light text-dark ms-2">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Customer Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100 border-0 bg-light">
                                <div class="card-body">
                                    <h6><i class="fas fa-user me-2"></i>Customer Information</h6>
                                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                                    <?php if (!empty($order['customer_phone'])): ?>
                                        <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                                    <?php endif; ?>
                                    <p class="mb-0"><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 bg-light">
                                <div class="card-body">
                                    <h6><i class="fas fa-truck me-2"></i>Shipping Address</h6>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <h6><i class="fas fa-boxes me-2"></i>Order Items</h6>
                    <?php
                    $items_stmt = $conn->prepare("SELECT oi.quantity, oi.price, p.name, p.image FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?");
                    $items_stmt->bind_param("i", $order_id);
                    $items_stmt->execute();
                    $items = $items_stmt->get_result();
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $items->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="../assets/images/products/<?php echo $item['image']; ?>" 
                                                     class="order-image me-3"
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </div>
                                    </td>
                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end">₹<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="text-end"><strong>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong></td>
                                </tr>
                                <?php endwhile; ?>
                                <tr class="table-success">
                                    <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                    <td class="text-end fw-bold fs-5">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Order Status Update -->
                    <form method="post" class="mt-4">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <h6><i class="fas fa-edit me-2"></i>Update Order Status</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Order Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                                    <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>✅ Confirmed</option>
                                    <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>🚚 Shipped</option>
                                    <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>📦 Delivered</option>
                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>❌ Cancelled</option>
                                </select>
                                <div class="form-text">
                                    Update the order status to track progress
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tracking Number</label>
                                <input type="text" name="tracking_number" class="form-control" 
                                       value="<?php echo htmlspecialchars($order['tracking_number'] ?? ''); ?>" 
                                       placeholder="Enter tracking number (optional)">
                                <div class="form-text">
                                    Add tracking number when order is shipped
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="update_order" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Update Order
                            </button>
                            <a href="orders.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Orders
                            </a>
                        </div>
                    </form>
                </div>
            </div>
    <?php
        }
    } else {
        // Show all orders
        $orders = $conn->query("SELECT id, customer_name, customer_email, total_amount, created_at, status, tracking_number FROM orders ORDER BY created_at DESC");
    ?>
        <!-- Orders List -->
        <div class="card order-card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Orders</h5>
                <span class="badge bg-light text-dark"><?php echo $total_orders; ?> orders</span>
            </div>
            <div class="card-body p-0">
                <?php if ($orders->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">Order ID</th>
                                    <th class="py-3">Customer</th>
                                    <th class="py-3 text-end">Amount</th>
                                    <th class="py-3">Date</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="py-3 text-center">Tracking</th>
                                    <th class="py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold text-primary">#<?php echo $order['id']; ?></td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">₹<?php echo number_format($order['total_amount'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <small><?php echo date('M j, Y', strtotime($order['created_at'])); ?></small>
                                        <br>
                                        <small class="text-muted"><?php echo date('g:i A', strtotime($order['created_at'])); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="status-badge bg-<?php 
                                            echo $order['status'] === 'delivered' ? 'success' : 
                                                 ($order['status'] === 'shipped' ? 'info' : 
                                                 ($order['status'] === 'confirmed' ? 'primary' : 
                                                 ($order['status'] === 'pending' ? 'warning' : 'secondary')));
                                        ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($order['tracking_number'])): ?>
                                            <span class="tracking-badge">
                                                <i class="fas fa-barcode me-1"></i>
                                                <?php echo substr($order['tracking_number'], 0, 8) . '...'; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="orders.php?view=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit me-1"></i>Manage
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted mb-3">No Orders Found</h4>
                        <p class="text-muted">Orders will appear here when customers place them.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php } ?>
</div>

<?php require_once __DIR__ .'/includes/footer.php'; ?>