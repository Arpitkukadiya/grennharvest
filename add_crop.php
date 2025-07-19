<?php
include 'config.php';
session_start();

if (!isset($_SESSION['farmer_id'])) {
    header('Location: farmer_login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price_per_kg = $_POST['price_per_kg'];
    $season = $_POST['season'];
    $insurance_status = isset($_POST['insurance_status']) ? 1 : 0;
    $certificate_available = isset($_POST['certificate_available']) ? 1 : 0;
    $farmer_id = $_SESSION['farmer_id'];

    // Video upload handling
    $video_url = null;
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $target_dir = "uploads/videos/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $video_name = preg_replace("/[^a-zA-Z0-9\-_\.]/", "_", basename($_FILES['video']['name']));
        $target_file = $target_dir . $video_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (in_array($file_type, ['mp4', 'avi', 'mov', 'mkv'])) {
            if (move_uploaded_file($_FILES['video']['tmp_name'], $target_file)) {
                $video_url = $target_file;
            } else {
                echo "<script>alert('Error uploading video.');</script>";
            }
        } else {
            echo "<script>alert('Only MP4, AVI, MOV, MKV files are allowed.');</script>";
        }
    }

    $stmt = $conn->prepare("INSERT INTO crops (name, description, price_per_kg, season, farmer_id, video, insurance_status, certificate_available) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $description, $price_per_kg, $season, $farmer_id, $video_url, $insurance_status, $certificate_available]);

    echo "<script>alert('Crop added successfully!'); window.location.href='farmer_dashboard.php';</script>";
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Crop</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"/>
  <style>
    body {
      background-color: #f4f7f8;
      font-family: 'Roboto', sans-serif;
    }

    /* Sidebar for Desktop */
    .sidebar-desktop {
      background-color: #1a1a1a;
      color: white;
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      width: 250px;
      padding: 20px;
      overflow-y: auto;
      z-index: 1000;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
    }

    .sidebar-desktop h4 {
      text-align: center;
      font-size: 1.5rem;
      margin-bottom: 30px;
    }

    .sidebar-desktop a {
      display: block;
      padding: 12px 20px;
      margin-bottom: 10px;
      color: #dcdcdc;
      font-size: 1rem;
      text-decoration: none;
      border-radius: 4px;
      transition: 0.3s;
    }

    .sidebar-desktop a:hover,
    .sidebar-desktop a.active {
      background: linear-gradient(45deg, #007bff, #0056b3);
      color: #fff;
    }

    /* Hide sidebar on mobile */
    @media (max-width: 767.98px) {
      .sidebar-desktop {
        display: none;
      }
    }

    /* Content area */
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

    /* Form Card */
    .card {
      max-width: 600px;
      margin: auto;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .card-header {
      background-color: #28a745;
      color: white;
      font-size: 18px;
      text-align: center;
      border-radius: 10px 10px 0 0;
    }

    .form-group label {
      font-weight: 600;
      color: #333;
    }

    .form-control {
      border-radius: 8px;
      border: 1px solid #ced4da;
      transition: 0.3s;
    }

    .form-control:focus {
      border-color: #28a745;
      box-shadow: none;
    }

    /* Toggle Switch */
    .toggle-switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 24px;
    }

    .toggle-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: 0.4s;
      border-radius: 24px;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 4px;
      bottom: 3px;
      background-color: white;
      transition: 0.4s;
      border-radius: 50%;
    }

    input:checked + .slider {
      background-color: #28a745;
    }

    input:checked + .slider:before {
      transform: translateX(26px);
    }

    /* Submit Button */
    .btn-submit {
      background-color: #28a745;
      color: white;
      font-weight: bold;
      padding: 10px;
      width: 100%;
      border-radius: 8px;
      transition: 0.3s;
    }

    .btn-submit:hover {
      background-color: #218838;
    }
  </style>
</head>
<body>

<!-- Sidebar for Desktop -->
<div class="sidebar-desktop d-none d-md-block">
  <h4>Farmer Dashboard</h4>
  <a href="farmer_dashboard.php">Dashboard</a>
  <a href="add_crop.php" class="active">Add Crop</a>
  <a href="manage_orders.php">Manage Orders</a>
  <a href="farm_visits.php">Farm Visits</a>
  <a href="view_crops.php">View Crops</a>
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
      <li class="nav-item"><a class="nav-link" href="view_crops.php">View Crops</a></li>
      <li class="nav-item"><a class="nav-link" href="feedback_view.php">View Feedback</a></li>
      <li class="nav-item"><a class="btn btn-danger btn-sm mt-2" href="logout.php">Logout</a></li>
    </ul>
  </div>
</nav>

<!-- Main Content -->
<div class="content-area">
  <div class="container">
    <div class="card mt-4">
      <div class="card-header">Add a New Crop</div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label for="name">Crop Name</label>
            <input type="text" id="name" name="name" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" required></textarea>
          </div>
          <div class="form-group">
            <label for="price_per_kg">Price per kg (₹)</label>
            <input type="number" id="price_per_kg" name="price_per_kg" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="season">Season</label>
            <input type="text" id="season" name="season" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="video">Upload Crop Video (optional)</label>
            <input type="file" id="video" name="video" class="form-control">
          </div>

          <div class="form-group">
            <label class="d-block">Has Insurance?
              <label class="toggle-switch ml-2">
                <input type="checkbox" name="insurance_status">
                <span class="slider"></span>
              </label>
            </label>
          </div>

          <div class="form-group">
            <label class="d-block">Has Certification?
              <label class="toggle-switch ml-2">
                <input type="checkbox" name="certificate_available">
                <span class="slider"></span>
              </label>
            </label>
          </div>

          <button type="submit" class="btn btn-submit mt-3">Add Crop</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
