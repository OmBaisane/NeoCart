<?php
session_start();
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required', 'login' => true]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['quantity'] ?? 1));

    $pstmt = $conn->prepare("SELECT id FROM products WHERE id=?");
    $pstmt->bind_param("i", $product_id);
    $pstmt->execute();
    $pret = $pstmt->get_result();
    if (!$pret->num_rows) {
        echo json_encode(['success'=>false, 'message'=>'Product not found']);
        exit;
    }

    $s = $conn->prepare("SELECT id, quantity FROM cart WHERE cart_user_id=? AND product_id=?");
    $s->bind_param("ii",$user_id,$product_id);
    $s->execute();
    $r = $s->get_result();

    if($r->num_rows){
        $row=$r->fetch_assoc();
        $newq=$row['quantity']+$qty;
        $u=$conn->prepare("UPDATE cart SET quantity=? WHERE id=?");
        $u->bind_param("ii",$newq,$row['id']);
        $u->execute();
    } else {
        $i=$conn->prepare("INSERT INTO cart(product_id,quantity,cart_user_id) VALUES(?,?,?)");
        $i->bind_param("iii",$product_id,$qty,$user_id);
        $i->execute();
    }

    $c=$conn->prepare("SELECT SUM(quantity) as cnt FROM cart WHERE cart_user_id=?");
    $c->bind_param("i",$user_id);
    $c->execute();
    $cnt=$c->get_result()->fetch_assoc()['cnt'] ?? 0;

    echo json_encode(['success'=>true,'message'=>'Added to cart','cart_count'=>(int)$cnt]);
    exit;
}

if ($action==='update') {
    $cart_id=(int)($_POST['cart_id']??0);
    $qty=max(1,(int)($_POST['quantity']??1));

    $u=$conn->prepare("UPDATE cart SET quantity=? WHERE id=? AND cart_user_id=?");
    $u->bind_param("iii",$qty,$cart_id,$user_id);
    $u->execute();

    $stmt=$conn->prepare("SELECT c.quantity,p.price FROM cart c JOIN products p ON p.id=c.product_id WHERE c.id=?");
    $stmt->bind_param("i",$cart_id);
    $stmt->execute();
    $res=$stmt->get_result()->fetch_assoc();
    $subtotal=$res['quantity']*$res['price'];

    $gt_stmt=$conn->prepare("SELECT SUM(c.quantity*p.price) AS grand_total FROM cart c JOIN products p ON p.id=c.product_id WHERE c.cart_user_id=?");
    $gt_stmt->bind_param("i",$user_id);
    $gt_stmt->execute();
    $grand_total=$gt_stmt->get_result()->fetch_assoc()['grand_total']??0;

    $c=$conn->prepare("SELECT SUM(quantity) as cnt FROM cart WHERE cart_user_id=?");
    $c->bind_param("i",$user_id);
    $c->execute();
    $cnt=$c->get_result()->fetch_assoc()['cnt']??0;

    echo json_encode([
        'success'=>true,
        'subtotal'=>number_format($subtotal,2),
        'grand_total'=>number_format($grand_total,2),
        'cart_count'=>(int)$cnt
    ]);
    exit;
}

if ($action==='remove') {
    $cart_id=(int)($_POST['cart_id']??0);
    $d=$conn->prepare("DELETE FROM cart WHERE id=? AND cart_user_id=?");
    $d->bind_param("ii",$cart_id,$user_id);
    $d->execute();

    $gt_stmt=$conn->prepare("SELECT SUM(c.quantity*p.price) AS grand_total FROM cart c JOIN products p ON p.id=c.product_id WHERE c.cart_user_id=?");
    $gt_stmt->bind_param("i",$user_id);
    $gt_stmt->execute();
    $grand_total=$gt_stmt->get_result()->fetch_assoc()['grand_total']??0;

    $c=$conn->prepare("SELECT SUM(quantity) as cnt FROM cart WHERE cart_user_id=?");
    $c->bind_param("i",$user_id);
    $c->execute();
    $cnt=$c->get_result()->fetch_assoc()['cnt']??0;

    echo json_encode([
        'success'=>true,
        'grand_total'=>number_format($grand_total,2),
        'cart_count'=>(int)$cnt
    ]);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Unknown action']);
exit;
?>