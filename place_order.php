<?php
session_start();
include('config.php');

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];
$payment_method = $_POST['payment_method'] ?? 'cod';
$farmer_id = $_POST['farmer_id'] ?? null;

if (!$farmer_id) {
    header('Location: view_cart.php');
    exit();
}

// Fetch cart items for that farmer
$stmt = $conn->prepare("
    SELECT c.id AS cart_id, c.crop_id, c.quantity, crops.price_per_kg, crops.name AS crop_name, 
           f.name AS farmer_name 
    FROM carts c 
    JOIN crops ON c.crop_id = crops.id 
    JOIN farmers f ON crops.farmer_id = f.id 
    WHERE c.customer_id = ? AND f.id = ?
");
$stmt->execute([$customer_id, $farmer_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    header('Location: view_cart.php');
    exit();
}

$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price_per_kg'] * $item['quantity'];
}

if ($payment_method === 'online') {
    // Prepare data for Razorpay
    $_SESSION['order_data'] = [
        'customer_id' => $customer_id,
        'farmer_id' => $farmer_id,
        'payment_method' => $payment_method,
        'total_price' => $total_price,
        'items' => $cart_items
    ];

    header('Location: RAZORPAY_PAYMENT_GATEWAY/checkout.php');
    exit();
} else {
    // Place each cart item as individual order
    $orderStmt = $conn->prepare("
        INSERT INTO orders (customer_id, crop_id, quantity, total_price, payment_method, status, order_date)
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");

    foreach ($cart_items as $item) {
        $crop_total = $item['price_per_kg'] * $item['quantity'];
        $orderStmt->execute([
            $customer_id,
            $item['crop_id'],
            $item['quantity'],
            $crop_total,
            $payment_method
        ]);
    }

    // Delete those items from cart
    $deleteStmt = $conn->prepare("DELETE FROM carts WHERE customer_id = ? AND crop_id = ?");
    foreach ($cart_items as $item) {
        $deleteStmt->execute([$customer_id, $item['crop_id']]);
    }

    header('Location: order_history.php');
    exit();
}
?>
