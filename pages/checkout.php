<?php
// pages/checkout.php

// Start session only if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart total and items
$cart_stmt = $conn->prepare("SELECT SUM(p.price * c.quantity) as total 
                            FROM cart c 
                            JOIN products p ON p.id = c.product_id 
                            WHERE c.cart_user_id = ?");
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result()->fetch_assoc();
$total = $cart_result['total'] ? (float)$cart_result['total'] : 0.0;
$cart_stmt->close();

// Get cart items for display
$items_stmt = $conn->prepare("SELECT p.name, p.price, c.quantity, p.id as product_id
                             FROM cart c 
                             JOIN products p ON p.id = c.product_id 
                             WHERE c.cart_user_id = ?");
$items_stmt->bind_param("i", $user_id);
$items_stmt->execute();
$cart_items = $items_stmt->get_result();

$errors = [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - NeoCart</title>
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
                    <i class="fas fa-shopping-bag me-2"></i>Checkout
                </h2>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger animate__animated animate__shakeX">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($cart_items->num_rows === 0): ?>
            <div class="alert alert-warning text-center">
                <i class="fas fa-shopping-cart me-2"></i>Your cart is empty.
                <a href="products.php" class="alert-link">Continue shopping</a>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Order Summary -->
                <div class="col-lg-5 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $cart_items->data_seek(0);
                            while ($item = $cart_items->fetch_assoc()):
                                $item_total = $item['price'] * $item['quantity'];
                            ?>
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <small class="text-muted">Qty: <?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['price'], 2); ?></small>
                                    </div>
                                    <strong>₹<?php echo number_format($item_total, 2); ?></strong>
                                </div>
                            <?php endwhile; ?>

                            <div class="d-flex justify-content-between mt-3">
                                <strong>Total Amount:</strong>
                                <strong class="h5 text-success">₹<?php echo number_format($total, 2); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checkout Form -->
                <div class="col-lg-7">
                    <div class="card animate__animated animate__fadeIn">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-shipping-fast me-2"></i>Shipping Information</h5>
                        </div>
                        <div class="card-body">
                            <!-- FORM ACTION CHANGE: payment.php pe redirect -->
                            <form method="post" action="payment.php" id="checkoutForm">
                                <div class="row">
                                    <input type="hidden" name="total_amount" value="<?php echo $total; ?>">
                                    <div class="col-md-6 mb-3 form-floating">
                                        <input type="text" name="name" class="form-control"
                                            value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>"
                                            placeholder=" " required>
                                        <label class="form-label">Full Name *</label>
                                    </div>
                                    <div class="col-md-6 mb-3 form-floating">
                                        <input type="email" name="email" class="form-control"
                                            value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>"
                                            placeholder=" " required>
                                        <label class="form-label">Email Address *</label>
                                    </div>
                                </div>
                                <div class="mb-3 form-floating">
                                    <input type="tel" name="phone" class="form-control"
                                        placeholder=" ">
                                    <label class="form-label">Phone Number</label>
                                </div>
                                <div class="mb-3 form-floating">
                                    <textarea name="address" class="form-control"
                                        placeholder=" " required style="height: 100px"></textarea>
                                    <label class="form-label">Shipping Address *</label>
                                    <small class="text-muted">Please include street address, city, state, and PIN code</small>
                                </div>

                                <div class="d-grid">
                                    <!-- BUTTON TEXT CHANGE: Proceed to Payment -->
                                    <button type="submit" name="proceed_to_payment" class="btn btn-success btn-lg">
                                        <i class="fas fa-credit-card me-2"></i>Proceed to Payment - ₹<?php echo number_format($total, 2); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
</body>

</html>