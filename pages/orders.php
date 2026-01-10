<?php
// pages/orders.php

// Start session only if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/header.php';

// require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = (int)$_SESSION['user_id'];
?>

<div class="container">
  <h2 class="mt-4 animate__animated animate__fadeInDown">My Orders</h2>

<?php
if (isset($_GET['view'])) {
    $order_id = (int)$_GET['view'];
    
    // Get order details with tracking number
    $check_status = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
    $status_exists = $check_status->num_rows > 0;
    
    $check_tracking = $conn->query("SHOW COLUMNS FROM orders LIKE 'tracking_number'");
    $tracking_exists = $check_tracking->num_rows > 0;
    
    if ($status_exists && $tracking_exists) {
        $stmt = $conn->prepare("SELECT id, customer_name, customer_email, customer_address, total_amount, created_at, status, tracking_number FROM orders WHERE id = ? AND user_id = ?");
    } else {
        $stmt = $conn->prepare("SELECT id, customer_name, customer_email, customer_address, total_amount, created_at, 'confirmed' as status, '' as tracking_number FROM orders WHERE id = ? AND user_id = ?");
    }
    
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if (!$order) {
        echo '<div class="alert alert-danger animate__animated animate__shakeX">Order not found.</div>';
    } else {
        echo '<h4 class="animate__animated animate__fadeInUp">Order #'.htmlspecialchars($order['id']).' — ₹'.number_format($order['total_amount'],2).'</h4>';
        echo '<p class="animate__animated animate__fadeIn">Placed on: '.htmlspecialchars($order['created_at']).'<br/>Address: '.nl2br(htmlspecialchars($order['customer_address'])).'</p>';

        // Get order items
        $it = $conn->prepare("SELECT oi.quantity, oi.price, p.name, p.image FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?");
        $it->bind_param("i", $order_id);
        $it->execute();
        $res = $it->get_result();
        
        echo '<table class="table table-striped animate__animated animate__fadeIn"><thead class="table-dark"><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>';
        while ($row = $res->fetch_assoc()) {
            $item_total = $row['price'] * $row['quantity'];
            echo '<tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="../assets/images/products/'.htmlspecialchars($row['image']).'" 
                                 alt="'.htmlspecialchars($row['name']).'" 
                                 class="img-thumbnail me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            '.htmlspecialchars($row['name']).'
                        </div>
                    </td>
                    <td>'.(int)$row['quantity'].'</td>
                    <td>₹'.number_format($row['price'],2).'</td>
                    <td><strong>₹'.number_format($item_total,2).'</strong></td>
                  </tr>';
        }
        echo '</tbody></table>';

        // ORDER STATUS TRACKING - VISUAL PROGRESS
        $status = strtolower($order['status'] ?? 'confirmed');
        $tracking_number = $order['tracking_number'] ?? '';
        
        // Define status steps
        $status_steps = [
            'pending' => ['pending', 'confirmed', 'shipped', 'delivered'],
            'confirmed' => ['pending', 'confirmed', 'shipped', 'delivered'],
            'shipped' => ['pending', 'confirmed', 'shipped', 'delivered'],
            'delivered' => ['pending', 'confirmed', 'shipped', 'delivered'],
            'cancelled' => ['cancelled']
        ];
        
        $current_steps = $status_steps[$status] ?? $status_steps['confirmed'];
        $current_index = array_search($status, $current_steps);
        
        echo '<div class="card mt-4 animate__animated animate__fadeInUp">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Order Tracking</h5>
                </div>
                <div class="card-body">';
        
        if ($status === 'cancelled') {
            echo '<div class="text-center py-3">
                    <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                    <h4 class="text-danger">Order Cancelled</h4>
                    <p class="text-muted">This order has been cancelled.</p>
                  </div>';
        } else {
            // Visual Progress Bar
            echo '<div class="order-progress mb-4">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: '.(($current_index + 1) / count($current_steps) * 100).'%" 
                             aria-valuenow="'.(($current_index + 1) / count($current_steps) * 100).'" 
                             aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">';
            
            foreach ($current_steps as $index => $step) {
                $is_completed = $index <= $current_index;
                $is_current = $index === $current_index;
                
                echo '<div class="text-center">
                        <div class="step-icon '.( $is_completed ? 'completed' : '' ).' '.( $is_current ? 'current' : '' ).' 
                                    mx-auto mb-1 rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 40px; height: 40px; background: '.($is_completed ? '#28a745' : '#e9ecef').'; color: '.($is_completed ? 'white' : '#6c757d').';">
                            <i class="fas '.($step === 'pending' ? 'fa-clock' : 
                                            ($step === 'confirmed' ? 'fa-check' : 
                                            ($step === 'shipped' ? 'fa-shipping-fast' : 'fa-box-open'))).'"></i>
                        </div>
                        <small class="fw-bold '.($is_completed || $is_current ? 'text-success' : 'text-muted').'">
                            '.ucfirst($step).'
                        </small>
                      </div>';
            }
            
            echo '</div></div>';
            
            // Status Details
            echo '<div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Current Status:</strong> 
                            <span class="badge bg-'.($status === 'confirmed' ? 'success' : 
                                                  ($status === 'pending' ? 'warning' : 
                                                  ($status === 'shipped' ? 'info' : 
                                                  ($status === 'delivered' ? 'primary' : 'secondary')))).'">
                                '.ucfirst($status).'
                            </span>
                        </p>
                        <p class="mb-0"><strong>Order Date:</strong> '.date('F j, Y g:i A', strtotime($order['created_at'])).'</p>
                    </div>';
            
            if (!empty($tracking_number)) {
                echo '<div class="col-md-6">
                        <p class="mb-2"><strong>Tracking Number:</strong> 
                            <code>'.htmlspecialchars($tracking_number).'</code>
                        </p>
                        <p class="mb-0"><strong>Shipping:</strong> Track your package with the number above</p>
                      </div>';
            }
            
            echo '</div>';
        }
        
        echo '</div></div>';

        echo '<a href="orders.php" class="btn btn-outline-primary mt-3 animate__animated animate__fadeIn">
                <i class="fas fa-arrow-left me-2"></i>Back to Orders
              </a>';
    }
} else {
    // Get all orders for the user
    $check_status = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
    $status_exists = $check_status->num_rows > 0;
    
    if ($status_exists) {
        $stmt = $conn->prepare("SELECT id, customer_name, total_amount, created_at, status FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    } else {
        $stmt = $conn->prepare("SELECT id, customer_name, total_amount, created_at, 'confirmed' as status FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows === 0) {
        echo '<div class="alert alert-info animate__animated animate__fadeIn text-center py-5">
                <i class="fas fa-shopping-bag fa-3x mb-3 text-muted"></i>
                <h4>No Orders Found</h4>
                <p class="mb-3">You haven\'t placed any orders yet.</p>
                <a href="products.php" class="btn btn-primary">Start Shopping</a>
              </div>';
    } else {
        echo '<div class="table-responsive animate__animated animate__fadeIn">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        while ($o = $res->fetch_assoc()) {
            $status_badge = 'bg-success';
            $status_text = $o['status'];
            if (isset($o['status'])) {
                switch(strtolower($o['status'])) {
                    case 'confirmed': $status_badge = 'bg-success'; break;
                    case 'pending': $status_badge = 'bg-warning'; break;
                    case 'shipped': $status_badge = 'bg-info'; break;
                    case 'delivered': $status_badge = 'bg-primary'; break;
                    case 'cancelled': $status_badge = 'bg-danger'; break;
                    default: $status_badge = 'bg-success';
                }
            }
            
            echo '<tr>
                    <td><strong>#'.htmlspecialchars($o['id']).'</strong></td>
                    <td>'.htmlspecialchars($o['customer_name']).'</td>
                    <td><strong>₹'.number_format($o['total_amount'],2).'</strong></td>
                    <td>'.date('M j, Y', strtotime($o['created_at'])).'</td>
                    <td><span class="badge '.$status_badge.'">'.ucfirst($status_text).'</span></td>
                    <td>
                        <a href="orders.php?view='.urlencode($o['id']).'" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye me-1"></i>View Details
                        </a>
                    </td>
                  </tr>';
        }
        echo '</tbody></table></div>';
    }
}
?>
</div>

<style>
.step-icon {
    transition: all 0.3s ease;
    border: 3px solid transparent;
}
.step-icon.completed {
    background: #28a745 !important;
    color: white !important;
    border-color: #28a745;
}
.step-icon.current {
    border-color: #007bff;
    transform: scale(1.1);
}
.order-progress {
    position: relative;
}
.progress {
    border-radius: 10px;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>