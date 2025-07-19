<?php
include 'config.php';
session_start();

if (!isset($_SESSION['farmer_id'])) {
    header('Location: farmer_login.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM farmers WHERE id = ?");
$stmt->execute([$_SESSION['farmer_id']]);
$farmer = $stmt->fetch();

$cropsStmt = $conn->prepare("SELECT * FROM crops WHERE farmer_id = ?");
$cropsStmt->execute([$_SESSION['farmer_id']]);
$crops = $cropsStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Farmer Dashboard</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"/>
  <style>
    body {
      background-color: #f4f7f8;
      font-family: 'Roboto', sans-serif;
    }

    .navbar-dark {
      background-color: #1a1a1a;
    }

    .crop-card {
      border: 1px solid #ddd;
      padding: 15px;
      margin-bottom: 15px;
      border-radius: 10px;
      background: #fff;
      box-shadow: 0px 0px 10px rgba(0,0,0,0.05);
    }

    @media (min-width: 768px) {
      .crop-table {
        display: block;
      }

      .crop-cards {
        display: none;
      }
    }

    @media (max-width: 767.98px) {
      .crop-table {
        display: none;
      }

      .crop-cards {
        display: block;
      }
    }
 /* Sidebar styles for computer view */
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

/* Adjust page content margin on desktop */
@media (min-width: 768px) {
  .content-area {
    margin-left: 270px;
    padding: 20px;
  }
}


  </style>
</head>
<body>

<!-- Sidebar visible only on desktop -->
<div class="sidebar-desktop d-none d-md-block">
  <h4>Farmer Dashboard</h4>
  <a href="farmer_dashboard.php">Dashboard</a>
  <a href="add_crop.php">Add Crop</a>
  <a href="manage_orders.php">Manage Orders</a>
  <a href="farm_visits.php">Farm Visits</a>
  <a href="view_crops.php">View Crops</a>
  <a href="feedback_view.php">View Feedback</a>
  <a href="logout.php" class="btn btn-danger mt-3 w-100">Logout</a>
</div>


<!-- Top Navbar for Mobile -->
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



<!-- Content -->
 <div class="content-area">

<div class="container-fluid mt-4">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
    <h2>Welcome, <?php echo $farmer['name']; ?>!</h2>
    <button class="btn btn-primary mt-2 mt-md-0" data-toggle="modal" data-target="#farmerProfileModal">View Profile</button>
  </div>

  <!-- Farmer Profile Modal -->
  <div class="modal fade" id="farmerProfileModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Farmer Profile</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <p><strong>Name:</strong> <?php echo $farmer['name']; ?></p>
          <p><strong>Email:</strong> <?php echo $farmer['email']; ?></p>
          <p><strong>Location:</strong> <?php echo $farmer['location']; ?></p>
          <p><strong>Bio:</strong> <?php echo $farmer['bio']; ?></p>
          <p><strong>Certification Status:</strong> <?php echo $farmer['certification_status'] ? 'Certified' : 'Not Certified'; ?></p>
        </div>
      </div>
    </div>
  </div>

  <h3 class="mb-4 mt-3">Your Crops</h3>

  <!-- Table (Desktop View) -->
  <div class="table-responsive crop-table">
    <table class="table table-bordered table-striped">
      <thead class="thead-dark">
        <tr>
          <th>Name</th>
          <th>Description</th>
          <th>Price (₹/kg)</th>
          <th>Season</th>
          <th>Insurance</th>
          <th>Certified</th>
          <th>Video</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($crops as $crop): ?>
        <tr>
          <td><?php echo $crop['name']; ?></td>
          <td><?php echo $crop['description']; ?></td>
          <td><?php echo $crop['price_per_kg']; ?></td>
          <td><?php echo $crop['season']; ?></td>
          <td><?php echo $crop['insurance_status'] ? 'Yes' : 'No'; ?></td>
          <td><?php echo $crop['certificate_available'] ? 'Yes' : 'No'; ?></td>
          <td>
            <?php if ($crop['video']): ?>
              <a href="#" data-toggle="modal" data-target="#videoModal<?php echo $crop['id']; ?>">View</a>
            <?php else: ?>
              <span class="text-muted">No video</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="edit_crop.php?id=<?php echo $crop['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
            <a href="delete_crop.php?id=<?php echo $crop['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
            <a href="send_crop_email.php?crop_id=<?php echo $crop['id']; ?>" class="btn btn-info btn-sm" onclick="return confirm('Send this crop details to all customers?')">Send Email</a>
          </td>
        </tr>
        <!-- Video Modal -->
        <div class="modal fade" id="videoModal<?php echo $crop['id']; ?>" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Crop Video</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                <video controls style="width:100%; height:300px; object-fit:cover; border-radius:6px;">
                  <source src="<?php echo $crop['video']; ?>" type="video/mp4">
                </video>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Cards (Mobile View) -->
  <div class="crop-cards">
    <?php foreach ($crops as $crop): ?>
    <div class="crop-card">
      <h5><?php echo $crop['name']; ?></h5>
      <p><strong>Description:</strong> <?php echo $crop['description']; ?></p>
      <p><strong>Price:</strong> ₹<?php echo $crop['price_per_kg']; ?> /kg</p>
      <p><strong>Season:</strong> <?php echo $crop['season']; ?></p>
      <p><strong>Insurance:</strong> <?php echo $crop['insurance_status'] ? 'Yes' : 'No'; ?></p>
      <p><strong>Certified:</strong> <?php echo $crop['certificate_available'] ? 'Yes' : 'No'; ?></p>
      <p><strong>Video:</strong> 
        <?php if ($crop['video']): ?>
          <a href="#" data-toggle="modal" data-target="#videoModal<?php echo $crop['id']; ?>">View</a>
        <?php else: ?>
          <span class="text-muted">No video</span>
        <?php endif; ?>
      </p>
      <div class="d-flex justify-content-between flex-wrap">
        <a href="edit_crop.php?id=<?php echo $crop['id']; ?>" class="btn btn-warning btn-sm mb-1">Edit</a>
        <a href="delete_crop.php?id=<?php echo $crop['id']; ?>" class="btn btn-danger btn-sm mb-1">Delete</a>
        <a href="send_crop_email.php?crop_id=<?php echo $crop['id']; ?>" class="btn btn-info btn-sm mb-1" onclick="return confirm('Send this crop details to all customers?')">Send Email</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Crop Videos Grid -->
  <h3 class="mb-4 mt-5">Crop Videos</h3>
  <div class="row">
    <?php foreach ($crops as $crop): ?>
      <?php if (!empty($crop['video'])): ?>
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title"><?php echo $crop['name']; ?></h5>
              <p class="card-text"><strong>Season:</strong> <?php echo $crop['season']; ?></p>
              <p class="card-text"><strong>Price:</strong> ₹<?php echo $crop['price_per_kg']; ?> /kg</p>
              <video controls style="width:100%; height:250px; object-fit:cover; border-radius:6px;">
                <source src="<?php echo $crop['video']; ?>" type="video/mp4">
              </video>
            </div>
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
