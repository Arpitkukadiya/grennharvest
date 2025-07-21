<?php
session_start();

// ✅ DB & Razorpay credentials
$host = 'localhost';
$dbname = 'greenharvest';
$username = 'root';
$password = '';
$keyId = "rzp_test_weWkTdxTnwUghx";
$keySecret = "7ujDworqXqupNSitmjSpsa8M";

// ✅ DB Connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ✅ Razorpay SDK
require('vendor/autoload.php');
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Status | GreenHarvest 🌿</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #f4f7f9, #e8f5e9);
            font-family: 'Nunito', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .status-box {
            background: white;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            text-align: center;
            max-width: 500px;
        }
        h2 { font-size: 2rem; margin-bottom: 15px; }
        .success { color: #2e7d32; }
        .error { color: #c62828; }
        .info { font-size: 1.1rem; color: #555; margin: 8px 0; }
        .emoji { font-size: 3rem; }
        .back-btn {
            margin-top: 25px;
            padding: 10px 20px;
            background-color: #43a047;
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }
        .back-btn:hover {
            background-color: #2e7d32;
        }
    </style>
</head>
<body>

<div class="status-box">
<?php
if (
    isset($_POST['razorpay_payment_id']) &&
    isset($_POST['razorpay_order_id']) &&
    isset($_POST['razorpay_signature'])
) {
    $api = new Api($keyId, $keySecret);

    $attributes = [
        'razorpay_order_id' => $_POST['razorpay_order_id'],
        'razorpay_payment_id' => $_POST['razorpay_payment_id'],
        'razorpay_signature' => $_POST['razorpay_signature']
    ];

    try {
        $api->utility->verifyPaymentSignature($attributes);

        echo "<div class='emoji'>✅</div>";
        echo "<h2 class='success'>Payment Successful!</h2>";
        echo "<div class='info'>🎉 Your payment has been verified.</div>";
        echo "<div class='info'>🧾 Payment ID: <strong>" . htmlspecialchars($_POST['razorpay_payment_id']) . "</strong></div>";
        echo "<div class='info'>📦 Order ID: <strong>" . htmlspecialchars($_POST['razorpay_order_id']) . "</strong></div>";

        // ✔ Order Data from session
        $orderData = $_SESSION['order_data'] ?? null;
        $customer_id = $_SESSION['customer_id'] ?? null;

        if ($orderData && $customer_id) {
            // 1. Insert into orders table
           // 1. Insert into orders table
$stmt = $conn->prepare("INSERT INTO orders (customer_id, total_price, payment_method, status, order_date) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([
    $customer_id,
    $orderData['total_price'],
    'online',
    'paid'
]);

$order_id = $conn->lastInsertId();

// 2. Insert each item into order_items
if (!empty($orderData['items'])) {
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, crop_id, quantity, price_per_kg) VALUES (?, ?, ?, ?)");
    foreach ($orderData['items'] as $item) {
        $stmt->execute([
            $order_id,
            $item['crop_id'],
            $item['quantity'],
            $item['price_per_kg']
        ]);
    }
}

            $order_id = $conn->lastInsertId();

            // 2. Insert payment record
            $stmt = $conn->prepare("INSERT INTO payments (razorpay_payment_id, razorpay_order_id, amount, status, customer_id, order_id) VALUES (?, ?, ?, 'success', ?, ?)");
            $stmt->execute([
                $_POST['razorpay_payment_id'],
                $_POST['razorpay_order_id'],
                $orderData['total_price'],
                $customer_id,
                $order_id
            ]);

            // ✅ Clear session
            unset($_SESSION['order_data']);
        } else {
            echo "<div class='info'>❌ Missing order data in session.</div>";
        }

    } catch (SignatureVerificationError $e) {
        echo "<div class='emoji'>❌</div>";
        echo "<h2 class='error'>Verification Failed</h2>";
        echo "<div class='info'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";

        // Log failed payment
        $amount = $_SESSION['order_data']['total_price'] ?? 0;
        $customer_id = $_SESSION['customer_id'] ?? null;

        $stmt = $conn->prepare("INSERT INTO payments (razorpay_payment_id, razorpay_order_id, amount, status, customer_id) VALUES (?, ?, ?, 'failed', ?)");
        $stmt->execute([$_POST['razorpay_payment_id'], $_POST['razorpay_order_id'], $amount, $customer_id]);
    }
} else {
    echo "<div class='emoji'>⚠️</div>";
    echo "<h2 class='error'>Invalid Request</h2>";
    echo "<div class='info'>No payment data received.</div>";
}
?>
    <a href="../order_history.php" class="back-btn">🔙 Back to Home</a>
</div>

</body>
</html>
