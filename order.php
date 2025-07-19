<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

if (isset($_GET['crop_id'])) {
    $crop_id = $_GET['crop_id'];

    $stmt = $conn->prepare("SELECT c.name AS crop_name, c.description, c.price_per_kg, c.season, c.certificate_available, f.name AS farmer_name, c.video FROM crops c JOIN farmers f ON c.farmer_id = f.id WHERE c.id = ?");
    $stmt->execute([$crop_id]);
    $crop = $stmt->fetch();
} else {
    header('Location: crop_info.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quantity = $_POST['quantity'];
    $payment_method = $_POST['payment_method'];
    $total_price = $crop['price_per_kg'] * $quantity;

    if ($payment_method === 'online') {
        $_SESSION['order_data'] = [
            'customer_id' => $customer_id,
            'crop_id' => $crop_id,
            'crop_name' => $crop['crop_name'],
            'quantity' => $quantity,
            'price_per_kg' => $crop['price_per_kg'],
            'total_price' => $total_price,
            'payment_method' => $payment_method,
            'farmer_name' => $crop['farmer_name']
        ];
        header('Location: RAZORPAY_PAYMENT_GATEWAY/checkout.php');
        exit();
    } else {
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, crop_id, quantity, total_price, payment_method, status, order_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$customer_id, $crop_id, $quantity, $total_price, $payment_method, 'pending']);
        header('Location: order_history.php');
        exit();
    }
}
?>
<!-- Your existing HTML layout for form -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Crop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
            background: #ffffff;
        }

        .card-header {
            background: #007bff;
            color: white;
            font-size: 1.5rem;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .btn-primary {
            background: #007bff;
            border: none;
            padding: 10px;
            font-size: 1rem;
            font-weight: bold;
            width: 100%;
            border-radius: 5px;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .form-group label {
            font-weight: bold;
        }

        video {
            width: 100%;
            border-radius: 8px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container">
    <h2 class="mb-3 mt-4 text-center">Book a Crop</h2>

    <div class="card w-50" style="margin: 0 auto;">
        <div class="card-header">
            <?php echo htmlspecialchars($crop['crop_name']); ?>
        </div>
        <div class="card-body">
            <p><strong>Description:</strong> <?php echo htmlspecialchars($crop['description']); ?></p>
            <p><strong>Price per KG:</strong> ₹<?php echo htmlspecialchars($crop['price_per_kg']); ?></p>
            <p><strong>Season:</strong> <?php echo htmlspecialchars($crop['season']); ?></p>
            <p><strong>Farmer:</strong> <?php echo htmlspecialchars($crop['farmer_name']); ?></p>
            <p><strong>Certificate Available:</strong> <?php echo $crop['certificate_available'] ? 'Yes' : 'No'; ?></p>

            <?php if (!empty($crop['video'])): ?>
                <video controls>
                    <source src="<?php echo htmlspecialchars($crop['video']); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php endif; ?>

            <form method="POST" class="mt-3">
                <div class="form-group">
                    <label for="quantity">Quantity (in KG)</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" required>
                </div>

                <div class="form-group mt-3">
                    <label for="payment_method">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-control" required>
                        <option value="online">Online</option>
                        <option value="cod">Cash on Delivery</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Book Crop</button>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
