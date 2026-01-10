<?php
// pages/payment.php - FIXED VERSION

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

// This page expects POST data from checkout.php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit();
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$phone = trim($_POST['phone'] ?? '');

// Recalculate cart total from database
$stmt = $conn->prepare("SELECT SUM(p.price * c.quantity) as total 
                       FROM cart c 
                       JOIN products p ON p.id = c.product_id 
                       WHERE c.cart_user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total = $result['total'] ? (float)$result['total'] : 0.0;
$stmt->close();

if ($total <= 0) {
    $_SESSION['error'] = "Your cart is empty. Please add products first.";
    header('Location: products.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - NeoCart</title>
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
                    <i class="fas fa-credit-card me-2"></i>Payment
                </h2>
                <p class="text-muted">Complete your purchase securely</p>
            </div>
        </div>

        <div class="row">
            <!-- Payment Form -->
            <div class="col-lg-8 mb-4">
                <div class="card animate__animated animate__fadeInLeft">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <!-- FORM ACTION CHANGED to process_order.php -->
                        <form method="post" action="process_order.php" id="paymentForm">
                            <!-- Hidden fields to carry checkout data -->
                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($name); ?>">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                            <input type="hidden" name="address" value="<?php echo htmlspecialchars($address); ?>">
                            <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                            <input type="hidden" name="total_amount" value="<?php echo $total; ?>">
                            
                            <div class="mb-4">
                                <label class="form-label h6">Select Payment Method</label>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="card payment-option">
                                            <div class="card-body text-center">
                                                <input class="form-check-input" type="radio" name="payment_method" 
                                                       id="pm_card" value="card" required>
                                                <label class="form-check-label d-block" for="pm_card">
                                                    <i class="fas fa-credit-card fa-2x text-primary mb-2"></i><br>
                                                    Credit/Debit Card
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card payment-option">
                                            <div class="card-body text-center">
                                                <input class="form-check-input" type="radio" name="payment_method" 
                                                       id="pm_upi" value="upi">
                                                <label class="form-check-label d-block" for="pm_upi">
                                                    <i class="fas fa-mobile-alt fa-2x text-success mb-2"></i><br>
                                                    UPI Payment
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card payment-option">
                                            <div class="card-body text-center">
                                                <input class="form-check-input" type="radio" name="payment_method" 
                                                       id="pm_cod" value="cod" checked>
                                                <label class="form-check-label d-block" for="pm_cod">
                                                    <i class="fas fa-money-bill-wave fa-2x text-warning mb-2"></i><br>
                                                    Cash on Delivery
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Payment Details -->
                            <div id="cardDetails" class="payment-details" style="display:none;">
                                <div class="border rounded p-4 bg-light">
                                    <h6 class="mb-3"><i class="fas fa-credit-card me-2"></i>Card Details</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Card Number</label>
                                        <input type="text" name="card_number" class="form-control" 
                                               placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="cc-number">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Expiry Date</label>
                                            <input type="text" name="expiry" class="form-control" 
                                                   placeholder="MM/YY" maxlength="5" autocomplete="cc-exp">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">CVV</label>
                                            <input type="password" name="cvv" class="form-control" 
                                                    placeholder="123" maxlength="3" autocomplete="cc-csc">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Cardholder Name</label>
                                        <input type="text" name="card_name" class="form-control" 
                                               placeholder="John Doe" autocomplete="cc-name">
                                    </div>
                                </div>
                            </div>

                            <!-- UPI Payment Details -->
                            <div id="upiDetails" class="payment-details" style="display:none;">
                                <div class="border rounded p-4 bg-light">
                                    <h6 class="mb-3"><i class="fas fa-mobile-alt me-2"></i>UPI Details</h6>
                                    <div class="mb-3">
                                        <label class="form-label">UPI ID</label>
                                        <input type="text" name="upi_id" class="form-control" 
                                               placeholder="yourname@upi" autocomplete="off">
                                    </div>
                                    <div class="alert alert-info">
                                        <small>
                                            <i class="fas fa-info-circle me-2"></i>
                                            You will be redirected to your UPI app for payment confirmation.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- COD Message -->
                            <div id="codDetails" class="payment-details">
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Pay when your order is delivered. Additional charges may apply.
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-lock me-2"></i>
                                    Complete Order - ₹<?php echo number_format($total, 2); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card animate__animated animate__fadeInRight">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Shipping Information</h6>
                            <p class="mb-1"><strong><?php echo htmlspecialchars($name); ?></strong></p>
                            <p class="mb-1 text-muted small"><?php echo htmlspecialchars($email); ?></p>
                            <?php if (!empty($phone)): ?>
                                <p class="mb-1 text-muted small"><?php echo htmlspecialchars($phone); ?></p>
                            <?php endif; ?>
                            <p class="text-muted small"><?php echo nl2br(htmlspecialchars($address)); ?></p>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <h6>Amount Details</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>₹<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span>₹0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax:</span>
                                <span>₹0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total Amount:</strong>
                                <strong class="h5 text-success">₹<?php echo number_format($total, 2); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="checkout.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Checkout
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
        const cardDetails = document.getElementById('cardDetails');
        const upiDetails = document.getElementById('upiDetails');
        const codDetails = document.getElementById('codDetails');

        function showPaymentDetails() {
            // Hide all first
            cardDetails.style.display = 'none';
            upiDetails.style.display = 'none';
            codDetails.style.display = 'none';

            // Show selected
            const selected = document.querySelector('input[name="payment_method"]:checked').value;
            if (selected === 'card') {
                cardDetails.style.display = 'block';
            } else if (selected === 'upi') {
                upiDetails.style.display = 'block';
            } else {
                codDetails.style.display = 'block';
            }
        }

        // Add event listeners
        paymentOptions.forEach(radio => {
            radio.addEventListener('change', showPaymentDetails);
        });

        // Initialize
        showPaymentDetails();

        // Card number formatting
        const cardNumber = document.querySelector('input[name="card_number"]');
        if (cardNumber) {
            cardNumber.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                let formatted = value.match(/.{1,4}/g)?.join(' ') || '';
                e.target.value = formatted;
            });
        }

        // Expiry date formatting
        const expiry = document.querySelector('input[name="expiry"]');
        if (expiry) {
            expiry.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\//g, '').replace(/[^0-9]/gi, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                e.target.value = value;
            });
        }
    });
    </script>

    <style>
    .payment-option {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .payment-option:hover {
        border-color: #007bff;
        transform: translateY(-2px);
    }
    .payment-option .form-check-input {
        position: absolute;
        top: 10px;
        left: 10px;
    }
    .payment-details {
        transition: all 0.3s ease;
    }
    </style>

    <?php include '../includes/footer.php'; ?>
</body>
</html>