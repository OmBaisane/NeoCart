<?php
// pages/cart.php - HEADER ERROR FIXED

// Start session at VERY TOP with no spaces
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect BEFORE any output
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Now include other files
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';

$user_id = $_SESSION['user_id'];

// Get cart items with product details
$stmt = $conn->prepare("SELECT c.id as cart_id, p.id as product_id, p.name, p.price, p.image, p.description, c.quantity
    FROM cart c 
    JOIN products p ON p.id = c.product_id
    WHERE c.cart_user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

// Calculate total
$total_amount = 0;
while($item = $cart_items->fetch_assoc()) {
    $total_amount += $item['price'] * $item['quantity'];
}
?>

<main class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4 animate__animated animate__fadeIn">
                <i class="fas fa-shopping-cart me-2"></i>My Shopping Cart
            </h2>
        </div>
    </div>

    <?php if ($cart_items->num_rows > 0): ?>
        <div class="row">
            <div class="col-lg-8">
                <!-- Cart Items -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Cart Items (<?php echo $cart_items->num_rows; ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php 
                        $cart_items->data_seek(0);
                        while ($row = $cart_items->fetch_assoc()): 
                            $subtotal = $row['price'] * $row['quantity'];
                        ?>
                        <div class="cart-item p-3 border-bottom animate__animated animate__fadeIn" data-cart-id="<?php echo $row['cart_id']; ?>">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img src="../assets/images/products/<?php echo $row['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($row['name']); ?>"
                                         class="img-fluid rounded" style="height: 80px; object-fit: cover;">
                                </div>
                                <div class="col-md-4">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($row['name']); ?></h6>
                                    <small class="text-muted"><?php echo substr($row['description'], 0, 60); ?>...</small>
                                </div>
                                <div class="col-md-2 text-center">
                                    <strong class="text-primary price" data-price="<?php echo $row['price']; ?>">
                                        ₹<?php echo number_format($row['price'], 2); ?>
                                    </strong>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group input-group-sm quantity-group" style="width: 120px;">
                                        <button type="button" class="btn btn-outline-secondary quantity-minus">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" class="form-control text-center qty-input" 
                                               min="1" max="10" value="<?php echo $row['quantity']; ?>">
                                        <button type="button" class="btn btn-outline-secondary quantity-plus">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <strong class="subtotal">₹<?php echo number_format($subtotal, 2); ?></strong>
                                    <button class="btn btn-sm btn-outline-danger remove-btn mt-1" title="Remove item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong id="subtotal-display">₹<?php echo number_format($total_amount, 2); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <strong>₹0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <strong>₹0.00</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="h5">Total:</span>
                            <strong class="h5 text-primary" id="grand-total">₹<?php echo number_format($total_amount, 2); ?></strong>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-success w-100 btn-lg">
                            <i class="fas fa-lock me-2"></i>Proceed to Checkout
                        </a>
                        
                        <div class="text-center mt-3">
                            <a href="products.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Empty Cart -->
        <div class="text-center py-5" id="empty-cart-message">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Your cart is empty</h4>
            <p class="text-muted mb-4">Add some amazing products to your cart!</p>
            <a href="products.php" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag me-2"></i>Start Shopping
            </a>
        </div>
    <?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Safe element selector function
    function getElement(selector) {
        const element = document.querySelector(selector);
        if (!element) {
            console.warn('Element not found:', selector);
        }
        return element;
    }

    // Update totals function
    function updateTotals() {
        let subtotal = 0;
        const cartItems = document.querySelectorAll('.cart-item');
        
        cartItems.forEach(item => {
            const priceElement = item.querySelector('.price');
            const qtyInput = item.querySelector('.qty-input');
            const subtotalElement = item.querySelector('.subtotal');
            
            if (priceElement && qtyInput && subtotalElement) {
                const price = parseFloat(priceElement.dataset.price) || 0;
                const quantity = parseInt(qtyInput.value) || 0;
                const itemSubtotal = price * quantity;
                
                subtotalElement.textContent = '₹' + itemSubtotal.toFixed(2);
                subtotal += itemSubtotal;
            }
        });
        
        const subtotalDisplay = getElement('#subtotal-display');
        const grandTotal = getElement('#grand-total');
        
        if (subtotalDisplay) subtotalDisplay.textContent = '₹' + subtotal.toFixed(2);
        if (grandTotal) grandTotal.textContent = '₹' + subtotal.toFixed(2);
    }

    // Initialize totals
    const cartItems = document.querySelectorAll('.cart-item');
    if (cartItems.length > 0) {
        updateTotals();
    }

    // Quantity buttons functionality
    document.querySelectorAll('.quantity-minus').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.quantity-group').querySelector('.qty-input');
            let value = parseInt(input.value) || 1;
            if (value > 1) {
                input.value = value - 1;
                input.dispatchEvent(new Event('change'));
            }
        });
    });

    document.querySelectorAll('.quantity-plus').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.quantity-group').querySelector('.qty-input');
            let value = parseInt(input.value) || 1;
            const max = parseInt(input.getAttribute('max')) || 10;
            if (value < max) {
                input.value = value + 1;
                input.dispatchEvent(new Event('change'));
            } else {
                alert('Maximum quantity is ' + max);
            }
        });
    });

    // Quantity change event
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('change', function() {
            const cartItem = this.closest('.cart-item');
            if (!cartItem) return;
            
            const cartId = cartItem.dataset.cartId;
            const quantity = parseInt(this.value) || 1;
            
            if (quantity < 1) {
                this.value = 1;
                return;
            }

            if (quantity > 10) {
                this.value = 10;
                alert('Maximum quantity is 10 per product');
                return;
            }

            // AJAX call to update quantity
            fetch('../ajax/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&cart_id=${cartId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateTotals();
                    if (data.cart_count !== undefined) {
                        const cartCount = document.querySelector('#cartCount');
                        if (cartCount) cartCount.textContent = data.cart_count;
                    }
                } else {
                    alert('Failed to update quantity: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error occurred');
            });
        });
    });

    // Remove item event
    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }

            const cartItem = this.closest('.cart-item');
            if (!cartItem) return;
            
            const cartId = cartItem.dataset.cartId;

            fetch('../ajax/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=remove&cart_id=${cartId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cartItem.style.opacity = '0';
                    setTimeout(() => {
                        cartItem.remove();
                        updateTotals();
                        
                        if (data.cart_count !== undefined) {
                            const cartCount = document.querySelector('#cartCount');
                            if (cartCount) cartCount.textContent = data.cart_count;
                        }
                        
                        const remainingItems = document.querySelectorAll('.cart-item');
                        if (remainingItems.length === 0) {
                            location.reload();
                        }
                    }, 300);
                } else {
                    alert('Failed to remove item: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error occurred');
            });
        });
    });
});
</script>

<style>
.quantity-group {
    width: 120px;
}

.quantity-group .btn {
    width: 30px;
    padding: 0.25rem 0.5rem;
}

.qty-input {
    text-align: center;
    border-left: none;
    border-right: none;
}

.cart-item {
    transition: all 0.3s ease;
}

.cart-item:hover {
    background-color: #f8f9fa;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>