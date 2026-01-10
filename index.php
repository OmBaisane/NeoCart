<?php 
require_once 'config/db.php';

// Get featured products
$stmt = $conn->prepare("SELECT * FROM products ORDER BY RAND() LIMIT 4");
$stmt->execute();
$featured_products = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeoCart - Modern Shopping Experience</title>
    <link rel="icon" href="assets/images/Logo.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/animate.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container-fluid px-0">
        <!-- Hero Section - DIFFERENT BACKGROUND COLOR -->
        <section class="hero-section py-5 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="container">
                <div class="row align-items-center justify-content-center min-vh-50">
                    <div class="col-lg-8 text-center animate__animated animate__fadeIn">
                        <h1 class="display-4 fw-bold magictime spaceInLeft mb-4">Welcome to NeoCart</h1>
                        <p class="lead mb-4 mx-auto" style="max-width: 600px;">
                            Discover amazing products at unbeatable prices. Your modern shopping destination built with PHP & MySQL - Simple, Smart & Scalable.
                        </p>
                        <div class="hero-buttons">
                            <a href="pages/products.php" class="btn btn-light btn-lg me-3 magictime puffIn">
                                <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                            </a>
                            <a href="#featured" class="btn btn-outline-light btn-lg magictime puffIn">
                                <i class="fas fa-star me-2"></i>Featured Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-5 bg-light">
            <div class="container">
                <div class="row text-center">
                    <div class="col-md-4 mb-4">
                        <div class="feature-card p-4 animate__animated animate__fadeInUp">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-shipping-fast fa-3x text-primary"></i>
                            </div>
                            <h4 class="fw-bold">Free Shipping</h4>
                            <p class="text-muted">Free delivery on orders above ₹499. Fast and reliable shipping across India.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="feature-card p-4 animate__animated animate__fadeInUp">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-shield-alt fa-3x text-success"></i>
                            </div>
                            <h4 class="fw-bold">Secure Payment</h4>
                            <p class="text-muted">100% secure payment processing with SSL encryption. Your data is safe with us.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="feature-card p-4 animate__animated animate__fadeInUp">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-headset fa-3x text-info"></i>
                            </div>
                            <h4 class="fw-bold">24/7 Support</h4>
                            <p class="text-muted">Round the clock customer support. We're here to help you anytime.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section id="featured" class="py-5">
            <div class="container">
                <div class="row mb-5">
                    <div class="col-12 text-center">
                        <h2 class="fw-bold mb-3 animate__animated animate__fadeIn">Featured Products</h2>
                        <p class="lead text-muted animate__animated animate__fadeIn">Check out our handpicked collection</p>
                    </div>
                </div>

                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                    <?php while ($product = $featured_products->fetch_assoc()): 
                        $img_path = 'assets/images/products/' . $product['image'];
                        $short_description = strlen($product['description']) > 80 
                            ? substr($product['description'], 0, 80) . '...' 
                            : $product['description'];
                    ?>
                    <div class="col d-flex">
                        <div class="card product-card w-100 h-100 animate__animated animate__fadeInUp">
                            <!-- Fixed Height Image -->
                            <div class="product-image-container">
                                <img src="<?php echo $img_path; ?>" 
                                     class="card-img-top product-image" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     onerror="this.src='assets/images/placeholder.jpg'">
                                <div class="product-overlay">
                                    <?php if(isset($_SESSION['logged_in'])): ?>
                                        <form action="pages/add_to_cart.php" method="POST" class="w-100 text-center">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <input type="hidden" name="return_url" value="../index.php">
                                            <button type="submit" name="add_to_cart" class="btn btn-success btn-sm">
                                                <i class="fas fa-cart-plus me-1"></i>Add to Cart
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="pages/login.php" class="btn btn-outline-light btn-sm">
                                            <i class="fas fa-sign-in-alt me-1"></i>Login to Buy
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Card Body -->
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text text-muted small flex-grow-1"><?php echo htmlspecialchars($short_description); ?></p>
                                
                                <!-- Bottom Section -->
                                <div class="mt-auto pt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h5 text-primary fw-bold mb-0">₹<?php echo number_format($product['price'], 2); ?></span>
                                        <a href="pages/products.php" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <div class="text-center mt-5">
                    <a href="pages/products.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-grid me-2"></i>View All Products
                    </a>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-5 bg-dark text-white">
            <div class="container">
                <div class="row text-center">
                    <div class="col-md-3 mb-4">
                        <div class="stat-item animate__animated animate__fadeIn">
                            <h3 class="display-4 fw-bold text-primary">500+</h3>
                            <p class="mb-0">Happy Customers</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="stat-item animate__animated animate__fadeIn">
                            <h3 class="display-4 fw-bold text-success">1000+</h3>
                            <p class="mb-0">Products Sold</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="stat-item animate__animated animate__fadeIn">
                            <h3 class="display-4 fw-bold text-info">50+</h3>
                            <p class="mb-0">Brand Partners</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="stat-item animate__animated animate__fadeIn">
                            <h3 class="display-4 fw-bold text-warning">24/7</h3>
                            <p class="mb-0">Customer Support</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <?php $conn->close(); ?>
</body>
</html>