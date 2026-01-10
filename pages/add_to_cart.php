<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to add products to cart";
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's an add to cart request
    if (isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        $return_url = isset($_POST['return_url']) ? $_POST['return_url'] : 'products.php';

        // Validate quantity
        if ($quantity < 1) {
            $quantity = 1;
        }
        if ($quantity > 10) {
            $quantity = 10;
        }

        // Validate product exists
        $product_stmt = $conn->prepare("SELECT id, name, price FROM products WHERE id = ?");
        $product_stmt->bind_param("i", $product_id);
        $product_stmt->execute();
        $product_result = $product_stmt->get_result();

        if ($product_result->num_rows === 0) {
            $_SESSION['error'] = "Product not found!";
            header("Location: $return_url");
            exit();
        }

        $product = $product_result->fetch_assoc();

        // Check if product already in cart
        $check_stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE product_id = ? AND cart_user_id = ?");
        $check_stmt->bind_param("ii", $product_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Update quantity if exists
            $existing_item = $check_result->fetch_assoc();
            $new_quantity = $existing_item['quantity'] + $quantity;
            
            $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $new_quantity, $existing_item['id']);
            
            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Product quantity updated in cart!";
            } else {
                $_SESSION['error'] = "Failed to update cart!";
            }
            $update_stmt->close();
        } else {
            // Insert new item
            $insert_stmt = $conn->prepare("INSERT INTO cart (product_id, quantity, cart_user_id) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("iii", $product_id, $quantity, $user_id);
            
            if ($insert_stmt->execute()) {
                $_SESSION['success'] = "Product added to cart successfully!";
            } else {
                $_SESSION['error'] = "Failed to add product to cart!";
            }
            $insert_stmt->close();
        }

        $check_stmt->close();
        $product_stmt->close();
    } else {
        $_SESSION['error'] = "Product ID is required!";
    }
} else {
    $_SESSION['error'] = "Invalid request method!";
}

// Redirect back
$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : 'products.php';
header("Location: $return_url");
exit();
?>