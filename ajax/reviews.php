<?php
// ajax/reviews.php - WITH EDIT FEATURE
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to submit review']);
    exit;
}

try {
    $user_id = (int)$_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

    // Validation
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        exit;
    }

    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Please select a rating between 1-5 stars']);
        exit;
    }

    if (empty($review_text)) {
        echo json_encode(['success' => false, 'message' => 'Please write your review']);
        exit;
    }

    if (strlen($review_text) < 10) {
        echo json_encode(['success' => false, 'message' => 'Review must be at least 10 characters long']);
        exit;
    }

    // Check database connection
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    // Check if product exists
    $product_check = $conn->prepare("SELECT id, name FROM products WHERE id = ?");
    if (!$product_check) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $product_check->bind_param("i", $product_id);
    $product_check->execute();
    $product_result = $product_check->get_result();
    
    if ($product_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    // Check if user already reviewed this product
    $check_stmt = $conn->prepare("SELECT id FROM product_reviews WHERE user_id = ? AND product_id = ?");
    if (!$check_stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $existing_review = $check_stmt->get_result()->fetch_assoc();

    // Sanitize review text
    $review_text = htmlspecialchars($review_text, ENT_QUOTES, 'UTF-8');

    if ($existing_review) {
        // UPDATE existing review
        $update_stmt = $conn->prepare("UPDATE product_reviews SET rating = ?, review_text = ?, updated_at = NOW() WHERE id = ?");
        if (!$update_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $update_stmt->bind_param("isi", $rating, $review_text, $existing_review['id']);
        
        if ($update_stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Review updated successfully!',
                'action' => 'updated'
            ]);
        } else {
            throw new Exception("Update failed: " . $update_stmt->error);
        }
    } else {
        // INSERT new review
        $insert_stmt = $conn->prepare("INSERT INTO product_reviews (user_id, product_id, rating, review_text, created_at) VALUES (?, ?, ?, ?, NOW())");
        if (!$insert_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $insert_stmt->bind_param("iiis", $user_id, $product_id, $rating, $review_text);
        
        if ($insert_stmt->execute()) {
            // Get user name for response
            $user_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user = $user_stmt->get_result()->fetch_assoc();
            
            // Calculate new average rating and stats
            $avg_stmt = $conn->prepare("
                SELECT 
                    AVG(rating) as avg_rating,
                    COUNT(*) as total_reviews
                FROM product_reviews 
                WHERE product_id = ?
            ");
            $avg_stmt->bind_param("i", $product_id);
            $avg_stmt->execute();
            $result = $avg_stmt->get_result()->fetch_assoc();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Review submitted successfully!',
                'avg_rating' => round($result['avg_rating'], 1),
                'total_reviews' => $result['total_reviews'],
                'user_name' => $user['name'] ?? 'User',
                'action' => 'submitted'
            ]);
        } else {
            throw new Exception("Insert failed: " . $insert_stmt->error);
        }
    }

} catch (Exception $e) {
    error_log("Review submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

// Close connection
if (isset($conn)) {
    $conn->close();
}
?>