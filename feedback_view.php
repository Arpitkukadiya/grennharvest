<?php
session_start();
include 'config.php';

// Redirect if the farmer is not logged in
if (!isset($_SESSION['farmer_id'])) {
    header('Location: farmer_login.php');
    exit();
}

$farmer_id = $_SESSION['farmer_id'];

try {
    // Fetch feedback only for the logged-in farmer
    $stmt = $conn->prepare("
        SELECT f.id AS feedback_id, f.rating, f.comment, f.order_id, 
               c.name AS customer_name, o.order_date
        FROM feedback f
        JOIN orders o ON f.order_id = o.id
        JOIN customers c ON f.customer_id = c.id
        JOIN crops cr ON o.crop_id = cr.id
        WHERE cr.farmer_id = :farmer_id
    ");
    $stmt->bindParam(':farmer_id', $farmer_id, PDO::PARAM_INT);
    $stmt->execute();
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching feedback: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Feedback</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
     body {
  background-color: #f4f7f8;
  font-family: 'Roboto', sans-serif;
}

/* Sidebar (desktop) */
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

.content-area {
  padding: 20px;
}

@media (min-width: 768px) {
  .content-area {
    margin-left: 270px;
  }
}

/* Table (desktop) */
.table thead {
  background-color: #007bff;
  color: white;
}

/* Mobile Card View */
.feedback-cards {
  display: none;
}

@media (max-width: 767.98px) {
  .feedback-table {
    display: none;
  }

  .feedback-cards {
    display: block;
  }

  .feedback-card {
    background: white;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
  }

  .feedback-card h5 {
    font-size: 1.1rem;
    color: #007bff;
    margin-bottom: 10px;
  }

  .feedback-card p {
    margin-bottom: 6px;
    font-size: 0.95rem;
  }

  .rating-stars {
    color: gold;
  }
}

    </style>
</head>
<body><!-- Sidebar (Desktop Only) -->
<div class="sidebar-desktop d-none d-md-block">
  <h4>Farmer Dashboard</h4>
  <a href="farmer_dashboard.php">Dashboard</a>
  <a href="add_crop.php">Add Crop</a>
  <a href="manage_orders.php">Manage Orders</a>
  <a href="farm_visits.php">Farm Visits</a>
  <a href="view_crops.php">View Crops</a>
  <a href="feedback_view.php" class="active">View Feedback</a>
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
      <li class="nav-item"><a class="nav-link active" href="feedback_view.php">View Feedback</a></li>
      <li class="nav-item"><a class="btn btn-danger btn-sm mt-2" href="logout.php">Logout</a></li>
    </ul>
  </div>
</nav>

<!-- Content -->
<div class="content-area">
  <div class="container">
    <h2 class="mb-4 text-center">Feedback from Customers</h2>

    <!-- Desktop Table View -->
    <div class="feedback-table">
      <?php if (!empty($feedbacks)): ?>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer Name</th>
              <th>Order Date</th>
              <th>Rating</th>
              <th>Comment</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($feedbacks as $feedback): ?>
              <tr>
                <td><?= htmlspecialchars($feedback['order_id']) ?></td>
                <td><?= htmlspecialchars($feedback['customer_name']) ?></td>
                <td><?= htmlspecialchars($feedback['order_date']) ?></td>
                <td class="rating-stars">
                  <?= str_repeat('★', $feedback['rating']) ?>
                  <?= str_repeat('☆', 5 - $feedback['rating']) ?>
                </td>
                <td><?= nl2br(htmlspecialchars($feedback['comment'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No feedback available yet.</p>
      <?php endif; ?>
    </div>

    <!-- Mobile Card View -->
    <div class="feedback-cards">
      <?php foreach ($feedbacks as $feedback): ?>
        <div class="feedback-card">
          <h5>Order #<?= htmlspecialchars($feedback['order_id']) ?></h5>
          <p><strong>Customer:</strong> <?= htmlspecialchars($feedback['customer_name']) ?></p>
          <p><strong>Date:</strong> <?= htmlspecialchars($feedback['order_date']) ?></p>
          <p><strong>Rating:</strong> 
            <span class="rating-stars">
              <?= str_repeat('★', $feedback['rating']) ?>
              <?= str_repeat('☆', 5 - $feedback['rating']) ?>
            </span>
          </p>
          <p><strong>Comment:</strong><br> <?= nl2br(htmlspecialchars($feedback['comment'])) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
