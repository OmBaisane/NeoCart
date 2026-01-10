<?php
require_once __DIR__ . '/../includes/header.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? intval($_GET['category']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build SQL query with filters
$sql = "SELECT * FROM products WHERE 1=1";

$params = [];
$types = '';

// Add search filter
if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

// Add price range filter
if (!empty($min_price)) {
    $sql .= " AND price >= ?";
    $params[] = $min_price;
    $types .= 'd';
}
if (!empty($max_price)) {
    $sql .= " AND price <= ?";
    $params[] = $max_price;
    $types .= 'd';
}

// Add sorting
switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY price DESC";
        break;
    case 'name':
        $sql .= " ORDER BY name ASC";
        break;
    default: // newest
        $sql .= " ORDER BY id DESC";
        break;
}

// Execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();
$total_products = $products->num_rows;
?>

<main class="container mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-primary">
                <i class="fas fa-boxes me-2"></i>All Products
            </h2>
            <p class="text-muted fs-5">Discover our amazing collection of products</p>
        </div>
    </div>

    <!-- Search and Filters Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form id="searchForm" method="GET" action="products.php">
                        <div class="row g-3 align-items-end">
                            <!-- Search Input -->
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Search Products</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white border-0">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control border-0" 
                                           placeholder="Search by product name or description..." 
                                           value="<?php echo htmlspecialchars($search); ?>"
                                           id="searchInput">
                                </div>
                            </div>

                            <!-- Price Range -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Price Range</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" name="min_price" class="form-control" 
                                               placeholder="Min Price" 
                                               value="<?php echo htmlspecialchars($min_price); ?>"
                                               min="0" step="0.01">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="max_price" class="form-control" 
                                               placeholder="Max Price" 
                                               value="<?php echo htmlspecialchars($max_price); ?>"
                                               min="0" step="0.01">
                                    </div>
                                </div>
                            </div>

                            <!-- Sort Options -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Sort By</label>
                                <select name="sort" class="form-select" id="sortSelect">
                                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                                </select>
                            </div>
                        </div>

                        <!-- Filter Button -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-filter me-1"></i> Apply Filters
                                    </button>
                                    <?php if ($search || $min_price || $max_price): ?>
                                        <a href="products.php" class="btn btn-outline-secondary px-4">
                                            <i class="fas fa-times me-1"></i>Clear All
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Info -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <p class="mb-0 text-muted fs-6">
                    <?php if ($search || $min_price || $max_price): ?>
                        Found <strong class="text-primary"><?php echo $total_products; ?></strong> product(s) 
                        <?php if ($search): ?>matching "<strong class="text-primary"><?php echo htmlspecialchars($search); ?></strong>"<?php endif; ?>
                    <?php else: ?>
                        Showing all <strong class="text-primary"><?php echo $total_products; ?></strong> products
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row g-4" id="productsGrid">
        <?php if ($total_products > 0): ?>
            <?php while ($product = $products->fetch_assoc()): 
                $img_path = '../assets/images/products/' . $product['image'];
                $short_description = strlen($product['description']) > 100 
                    ? substr($product['description'], 0, 100) . '...' 
                    : $product['description'];
                
                // Handle stock quantity field name
                $stock_quantity = $product['stock_quantity'] ?? $product['stock'] ?? 0;
                $max_quantity = min($stock_quantity, 10);
            ?>
            <div class="col-xl-3 col-lg-4 col-md-6 product-item">
                <div class="card product-card h-100 shadow-sm border-0">
                    <!-- Product Image - FIXED SIZE -->
                    <div class="product-image-container position-relative">
                        <a href="product-details.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
                            <div class="image-wrapper">
                                <img src="<?php echo $img_path; ?>" 
                                     class="card-img-top product-image" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     onerror="this.src='../assets/images/placeholder.jpg'">
                            </div>
                        </a>
                        <!-- Stock Badge -->
                        <?php if ($stock_quantity <= 0): ?>
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-danger fs-6">Out of Stock</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body d-flex flex-column p-3">
                        <!-- Product Name -->
                        <h6 class="card-title fw-bold mb-2">
                            <a href="product-details.php?id=<?php echo $product['id']; ?>" 
                               class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h6>
                        
                        <!-- Description - SIMPLIFIED (No Read More) -->
                        <div class="description-container mb-2">
                            <p class="card-text text-muted small flex-grow-1 description-text">
                                <?php echo htmlspecialchars($short_description); ?>
                            </p>
                        </div>
                        
                        <div class="mt-auto">
                            <!-- Price and Stock -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="h5 text-success fw-bold mb-0">₹<?php echo number_format($product['price'], 2); ?></span>
                                <?php if ($stock_quantity > 0): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success fs-6">
                                        <i class="fas fa-check me-1"></i>In Stock
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Add to Cart Form -->
                            <form action="add_to_cart.php" method="POST" class="d-flex gap-2">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="return_url" value="products.php">
                                
                                <!-- Quantity Controls -->
                                <?php if ($stock_quantity > 0): ?>
                                    <div class="flex-grow-1">
                                        <div class="input-group input-group-sm quantity-group">
                                            <button type="button" class="btn btn-outline-secondary quantity-minus border" 
                                                    data-product-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" name="quantity" value="1" min="1" 
                                                   max="<?php echo $max_quantity; ?>" 
                                                   class="form-control text-center quantity-input border"
                                                   id="quantity-<?php echo $product['id']; ?>">
                                            <button type="button" class="btn btn-outline-secondary quantity-plus border"
                                                    data-product-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Add to Cart Button -->
                                <button type="submit" name="add_to_cart" 
                                        class="btn btn-primary btn-sm flex-grow-1 add-to-cart-btn"
                                        <?php echo $stock_quantity == 0 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-cart-plus me-1"></i>
                                    <?php echo $stock_quantity == 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h4 class="text-muted fw-bold">No products found</h4>
                <p class="text-muted fs-5">
                    <?php if ($search || $min_price || $max_price): ?>
                        Try adjusting your search criteria or 
                        <a href="products.php" class="text-decoration-none fw-bold">clear filters</a>.
                    <?php else: ?>
                        No products available at the moment.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- JavaScript for Quantity Controls -->
