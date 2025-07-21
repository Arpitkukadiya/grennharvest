<?php
session_start();
include('config.php'); // DB connection

$customer_id = $_SESSION['customer_id'] ?? 1; // Replace with actual session in production

// Add to cart handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crop_id'])) {
    $crop_id = $_POST['crop_id'];
    $quantity = $_POST['quantity'];

    // Check crop details
    $cropStmt = $conn->prepare("SELECT * FROM crops WHERE id = ?");
    $cropStmt->execute([$crop_id]);
    $crop = $cropStmt->fetch();

    if ($crop && $quantity > 0) {
    // Check if already in cart
    $cartCheck = $conn->prepare("SELECT * FROM carts WHERE customer_id = ? AND crop_id = ?");
    $cartCheck->execute([$customer_id, $crop_id]);

    if ($cartCheck->rowCount() > 0) {
        // If exists, update quantity
        $updateCart = $conn->prepare("UPDATE carts SET quantity = quantity + ? WHERE customer_id = ? AND crop_id = ?");
        $updateCart->execute([$quantity, $customer_id, $crop_id]);
    } else {
        // Otherwise insert new
        $insert = $conn->prepare("INSERT INTO carts (customer_id, crop_id, quantity) VALUES (?, ?, ?)");
        $insert->execute([$customer_id, $crop_id, $quantity]);
    }

    $success = "Crop added to cart successfully!";
}
 else {
        $error = "Invalid crop or quantity.";
    }
}

// ✅ Fix: Check if 'city' column exists in DB
$farmerStmt = $conn->query("SELECT DISTINCT f.id, f.name FROM farmers f JOIN crops c ON f.id = c.farmer_id");
$farmers = $farmerStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }

        .navbar {
            background-color: #2c3e50;
            padding: 15px 20px;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ffffff !important;
        }
        .navbar-nav .nav-link {
            color: #ffffff !important;
            font-size: 1rem;
            margin-right: 15px;
        }
        .navbar-nav .nav-link:hover {
            color: #f1c40f !important;
        }

        .hero-section {
            background: linear-gradient(to right, #2c3e50, #4a69bd);
            color: white;
            padding: 80px;
            text-align: center;
        }

        .card {
            border: none;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
            border-radius: 10px;
            overflow: hidden;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.2);
        }
        .card-header {
            background: #4a69bd;
            color: white;
            font-weight: bold;
            font-size: 1.25rem;
        }

        .btn-primary {
            background-color: #4a69bd;
            border: none;
        }
        .btn-primary:hover {
            background-color: #1e3799;
        }

        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="hero-section">
    <h1>Welcome to GreenHarvest</h1>
    <p>Order fresh and organic produce directly from farmers</p>
</div>

<div class="container mt-4">
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php foreach ($farmers as $farmer): ?>
        <div class="card my-4">
            <div class="card-header">
                Farmer: <?= htmlspecialchars($farmer['name']) ?> 
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $cropStmt = $conn->prepare("SELECT * FROM crops WHERE farmer_id = ?");
                    $cropStmt->execute([$farmer['id']]);
                    $crops = $cropStmt->fetchAll();

                    if (count($crops) > 0):
                        foreach ($crops as $crop):
                    ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <?php if (!empty($crop['image'])): ?>
                                    <img src="<?= htmlspecialchars($crop['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($crop['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($crop['name']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($crop['description']) ?></p>
                                    <p><strong>Price:</strong> ₹<?= $crop['price_per_kg'] ?>/kg</p>
                                    <form method="POST">
                                        <input type="hidden" name="crop_id" value="<?= $crop['id'] ?>">
                                        <div class="form-group">
                                            <label>Quantity (kg):</label>
                                            <input type="number" name="quantity" min="1" required class="form-control">
                                        </div>
                                   <button type="submit" class="btn btn-primary btn-block mt-2">Add to cart</button>

                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php
                        endforeach;
                    else:
                        echo "<p class='mx-3'>No crops available for this farmer.</p>";
                    endif;
                    ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="footer">
    &copy; 2025 GreenHarvest - Connecting Farmers & Consumers
</div>

</body>
</html>
