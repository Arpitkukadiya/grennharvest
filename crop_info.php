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
    <title>Available Crops.</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f7f8;
            font-family: 'Roboto', sans-serif;
        }

        .sidebar {
            background-color: #1a1a1a;
            color: white;
            position: fixed;
            height: 100vh;
            width: 250px;
            top: 0;
            left: 0;
            padding-top: 20px;
        }

        .sidebar a {
            color: #dcdcdc;
            font-size: 1rem;
            padding: 10px 20px;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .sidebar a:hover {
            background-color: #007bff;
            color: white;
        }

        .content {
            margin-left: 270px;
            padding: 20px;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .card-header {
            background-color: #007bff;
            color: white;
        }

        .crop-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h4 class="text-center">Customer Dashboard</h4>
    <a href="customer_dashboard.php">Dashboard</a>
    <a href="customer_profile.php">Profile</a>
    <a href="order_history.php">Order History</a>
    <a href="visit_history.php">visit History</a>
    <a href="crop_info.php">Available Crops</a> <a href="customer_feedback.php">Give Feedback</a>
    <a href="logout.php" class="btn btn-danger text-center">Logout</a>
</div>

<!-- Content -->
<div class="content">
    <div class="container">
        <h2>Available Crops</h2>

        <?php if (count($crops) > 0): ?>
            <?php foreach ($crops as $crop): ?>
                <div class="card crop-card">
                    <div class="card-header">
                        <h5><?php echo $crop['crop_name']; ?></h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Description:</strong> <?php echo $crop['description']; ?></p>
                        <p><strong>Price per KG:</strong> ₹<?php echo $crop['price_per_kg']; ?></p>
                        <p><strong>Season:</strong> <?php echo $crop['season']; ?></p>
                        <p><strong>Farmer:</strong> <?php echo $crop['farmer_name']; ?></p>
                        <p><strong>Certificate Available:</strong> <?php echo $crop['certificate_available'] ? 'Yes' : 'No'; ?></p>
                        <a href="order.php?crop_id=<?php echo $crop['id']; ?>" class="btn btn-primary">Book a Crop</a>
                        <a href="farm_visit_form.php?farmer_id=<?php echo $crop['farmer_id']; ?>" class="btn btn-success">Request Farm Visit</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No crops available at the moment.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
