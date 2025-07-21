<?php
session_start();
require 'config.php';

if (!isset($_GET['farmer_id'])) {
    header("Location: customer_dashboard.php");
    exit();
}

$farmer_id = $_GET['farmer_id'];

// Get farmer and crops
$stmt = $conn->prepare("SELECT name FROM farmers WHERE id = ?");
$stmt->execute([$farmer_id]);
$farmer = $stmt->fetch();

$stmt = $conn->prepare("SELECT * FROM crops WHERE farmer_id = ?");
$stmt->execute([$farmer_id]);
$crops = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($farmer['name']) ?>'s Crops</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2><?= htmlspecialchars($farmer['name']) ?>'s Crops</h2>
    <div class="row">
        <?php foreach ($crops as $crop): ?>
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <?= htmlspecialchars($crop['name']) ?>
                    </div>
                    <div class="card-body">
                        <p><strong>Description:</strong> <?= htmlspecialchars($crop['description']) ?></p>
                        <p><strong>Price per KG:</strong> ₹<?= $crop['price_per_kg'] ?></p>
                        <form method="post" action="add_to_cart.php">
                            <input type="hidden" name="crop_id" value="<?= $crop['id'] ?>">
                            <label for="qty">Qty (KG):</label>
                            <input type="number" name="qty" min="1" required class="form-control mb-2">
                            <button type="submit" class="btn btn-success w-100">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <a href="view_cart.php" class="btn btn-primary">View Cart</a>
</div>
</body>
</html>
            