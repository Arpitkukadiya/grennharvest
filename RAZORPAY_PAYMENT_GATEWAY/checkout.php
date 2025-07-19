<?php
session_start();
require('config.php');
require('vendor/autoload.php');

use Razorpay\Api\Api;

if (!isset($_SESSION['order_data'])) {
    die("Order information is missing. Please go back and try again.");
}

$order = $_SESSION['order_data'];
$totalAmount = $order['total_price'] * 100;

$api = new Api($keyId, $keySecret);

$orderData = [
    'receipt' => 'receipt#' . rand(1000, 9999),
    'amount' => $totalAmount,
    'currency' => 'INR',
    'payment_capture' => 1
];

$razorpayOrder = $api->order->create($orderData);
$razorpayOrderId = $razorpayOrder['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GreenHarvest 🌾 | Pay ₹<?= htmlspecialchars($order['total_price']) ?></title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #e8f5e9, #f4f7f9);
            font-family: 'Nunito', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .payment-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            max-width: 500px;
            text-align: center;
        }

        .payment-card h2 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #2e7d32;
        }

        .emoji-header {
            font-size: 3rem;
        }

        .payment-card p {
            margin: 10px 0;
            font-size: 1.05rem;
            color: #555;
        }

        .price-tag {
            font-size: 2.2rem;
            font-weight: 800;
            color: #388e3c;
            margin-top: 15px;
            margin-bottom: 25px;
        }

        .btn-pay {
            background-color: #43a047;
            color: #fff;
            border: none;
            font-size: 1rem;
            padding: 14px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-pay:hover {
            background-color: #2e7d32;
        }
    </style>
</head>
<body>

<div class="payment-card">
    <div class="emoji-header">🌱 GreenHarvest Payment</div>
    <h2>Crop Order Checkout</h2>
    <p><strong>Crop:</strong> <?= htmlspecialchars($order['crop_name']) ?></p>
    <p><strong>Quantity:</strong> <?= htmlspecialchars($order['quantity']) ?> KG</p>
    <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
    <div class="price-tag">₹<?= htmlspecialchars($order['total_price']) ?></div>
    <button id="rzp-button" class="btn-pay">💳 Pay Now Securely</button>
</div>

<script>
    var options = {
        "key": "<?= $keyId ?>",
        "amount": "<?= $totalAmount ?>",
        "currency": "INR",
        "name": "GreenHarvest 🌾",
        "description": "<?= htmlspecialchars($order['quantity']) ?> KG of <?= htmlspecialchars($order['crop_name']) ?>",
        "order_id": "<?= $razorpayOrderId ?>",
        "handler": function (response) {
            var form = document.createElement("form");
            form.method = "POST";
            form.action = "success.php";

            var inputs = {
                "razorpay_payment_id": response.razorpay_payment_id,
                "razorpay_order_id": response.razorpay_order_id,
                "razorpay_signature": response.razorpay_signature
            };

            for (var key in inputs) {
                var input = document.createElement("input");
                input.type = "hidden";
                input.name = key;
                input.value = inputs[key];
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        },
        "prefill": {
            "name": "<?= $_SESSION['customer_name'] ?? 'Customer' ?>",
            "email": "<?= $_SESSION['customer_email'] ?? 'customer@email.com' ?>",
            "contact": "<?= $_SESSION['customer_mobile'] ?? '9999999999' ?>"
        },
        "theme": {
            "color": "#66bb6a"
        }
    };

    document.getElementById('rzp-button').onclick = function(e) {
        var rzp = new Razorpay(options);
        rzp.open();
        e.preventDefault();
    };
</script>

</body>
</html>
