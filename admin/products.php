<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/includes/header.php';

// FIXED: Dashboard ke saath consistent session check
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit();
}

// Success/Error messages display
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>' . $_SESSION['success'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>' . $_SESSION['error'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['error']);
}

// Get all products with error handling
try {
    $products = $conn->query("SELECT * FROM products ORDER BY id DESC");
    $total_products = $products->num_rows;
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error loading products: ' . $e->getMessage() . '</div>';
    $total_products = 0;
}
?>

<style>
    .product-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .product-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        transition: transform 0.3s ease;
    }

    .product-image:hover {
        transform: scale(1.1);
    }

    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .action-btn {
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .action-btn:hover {
        transform: translateY(-2px);
    }
</style>

<div class="container mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 text-primary">
                <i class="fas fa-boxes me-2"></i>Manage Products
            </h2>
            <p class="text-muted mb-0">Total <?php echo $total_products; ?> products in store</p>
        </div>
        <div>
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm me-2">
                <i class="fas fa-arrow-left me-1"></i>Dashboard
            </a>
            <a href="add_products.php" class="btn btn-success btn-sm">
                <i class="fas fa-plus me-1"></i>Add Product
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body text-center py-4">
                    <i class="fas fa-box fa-2x mb-2 opacity-75"></i>
                    <h3 class="mb-0"><?php echo $total_products; ?></h3>
                    <small>Total Products</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card product-card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Products List</h5>
            <span class="badge bg-light text-dark"><?php echo $total_products; ?> products</span>
        </div>
        <div class="card-body p-0">
            <?php if ($total_products > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3">ID</th>
                                <th class="py-3">Image</th>
                                <th class="py-3">Product Details</th>
                                <th class="py-3">Price</th>
                                <th class="py-3">Stock</th>
                                <th class="py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $products->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold text-primary">#<?php echo $product['id']; ?></td>
                                    <td>
                                        <?php if (!empty($product['image']) && file_exists('../assets/images/products/' . $product['image'])): ?>
                                            <img src="../assets/images/products/<?php echo $product['image']; ?>"
                                                class="product-image"
                                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                onerror="this.src='../assets/images/placeholder.jpg'">
                                        <?php else: ?>
                                            <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                                <i class="fas fa-cube text-muted fa-lg"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <h6 class="mb-1 text-dark"><?php echo htmlspecialchars($product['name']); ?></h6>
                                        <p class="mb-0 text-muted small">
                                            <?php
                                            $description = htmlspecialchars($product['description']);
                                            echo strlen($description) > 60 ? substr($description, 0, 60) . '...' : $description;
                                            ?>
                                        </p>
                                    </td>
                                    <td>
                                        <h5 class="mb-0 text-success">₹<?php echo number_format($product['price'], 2); ?></h5>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo (($product['stock_quantity'] ?? $product['stock'] ?? 0) > 0) ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php
                                            $stock = $product['stock_quantity'] ?? $product['stock'] ?? 0;
                                            echo ($stock > 0) ? $stock . ' in stock' : 'Out of stock';
                                            ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit_products.php?id=<?php echo $product['id']; ?>"
                                                class="btn btn-outline-warning action-btn"
                                                title="Edit Product">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                            <a href="delete_products.php?id=<?php echo $product['id']; ?>"
                                                class="btn btn-outline-danger action-btn"
                                                onclick="return confirm('Are you sure you want to delete \\'<?php echo htmlspecialchars(addslashes($product['name'])); ?>\\'? This action cannot be undone.')"
                                                title="Delete Product">
                                                <i class="fas fa-trash me-1"></i>Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted mb-3">No Products Found</h4>
                    <p class="text-muted mb-4">Start by adding your first product to the store.</p>
                    <a href="add_products.php" class="btn btn-success btn-lg">
                        <i class="fas fa-plus me-2"></i>Add Your First Product
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card product-card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="add_products.php" class="btn btn-outline-success">
                            <i class="fas fa-plus me-2"></i>Add New Product
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-primary">
                            <i class="fas fa-tachometer-alt me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card product-card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Use clear product images</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Write detailed descriptions</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Set competitive pricing</li>
                        <li class="mb-0"><i class="fas fa-check text-success me-2"></i>Update stock regularly</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>