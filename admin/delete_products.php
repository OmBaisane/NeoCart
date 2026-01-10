<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Admin check
if(!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit();
}

// Get product ID from GET
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if($id > 0){
    // Fetch product image
    $stmt = $conn->prepare("SELECT image FROM products WHERE id=?");
    if($stmt){
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if(method_exists($stmt, 'get_result')){
            $res = $stmt->get_result()->fetch_assoc();
        } else {
            $stmt->bind_result($image);
            $stmt->fetch();
            $res = ['image' => $image];
        }

        // Delete product image file if exists
        if($res && !empty($res['image'])){
            $imagePath = __DIR__ . '/../assets/image/products/' . $res['image'];
            if(file_exists($imagePath)){
                unlink($imagePath);
            }
        }
    } else {
        die("Error preparing SELECT query: " . $conn->error);
    }

    // Delete related cart items first to avoid foreign key constraint error
    $deleteCart = $conn->prepare("DELETE FROM cart WHERE product_id=?");
    if($deleteCart){
        $deleteCart->bind_param("i", $id);
        $deleteCart->execute();
    } else {
        die("Error preparing DELETE cart query: " . $conn->error);
    }

    // Delete related order_items first to avoid foreign key constraint error
    $deleteOrders = $conn->prepare("DELETE FROM order_items WHERE product_id=?");
    if($deleteOrders){
        $deleteOrders->bind_param("i", $id);
        $deleteOrders->execute();
    } else {
        die("Error preparing DELETE order_items query: " . $conn->error);
    }

    // Delete product from products table
    $d = $conn->prepare("DELETE FROM products WHERE id=?");
    if($d){
        $d->bind_param("i", $id);
        $d->execute();
    } else {
        die("Error preparing DELETE product query: " . $conn->error);
    }
}

// Redirect back to products page
header('Location: products.php');
exit();
?>