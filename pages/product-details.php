<?php
// pages/product-details.php
session_start();
require_once __DIR__ . '/../includes/header.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = (int)$_GET['id'];

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo '<div class="container mt-4"><div class="alert alert-danger">Product not found!</div></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit();
}

// Get product reviews with user names
$reviews_stmt = $conn->prepare("
    SELECT pr.*, u.name as user_name 
    FROM product_reviews pr 
    LEFT JOIN users u ON u.id = pr.user_id 
    WHERE pr.product_id = ? 
    ORDER BY pr.created_at DESC
");
$reviews_stmt->bind_param("i", $product_id);
$reviews_stmt->execute();
$reviews = $reviews_stmt->get_result();

// Calculate average rating
$rating_stmt = $conn->prepare("
    SELECT 
        AVG(rating) as avg_rating,
        COUNT(*) as total_reviews,
        COUNT(CASE WHEN rating = 5 THEN 1 END) as rating_5,
        COUNT(CASE WHEN rating = 4 THEN 1 END) as rating_4,
        COUNT(CASE WHEN rating = 3 THEN 1 END) as rating_3,
        COUNT(CASE WHEN rating = 2 THEN 1 END) as rating_2,
        COUNT(CASE WHEN rating = 1 THEN 1 END) as rating_1
    FROM product_reviews 
    WHERE product_id = ?
");
$rating_stmt->bind_param("i", $product_id);
$rating_stmt->execute();
$rating_stats = $rating_stmt->get_result()->fetch_assoc();

$avg_rating = $rating_stats['avg_rating'] ? round($rating_stats['avg_rating'], 1) : 0;
$total_reviews = $rating_stats['total_reviews'] ?? 0;

// Use both stock_quantity and stock columns for compatibility
$stock_quantity = $product['stock_quantity'] ?? $product['stock'] ?? 0;

// Check if user already reviewed this product (for edit feature)
$user_review = null;
if (isset($_SESSION['user_id'])) {
    $user_review_stmt = $conn->prepare("SELECT * FROM product_reviews WHERE user_id = ? AND product_id = ?");
    $user_review_stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    $user_review_stmt->execute();
    $user_review = $user_review_stmt->get_result()->fetch_assoc();
}

$is_edit_mode = $user_review ? true : false;
$existing_rating = $user_review['rating'] ?? 0;
$existing_review_text = $user_review['review_text'] ?? '';
?>

<div class="container mt-4">
    <!-- Product Details -->
    <div class="row">
        <!-- Product Image Section - FIXED -->
        <div class="col-md-6">
            <div class="product-image-container text-center">
                <div class="image-wrapper" style="height: 220px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border-radius: 10px; padding: 20px; border: 1px solid #dee2e6;">
                    <img src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                        class="img-fluid"
                        style="max-height: 100%; max-width: 100%; object-fit: contain;"
                        onerror="this.src='../assets/images/placeholder.jpg'">
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>

            <!-- Rating Display -->
            <div class="product-rating mb-3">
                <div class="d-flex align-items-center">
                    <div class="star-rating me-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= round($avg_rating) ? 'text-warning' : 'text-muted'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="text-muted">(<?php echo $avg_rating; ?> • <?php echo $total_reviews; ?> reviews)</span>
                </div>
            </div>

            <p class="product-description text-muted mb-3">
                <?php echo htmlspecialchars($product['description']); ?>
            </p>

            <div class="product-price mb-4">
                <h3 class="text-success">₹<?php echo number_format($product['price'], 2); ?></h3>
                <div class="d-flex align-items-center gap-3">
                    <?php if ($stock_quantity > 0): ?>
                        <span class="badge bg-success">In Stock (<?php echo $stock_quantity; ?> available)</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Out of Stock</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Add to Cart Form -->
            <form action="add_to_cart.php" method="POST" class="mb-4">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="return_url" value="product-details.php?id=<?php echo $product_id; ?>">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label for="quantity" class="form-label fw-bold">Quantity:</label>
                    </div>
                    <div class="col-auto">
                        <div class="input-group input-group-sm quantity-group" style="width: 120px;">
                            <button type="button" class="btn btn-outline-secondary quantity-minus">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" name="quantity" id="quantity"
                                class="form-control text-center" value="1" min="1"
                                max="<?php echo min($stock_quantity, 10); ?>"
                                <?php echo $stock_quantity <= 0 ? 'disabled' : ''; ?>>
                            <button type="button" class="btn btn-outline-secondary quantity-plus">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-lg"
                            <?php echo $stock_quantity <= 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart me-2"></i>
                            <?php echo $stock_quantity <= 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reviews Section - COMPLETELY FIXED WHITE LINES -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card neocart-fixed-card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Customer Reviews
                        <?php if ($total_reviews > 0): ?>
                            <span class="badge bg-light text-dark ms-2"><?php echo $total_reviews; ?> reviews</span>
                        <?php endif; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($total_reviews > 0): ?>
                        <!-- Rating Summary -->
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <div class="display-4 fw-bold text-primary"><?php echo $avg_rating; ?></div>
                                <div class="star-rating mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= round($avg_rating) ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <small class="text-muted">Based on <?php echo $total_reviews; ?> reviews</small>
                            </div>
                            <div class="col-md-8">
                                <?php for ($i = 5; $i >= 1; $i--):
                                    $count = $rating_stats["rating_$i"] ?? 0;
                                    $percentage = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
                                ?>
                                    <div class="row align-items-center mb-2">
                                        <div class="col-2">
                                            <small class="text-muted"><?php echo $i; ?> <i class="fas fa-star text-warning"></i></small>
                                        </div>
                                        <div class="col-8">
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-warning"
                                                    style="width: <?php echo $percentage; ?>%"></div>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <small class="text-muted"><?php echo $count; ?></small>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Add Review Form - WITH EDIT FEATURE -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="add-review-section mb-4 p-4 border rounded bg-light">
                            <h5>
                                <?php echo $is_edit_mode ? '✏️ Edit Your Review' : '📝 Write a Review'; ?>
                                <?php if ($is_edit_mode): ?>
                                    <span class="badge bg-warning text-dark ms-2">Editing</span>
                                <?php endif; ?>
                            </h5>
                            <form id="reviewForm">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Your Rating</label>
                                    <div class="star-rating-input mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star star-icon <?php echo $i <= $existing_rating ? 'text-warning' : 'text-muted'; ?>"
                                                data-rating="<?php echo $i; ?>"
                                                style="cursor: pointer; font-size: 1.5rem; margin-right: 5px;"></i>
                                        <?php endfor; ?>
                                        <input type="hidden" name="rating" id="selectedRating" value="<?php echo $existing_rating; ?>" required>
                                    </div>
                                    <small class="text-muted" id="ratingText">
                                        <?php echo $existing_rating ? 'Your rating: ' . $existing_rating . ' star' . ($existing_rating > 1 ? 's' : '') : 'Click stars to rate'; ?>
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="review_text" class="form-label fw-bold">Your Review</label>
                                    <textarea name="review_text" id="review_text" class="form-control"
                                        rows="4" placeholder="Share your experience with this product..."
                                        minlength="10" required><?php echo htmlspecialchars($existing_review_text); ?></textarea>
                                    <div class="form-text">Minimum 10 characters required</div>
                                </div>

                                <button type="submit" class="btn btn-success" id="submitReviewBtn" <?php echo $existing_rating ? '' : 'disabled'; ?>>
                                    <i class="fas fa-<?php echo $is_edit_mode ? 'edit' : 'paper-plane'; ?> me-2"></i>
                                    <?php echo $is_edit_mode ? 'Update Review' : 'Submit Review'; ?>
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>
                            Please <a href="login.php" class="alert-link">login</a> to write a review.
                        </div>
                    <?php endif; ?>

                    <!-- Reviews List - WHITE LINES FIXED -->
                    <div id="reviewsList">
                        <?php if ($reviews->num_rows > 0): ?>
                            <?php while ($review = $reviews->fetch_assoc()): ?>
                                <div class="review-item pb-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <?php echo htmlspecialchars($review['user_name']); ?>
                                                <?php if ($review['user_id'] == ($_SESSION['user_id'] ?? 0)): ?>
                                                    <span class="badge bg-info ms-1">You</span>
                                                <?php endif; ?>
                                            </h6>
                                            <div class="star-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>" style="font-size: 0.8rem;"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <?php
                                            // Show updated_at if available, otherwise created_at
                                            $display_date = $review['updated_at'] ? $review['updated_at'] : $review['created_at'];
                                            echo date('M j, Y', strtotime($display_date));
                                            if ($review['updated_at'] && $review['updated_at'] != $review['created_at']): ?>
                                                <br><small class="text-info">(Edited)</small>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Reviews Yet</h5>
                                <p class="text-muted">Be the first to review this product!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Review System -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentRating = <?php echo $existing_rating; ?>;
        const starIcons = document.querySelectorAll('.star-icon');
        const ratingText = document.getElementById('ratingText');
        const selectedRating = document.getElementById('selectedRating');
        const reviewText = document.getElementById('review_text');
        const submitBtn = document.getElementById('submitReviewBtn');

        // Initialize stars based on existing rating
        if (currentRating > 0) {
            highlightStars(currentRating);
            validateForm();
        }

        // Star rating selection
        starIcons.forEach(star => {
            star.addEventListener('mouseenter', function() {
                const rating = this.getAttribute('data-rating');
                highlightStars(rating);
                ratingText.textContent = 'Rating: ' + rating + ' star' + (rating > 1 ? 's' : '');
            });

            star.addEventListener('click', function() {
                currentRating = parseInt(this.getAttribute('data-rating'));
                selectedRating.value = currentRating;
                highlightStars(currentRating);
                ratingText.textContent = 'Your rating: ' + currentRating + ' star' + (currentRating > 1 ? 's' : '');
                validateForm();
            });
        });

        // Mouse leave for star container
        document.querySelector('.star-rating-input').addEventListener('mouseleave', function() {
            highlightStars(currentRating);
            if (currentRating > 0) {
                ratingText.textContent = 'Your rating: ' + currentRating + ' star' + (currentRating > 1 ? 's' : '');
            } else {
                ratingText.textContent = 'Click stars to rate';
            }
        });

        function highlightStars(rating) {
            starIcons.forEach(star => {
                const starRating = parseInt(star.getAttribute('data-rating'));
                if (starRating <= rating) {
                    star.classList.add('text-warning');
                    star.classList.remove('text-muted');
                } else {
                    star.classList.remove('text-warning');
                    star.classList.add('text-muted');
                }
            });
        }

        // Review text validation
        reviewText.addEventListener('input', function() {
            validateForm();

            if (this.value.trim().length > 0 && this.value.trim().length < 10) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

        // Form validation function
        function validateForm() {
            const isRatingValid = currentRating > 0;
            const isReviewValid = reviewText.value.trim().length >= 10;
            submitBtn.disabled = !(isRatingValid && isReviewValid);
        }

        // Submit review form - WITH EDIT FEATURE
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const rating = selectedRating.value;
            const reviewTextValue = reviewText.value.trim();

            // Validation
            if (!rating || rating < 1 || rating > 5) {
                alert('Please select a rating between 1-5 stars');
                return;
            }

            if (reviewTextValue.length < 10) {
                alert('Review must be at least 10 characters long');
                return;
            }

            const submitBtn = document.getElementById('submitReviewBtn');
            const originalText = submitBtn.innerHTML;

            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>' +
                (currentRating ? 'Updating...' : 'Submitting...');
            submitBtn.disabled = true;

            const formData = new FormData(this);

            fetch('../ajax/reviews.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload(); // Refresh to show updated review
                    } else {
                        alert('Error: ' + data.message);
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Server error occurred. Please try again.');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        });

        // Quantity controls - SAFE VERSION WITH NULL CHECKS
        const minusButtons = document.querySelectorAll('.quantity-minus');
        const plusButtons = document.querySelectorAll('.quantity-plus');

        if (minusButtons.length > 0) {
            minusButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const quantityGroup = this.closest('.quantity-group');
                    if (quantityGroup) {
                        const input = quantityGroup.querySelector('input');
                        if (input) {
                            let value = parseInt(input.value) || 1;
                            if (value > 1) {
                                input.value = value - 1;
                            }
                        }
                    }
                });
            });
        }

        if (plusButtons.length > 0) {
            plusButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const quantityGroup = this.closest('.quantity-group');
                    if (quantityGroup) {
                        const input = quantityGroup.querySelector('input');
                        if (input) {
                            let value = parseInt(input.value) || 1;
                            const max = parseInt(input.getAttribute('max')) || 10;
                            if (value < max) {
                                input.value = value + 1;
                            } else {
                                alert('Maximum quantity is ' + max);
                            }
                        }
                    }
                });
            });
        }

        // Fix for rendering issues
        const cards = document.querySelectorAll('.neocart-fixed-card');
        cards.forEach(card => {
            card.style.transform = 'translateZ(0)';
            card.style.backfaceVisibility = 'hidden';
            card.style.border = '1px solid #dee2e6';
            card.style.overflow = 'hidden';
        });

        // Remove any problematic animations
        document.querySelectorAll('*').forEach(el => {
            el.style.animation = 'none';
            el.style.transition = 'none';
        });
    })();
