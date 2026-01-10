<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Admin authentication 
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit();
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    $_SESSION['error'] = "Product not found!";
    header('Location: products.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $image_name = $product['image'];

    // Validation
    if (empty($name)) {
        $errors[] = "Product name is required.";
    }

    if (empty($description)) {
        $errors[] = "Product description is required.";
    }

    if ($price <= 0) {
        $errors[] = "Price must be greater than 0.";
    }

    // Image upload handling (optional)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $max_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($file_extension, $allowed_types)) {
            $errors[] = "Only JPG, JPEG, PNG, GIF & WEBP files are allowed.";
        }

        if ($_FILES['image']['size'] > $max_size) {
            $errors[] = "Image size must be less than 2MB.";
        }

        if (empty($errors)) {
            $upload_dir = __DIR__ . '/../assets/images/products/';
            
            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    $errors[] = "Failed to create upload directory.";
                }
            }

            if (empty($errors)) {
                $image_name = time() . '_' . uniqid() . '.' . $file_extension;
                $target_path = $upload_dir . $image_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    // Delete old image if new one uploaded successfully
                    if (!empty($product['image'])) {
                        $old_image_path = $upload_dir . $product['image'];
                        if (file_exists($old_image_path) && is_file($old_image_path)) {
                            unlink($old_image_path);
                        }
                    }
                } else {
                    $errors[] = "Failed to upload image. Please try again.";
                    $image_name = $product['image']; // Keep old image
                }
            }
        }
    }

    // Update product if no errors
    if (empty($errors)) {
        $update_stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, image=? WHERE id=?");
        $update_stmt->bind_param("ssdsi", $name, $description, $price, $image_name, $product_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Product '{$name}' updated successfully!";
            header('Location: products.php');
            exit();
        } else {
            $errors[] = "Failed to update product: " . $conn->error;
        }
        $update_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin | NeoCart</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            display: none;
            margin-top: 10px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }
        .current-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container mt-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                        <li class="breadcrumb-item active">Edit Product</li>
                    </ol>
                </nav>
                
                <h2 class="mb-4">
                    <i class="fas fa-edit me-2 text-primary"></i>Edit Product
                </h2>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Edit Product: 
                            <span class="fw-normal"><?php echo htmlspecialchars($product['name']); ?></span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="post" enctype="multipart/form-data" id="editProductForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3 form-floating">
                                        <input type="text" name="name" class="form-control" 
                                               value="<?php echo htmlspecialchars($product['name']); ?>" 
                                               placeholder="Product Name" required>
                                        <label class="form-label">Product Name *</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3 form-floating">
                                        <input type="number" name="price" class="form-control" 
                                               step="0.01" min="0.01" 
                                               value="<?php echo htmlspecialchars($product['price']); ?>" 
                                               placeholder="Price" required>
                                        <label class="form-label">Price (₹) *</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-floating">
                                <textarea name="description" class="form-control" 
                                          placeholder="Product Description" required style="height: 100px"><?php echo htmlspecialchars($product['description']); ?></textarea>
                                <label class="form-label">Description *</label>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Current Image</label>
                                <div class="mb-3">
                                    <?php if (!empty($product['image']) && file_exists('../assets/images/products/' . $product['image'])): ?>
                                        <img src="../assets/images/products/<?php echo $product['image']; ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             class="current-image"
                                             onerror="this.src='../assets/images/placeholder.jpg'">
                                    <?php else: ?>
                                        <div class="current-image bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-image text-muted fa-2x"></i>
                                        </div>
                                        <small class="text-muted d-block mt-1">No image available</small>
                                    <?php endif; ?>
                                </div>
                                
                                <label class="form-label fw-semibold">Upload New Image (Optional)</label>
                                <input type="file" name="image" class="form-control" 
                                       accept="image/*" id="imageInput">
                                <small class="text-muted">Supported formats: JPG, JPEG, PNG, GIF, WEBP (Max: 2MB)</small>
                                
                                <!-- New Image Preview -->
                                <img id="imagePreview" class="preview-image" alt="New Image Preview">
                            </div>
                            
                            <div class="d-flex gap-2 pt-3 border-top">
                                <button type="submit" class="btn btn-primary px-4 py-2">
                                    <i class="fas fa-save me-2"></i>Update Product
                                </button>
                                <a href="products.php" class="btn btn-outline-secondary px-4 py-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Products
                                </a>
                                <a href="add_products.php" class="btn btn-outline-success px-4 py-2 ms-auto">
                                    <i class="fas fa-plus me-2"></i>Add New
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Image preview functionality
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });

        // Form validation
        document.getElementById('editProductForm').addEventListener('submit', function(e) {
            const price = document.querySelector('input[name="price"]');
            if (parseFloat(price.value) <= 0) {
                e.preventDefault();
                alert('Price must be greater than 0.');
                price.focus();
            }
        });
    </script>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>