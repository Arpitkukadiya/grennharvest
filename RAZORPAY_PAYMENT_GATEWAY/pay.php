<?php
session_start();
include '../config.php';  // Go up one level to include config.php


if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

if (!isset($_GET['order_id'])) {
    die("Order ID not provided.");
}

$order_id = $_GET['order_id'];
$customer_id = $_SESSION['customer_id'];

// Fetch order and crop details
$stmt = $conn->prepare("SELECT o.*, c.name AS crop_name 
                        FROM orders o 
                        JOIN crops c ON o.crop_id = c.id 
                        WHERE o.id = ? AND o.customer_id = ?");
$stmt->execute([$order_id, $customer_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found or you do not have permission.");
}

// Store for Razorpay checkout
$_SESSION['order_temp'] = [
    'customer_id'    => $order['customer_id'],
    'crop_id'        => $order['crop_id'],
    'quantity'       => $order['quantity'],
    'total_price'    => $order['total_price'],
    'payment_method' => 'online'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pay ₹<?= htmlspecialchars($order['total_price']) ?> - <?= htmlspecialchars($order['crop_name']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f7f9;
            font-family: 'Segoe UI', sans-serif;
        }

        .payment-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            max-width: 500px;
            margin: 80px auto;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
        }

        .payment-card h2 {
            font-size: 1.8rem;
            color: #007bff;
            margin-bottom: 20px;
        }

        .btn-pay {
            background: #007bff;
            border: none;
            font-weight: bold;
            padding: 12px;
            font-size: 1.1rem;
            width: 100%;
            border-radius: 5px;
        }

        .btn-pay:hover {
            background: #0056b3;
        }

        .price-tag {
            font-size: 2rem;
            color: #28a745;
            font-weight: bold;
        }

        .small-label {
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>
<body>

<div class="payment-card text-center">
    <h2>Razorpay Secure Payment</h2>
    <p class="small-label">Crop: <strong><?= htmlspecialchars($order['crop_name']) ?></strong></p>
    <p class="small-label">Quantity: <strong><?= htmlspecialchars($order['quantity']) ?> KG</strong></p>
    <p class="small-label">Order Date: <?= htmlspecialchars($order['order_date']) ?></p>
    <div class="price-tag mb-4">₹<?= htmlspecialchars($order['total_price']) ?></div>
    <form action="checkout.php" method="POST">
        <button type="submit" class="btn btn-pay">Proceed to Pay with Razorpay</button>
    </form>
</div>

</body>
</html>
