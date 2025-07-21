<?php
session_start();
include('config.php');

$customer_id = $_SESSION['customer_id'] ?? 1;

// Fetch all cart items grouped by farmer
$stmt = $conn->prepare("
    SELECT c.id AS cart_id, crops.name AS crop_name, crops.price_per_kg, crops.image, c.quantity, 
           crops.farmer_id, f.name AS farmer_name
    FROM carts c
    JOIN crops ON c.crop_id = crops.id
    JOIN farmers f ON crops.farmer_id = f.id
    WHERE c.customer_id = ?
    ORDER BY f.name
");
$stmt->execute([$customer_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by farmer_id
$grouped = [];
foreach ($items as $item) {
    $grouped[$item['farmer_id']]['farmer_name'] = $item['farmer_name'];
    $grouped[$item['farmer_id']]['items'][] = $item;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Grouped Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    

<?php include 'navbar.php'; ?>
<div class="container mt-5">
    <h2 class="text-center mb-4">🧺 My Cart (Grouped by Farmer)</h2>

    <?php if ($grouped): ?>
        <?php foreach ($grouped as $farmer_id => $group): ?>
            <div class="card mb-4 shadow">
                <div class="card-header bg-dark text-white">
                    👨‍🌾 Farmer: <?= htmlspecialchars($group['farmer_name']) ?>
                </div>
                <div class="card-body">
                    <form method="post" action="place_order.php">
                        <input type="hidden" name="farmer_id" value="<?= $farmer_id ?>">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Crop</th>
                                    <th>Price (₹/kg)</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $sub_total = 0;
                                foreach ($group['items'] as $item): 
                                    $total = $item['price_per_kg'] * $item['quantity'];
                                    $sub_total += $total;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['crop_name']) ?></td>
                                    <td>₹<?= $item['price_per_kg'] ?></td>
                                    <td><?= $item['quantity'] ?> kg</td>
                                    <td>₹<?= $total ?></td>
                                    <td>
                                        <form method="post" action="remove_cart.php" onsubmit="return confirm('Remove item?')">
                                            <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                            <button class="btn btn-sm btn-danger">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach ?>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                    <td colspan="2"><strong>₹<?= $sub_total ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    <div class="mt-3">
    <label><strong>Payment Method:</strong></label>
    <select name="payment_method" class="form-select mb-3" required>
        <option value="cod">Cash on Delivery</option>
        <option value="online">Online Payment</option>
    </select>
    <button class="btn btn-success">Place Order with <?= htmlspecialchars($group['farmer_name']) ?></button>
</div>
   
                    </form>
                </div>
            </div>
        <?php endforeach ?>
    <?php else: ?>
        <p class="text-center mt-5">🛒 Your cart is empty!</p>
    <?php endif ?>
</div>
</body>
</html>
