<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch customer details
$stmt = $conn->prepare("SELECT name, city FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

// Fetch crops information for customer
$stmt = $conn->prepare("SELECT c.id, c.name AS crop_name, c.description, c.price_per_kg, c.season, c.certificate_available, f.name AS farmer_name, c.video, f.id AS farmer_id FROM crops c JOIN farmers f ON c.farmer_id = f.id");
$stmt->execute();
$crops = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* Global Theme */
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }

        /* Navbar */
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

        /* Hero Section */
        .hero-section {
            background: linear-gradient(to right, #2c3e50, #4a69bd);
            color: white;
            padding: 80px;
            text-align: center;
        }

        /* Cards */
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
            text-align: center;
            font-size: 1.5rem;
        }

        /* Buttons */
        .btn-primary {
            background-color: #4a69bd;
            border: none;
        }
        .btn-primary:hover {
            background-color: #1e3799;
        }
        .btn-success {
            background-color: #38ada9;
            border: none;
        }
        .btn-success:hover {
            background-color: #079992;
        }

        /* Footer */
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

<?php include "navbar.php" ?>
<!-- Hero Section -->
<div class="hero-section">
    <h1>Welcome, <?php echo htmlspecialchars($customer['name']); ?> !</h1>
    <p>Explore organic and fresh crops directly from trusted farmers.</p>
</div>

<!-- Main Content -->
<div class="container-fluied mx-4 mt-5">
    <div class="row">
        <?php if (count($crops) > 0): ?>
            <?php foreach ($crops as $crop): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <?php echo htmlspecialchars($crop['crop_name']); ?>
                        </div>
                        <div class="card-body">
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($crop['description']); ?></p>
                            <p><strong>Price per KG:</strong> ₹<?php echo htmlspecialchars($crop['price_per_kg']); ?></p>
                            <p><strong>Season:</strong> <?php echo htmlspecialchars($crop['season']); ?></p>
                            <p><strong>Farmer:</strong> <?php echo htmlspecialchars($crop['farmer_name']); ?></p>
                            <p><strong>Certified:</strong> <?php echo $crop['certificate_available'] ? 'Yes' : 'No'; ?></p>
                            <div class="d-grid gap-2">
                                <a href="order.php?crop_id=<?php echo $crop['id']; ?>" class="btn btn-primary">Book a Crop</a>
                                <a href="farm_visit_form.php?farmer_id=<?php echo $crop['farmer_id']; ?>" class="btn btn-success">Request Farm Visit</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center w-100">No crops available at the moment.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    &copy; 2025 GreenHarvest - Connecting Farmers & Consumers
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
