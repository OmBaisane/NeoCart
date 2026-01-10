<?php
// pages/process_order.php

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

// This page expects POST data from payment.php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit();
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$total_amount = floatval($_POST['total_amount'] ?? 0);
$payment_method = trim($_POST['payment_method'] ?? 'cod');

// Validate required fields
if (empty($name) || empty($email) || empty($address) || $total_amount <= 0) {
    $_SESSION['error'] = "Invalid order data. Please try again.";
    header('Location: checkout.php');
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // SIMPLE FIX: Directly insert order without column check
    // Use default 'confirmed' status instead of 'pending'
    $order_stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, customer_email, customer_address, customer_phone, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, 'confirmed')");
    $order_stmt->bind_param("issssd", $user_id, $name, $email, $address, $phone, $total_amount);
    
    if (!$order_stmt->execute()) {
        throw new Exception("Failed to create order: " . $conn->error);
    }
    
    $order_id = $conn->insert_id;
    $order_stmt->close();

    // 2. Get cart items
    $cart_stmt = $conn->prepare("SELECT c.product_id, c.quantity, p.price, p.name 
                                FROM cart c 
                                JOIN products p ON c.product_id = p.id 
                                WHERE c.cart_user_id = ?");
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_items = $cart_stmt->get_result();

    // 3. Insert order items
    $order_item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    
    while ($item = $cart_items->fetch_assoc()) {
        $order_item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        if (!$order_item_stmt->execute()) {
            throw new Exception("Failed to add order items: " . $conn->error);
        }
    }
    $order_item_stmt->close();
    $cart_stmt->close();

    // 4. Clear user's cart
    $clear_cart_stmt = $conn->prepare("DELETE FROM cart WHERE cart_user_id = ?");
    $clear_cart_stmt->bind_param("i", $user_id);
    if (!$clear_cart_stmt->execute()) {
        throw new Exception("Failed to clear cart: " . $conn->error);
    }
    $clear_cart_stmt->close();

    // Commit transaction
    $conn->commit();

    // Redirect to success page with order ID
    header('Location: order_success.php?order_id=' . $order_id);
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['error'] = "Order failed: " . $e->getMessage();
    header('Location: checkout.php');
    exit();
}
?>