</script>

<style>
    /* Image Fix Styles */
    .product-image-container .image-wrapper {
        height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        border: 1px solid #dee2e6;
    }

    .product-image-container img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
    }

    /* WHITE LINES COMPLETE FIX */
    .neocart-fixed-card {
        border: 1px solid #dee2e6 !important;
        outline: 1px solid #dee2e6 !important;
        box-shadow: none !important;
        transform: translateZ(0);
        backface-visibility: hidden;
        perspective: 1000;
        overflow: hidden;
        position: relative;
    }

    .neocart-fixed-card * {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        animation: none !important;
        transition: none !important;
    }

    .review-item {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    .review-item:hover,
    .review-item:focus,
    .review-item:active {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    /* Remove any pseudo-elements */
    .review-item::before,
    .review-item::after,
    .review-item *::before,
    .review-item *::after {
        display: none !important;
        content: none !important;
    }

    /* Star rating styles */
    .star-rating .fas.fa-star {
        color: #ffc107;
    }

    .star-icon {
        color: #ddd;
    }

    .star-icon.text-warning {
        color: #ffc107 !important;
    }

    .quantity-group {
        width: 120px;
    }

    .quantity-group .btn {
        width: 30px;
        padding: 0.25rem 0.5rem;
    }

    /* Force stable rendering */
    .card,
    .card-header,
    .card-body {
        animation: none !important;
        transition: none !important;
    }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>