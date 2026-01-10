<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Admin authentication 
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit();
}

$errors = [];
$success = false;

// Initialize form fields
$name = $description = $price = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $image_name = '';

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

    // Image upload handling
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

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    $errors[] = "Failed to upload image. Please try again.";
                }
            }
        }
    } else {
        $errors[] = "Product image is required.";
    }

    // Insert product if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, stock) VALUES (?, ?, ?, ?, ?)");
        $stock = 0; // Default stock
        $stmt->bind_param("ssdsi", $name, $description, $price, $image_name, $stock);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Product '{$name}' added successfully!";
            header('Location: products.php');
            exit();
        } else {
            $errors[] = "Failed to add product: " . $conn->error;
            
            // Delete uploaded image if database insert failed
            if (!empty($image_name) && file_exists($upload_dir . $image_name)) {
                unlink($upload_dir . $image_name);
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin | NeoCart</title>
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
                        <li class="breadcrumb-item active">Add Product</li>
                    </ol>
                </nav>
                
                <h2 class="mb-4">
                    <i class="fas fa-plus-circle me-2 text-success"></i>Add New Product
                </h2>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white py-3">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Product Information</h5>
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

                        <form method="post" enctype="multipart/form-data" id="addProductForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3 form-floating">
                                        <input type="text" name="name" class="form-control" 
                                               value="<?php echo htmlspecialchars($name ?? ''); ?>" 
                                               placeholder="Product Name" required>
                                        <label class="form-label">Product Name *</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3 form-floating">
                                        <input type="number" name="price" class="form-control" 
                                               step="0.01" min="0.01" 
                                               value="<?php echo htmlspecialchars($price ?? ''); ?>" 
                                               placeholder="Price" required>
                                        <label class="form-label">Price (₹) *</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-floating">
                                <textarea name="description" class="form-control" 
                                          placeholder="Product Description" required style="height: 100px"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                                <label class="form-label">Description *</label>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Product Image *</label>
                                <input type="file" name="image" class="form-control" 
                                       accept="image/*" required id="imageInput">
                                <small class="text-muted">Supported formats: JPG, JPEG, PNG, GIF, WEBP (Max: 2MB)</small>
                                
                                <!-- Image Preview -->
                                <img id="imagePreview" class="preview-image" alt="Image Preview">
                            </div>
                            
                            <div class="d-flex gap-2 pt-3 border-top">
                                <button type="submit" class="btn btn-success px-4 py-2">
                                    <i class="fas fa-save me-2"></i>Save Product
                                </button>
                                <a href="products.php" class="btn btn-outline-secondary px-4 py-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Products
                                </a>
                                <button type="reset" class="btn btn-outline-danger px-4 py-2 ms-auto">
                                    <i class="fas fa-undo me-2"></i>Reset Form
                                </button>
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
        document.getElementById('addProductForm').addEventListener('submit', function(e) {
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