<script>
// Quantity Increase/Decrease
document.addEventListener('DOMContentLoaded', function() {
    // Quantity minus button
    document.querySelectorAll('.quantity-minus').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const input = document.getElementById('quantity-' + productId);
            let value = parseInt(input.value);
            const min = parseInt(input.getAttribute('min'));
            
            if (value > min) {
                input.value = value - 1;
            }
        });
    });
    
    // Quantity plus button
    document.querySelectorAll('.quantity-plus').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const input = document.getElementById('quantity-' + productId);
            let value = parseInt(input.value);
            const max = parseInt(input.getAttribute('max'));
            
            if (value < max) {
                input.value = value + 1;
            } else {
                showToast(`Maximum quantity is ${max}`, 'error');
            }
        });
    });
    
    // Quantity input validation
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const max = parseInt(this.getAttribute('max'));
            const min = parseInt(this.getAttribute('min'));
            let value = parseInt(this.value);
            
            if (isNaN(value) || value < min) {
                this.value = min;
            } else if (value > max) {
                this.value = max;
                showToast(`Maximum quantity is ${max}`, 'error');
            }
        });
        
        input.addEventListener('input', function() {
            // Allow only numbers
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
});

// Toast notification function
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.custom-toast');
    existingToasts.forEach(toast => toast.remove());
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `custom-toast alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        <strong>${type === 'error' ? 'Error' : 'Success'}!</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 3000);
}

// Add to cart form handling
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const addToCartBtn = this.querySelector('.add-to-cart-btn');
            if (addToCartBtn && addToCartBtn.disabled) {
                e.preventDefault();
                showToast('This product is out of stock', 'error');
            }
        });
    });
});
</script>

<style>
/* Product Image Fix - Consistent Size */
.product-image-container {
    overflow: hidden;
    border-radius: 10px 10px 0 0;
}

.image-wrapper {
    height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    padding: 20px;
}

.product-image {
    height: 100%;
    width: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.product-image:hover {
    transform: scale(1.05);
}

/* Product Card Styling */
.product-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    border-radius: 15px;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    border-color: #007bff;
}

/* Quantity Controls */
.quantity-group {
    width: 120px;
}

.quantity-group .btn {
    width: 35px;
    padding: 0.3rem 0.5rem;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
}

.quantity-group .btn:hover {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.quantity-input {
    text-align: center;
    border-left: none;
    border-right: none;
    background: white;
    font-weight: 600;
}

/* Button Styling */
.add-to-cart-btn {
    padding: 0.5rem 0.75rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.add-to-cart-btn:hover:not(:disabled) {
    background: #0056b3;
    transform: translateY(-1px);
}

/* Description Styling */
.description-text {
    line-height: 1.5;
    min-height: 48px;
}

/* Toast Styling */
.custom-toast {
    animation: slideInRight 0.3s ease;
    border-radius: 10px;
    border: none;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Badge Styling */
.badge {
    font-size: 0.7rem;
    padding: 0.4em 0.6em;
}

/* Responsive Design */
@media (max-width: 768px) {
    .image-wrapper {
        height: 200px;
        padding: 15px;
    }
    
    .product-card {
        margin-bottom: 1rem;
    }
    
    .quantity-group {
        width: 100px;
    }
}

@media (max-width: 576px) {
    .image-wrapper {
        height: 180px;
        padding: 10px;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>