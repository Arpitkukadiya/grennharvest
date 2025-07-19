<?php
include 'config.php';
session_start();

if (!isset($_SESSION['farmer_id'])) {
    header('Location: farmer_login.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM crops WHERE farmer_id = ?");
$stmt->execute([$_SESSION['farmer_id']]);
$crops = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>View Crops</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"/>
  <style>
    body {
      background-color: #f4f7f8;
      font-family: 'Roboto', sans-serif;
    }

    .sidebar-desktop {
      background-color: #1a1a1a;
      color: white;
      position: fixed;
      height: 100vh;
      width: 250px;
      top: 0;
      left: 0;
      padding: 20px;
      z-index: 1000;
    }

    .sidebar-desktop h4 {
      text-align: center;
      font-size: 1.5rem;
      margin-bottom: 30px;
    }

    .sidebar-desktop a {
      color: #dcdcdc;
      font-size: 1rem;
      padding: 10px 20px;
      display: block;
      text-decoration: none;
      margin-bottom: 10px;
      border-radius: 4px;
    }

    .sidebar-desktop a:hover {
      background: linear-gradient(45deg, #007bff, #0056b3);
      color: #fff;
    }

    @media (max-width: 767.98px) {
      .sidebar-desktop {
        display: none;
      }
    }

    @media (min-width: 768px) {
      .content-area {
        margin-left: 270px;
        padding: 20px;
      }
    }

    @media (max-width: 767.98px) {
      .content-area {
        padding: 15px;
      }
    }

    .table thead {
      background: #007bff;
      color: white;
    }

    .table tbody tr:hover {
      background-color: #f1f1f1;
    }

    .crop-cards {
      display: none;
    }

    @media (max-width: 767.98px) {
      .crop-table {
        display: none;
      }

      .crop-cards {
        display: block;
      }

      .crop-card {
        background: #fff;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
      }

      .crop-card h5 {
        font-size: 1.1rem;
        color: #007bff;
        margin-bottom: 10px;
      }

      .crop-card p {
        font-size: 0.95rem;
        margin-bottom: 6px;
      }

      .crop-card .btn {
        font-size: 0.85rem;
        padding: 6px 10px;
        margin-right: 5px;
      }
    }
  </style>
</head>
<body>

<!-- Sidebar (Desktop Only) -->
<div class="sidebar-desktop d-none d-md-block">
  <h4>Farmer Dashboard</h4>
  <a href="farmer_dashboard.php">Dashboard</a>
  <a href="add_crop.php">Add Crop</a>
  <a href="manage_orders.php">Manage Orders</a>
  <a href="farm_visits.php">Farm Visits</a>
  <a href="view_crops.php" class="active">View Crops</a>
  <a href="feedback_view.php">View Feedback</a>
  <a href="logout.php" class="btn btn-danger mt-3 w-100">Logout</a>
</div>

<!-- Mobile Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark d-md-none">
  <a class="navbar-brand" href="#">Farmer Dashboard</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mobileNav">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="mobileNav">
    <ul class="navbar-nav ml-auto">
      <li class="nav-item"><a class="nav-link" href="farmer_dashboard.php">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" href="add_crop.php">Add Crop</a></li>
      <li class="nav-item"><a class="nav-link" href="manage_orders.php">Manage Orders</a></li>
      <li class="nav-item"><a class="nav-link" href="farm_visits.php">Farm Visits</a></li>
      <li class="nav-item"><a class="nav-link active" href="view_crops.php">View Crops</a></li>
      <li class="nav-item"><a class="nav-link" href="feedback_view.php">View Feedback</a></li>
      <li class="nav-item"><a class="btn btn-danger btn-sm mt-2" href="logout.php">Logout</a></li>
    </ul>
  </div>
</nav>

<!-- Content -->
<div class="content-area">
  <div class="container">
    <h2 class="mb-4 text-center">Your Crops</h2>

    <!-- Desktop Table -->
    <div class="table-responsive crop-table">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Crop Name</th>
            <th>Description</th>
            <th>Price/kg (₹)</th>
            <th>Season</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($crops as $crop): ?>
            <tr>
              <td><?= htmlspecialchars($crop['name']) ?></td>
              <td><?= htmlspecialchars($crop['description']) ?></td>
              <td>₹<?= htmlspecialchars($crop['price_per_kg']) ?></td>
              <td><?= htmlspecialchars($crop['season']) ?></td>
              <td>
                <a href="edit_crop.php?id=<?= $crop['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="delete_crop.php?id=<?= $crop['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Mobile Cards -->
    <div class="crop-cards">
      <?php foreach ($crops as $crop): ?>
        <div class="crop-card">
          <h5><?= htmlspecialchars($crop['name']) ?></h5>
          <p><strong>Description:</strong> <?= htmlspecialchars($crop['description']) ?></p>
          <p><strong>Price:</strong> ₹<?= htmlspecialchars($crop['price_per_kg']) ?> /kg</p>
          <p><strong>Season:</strong> <?= htmlspecialchars($crop['season']) ?></p>
          <div>
            <a href="edit_crop.php?id=<?= $crop['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
            <a href="delete_crop.php?id=<?= $crop['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Scripts (required for navbar toggle) -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
