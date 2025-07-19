<!-- farm_visits.php -->
<?php
include 'config.php';
session_start();

if (!isset($_SESSION['farmer_id'])) {
    header('Location: farmer_login.php');
    exit();
}

if (isset($_POST['update_status'])) {
    $visit_id = $_POST['visit_id'];
    $status = $_POST['status'];
    
    // Use try-catch for error handling
    try {
        $stmt = $conn->prepare("UPDATE farm_visits SET status = ? WHERE id = ?");
        $stmt->execute([$status, $visit_id]);
        
        // Refresh page after updating status
        header("Location: farm_visits.php");
        exit();
    } catch (PDOException $e) {
        // Error message if update fails
        echo "Error updating status: " . $e->getMessage();
    }
}

$stmt = $conn->prepare("SELECT * FROM farm_visits WHERE farmer_id = ?");
$stmt->execute([$_SESSION['farmer_id']]);
$visits = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Visits</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
 <style>
  body {
    background-color: #f4f7f8;
    font-family: 'Roboto', sans-serif;
  }

  /* Sidebar (desktop only) */
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
    overflow-y: auto;
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

  .table thead {
    background-color: #007bff;
    color: white;
  }

  /* Card View */
  .visit-cards {
    display: none;
  }

  @media (max-width: 767.98px) {
    .visit-table {
      display: none;
    }
    .visit-cards {
      display: block;
    }
  }

  .visit-card {
    background: white;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
  }

  .visit-card h5 {
    font-size: 1.1rem;
    margin-bottom: 10px;
    color: #007bff;
  }

  .visit-card p {
    margin-bottom: 6px;
    font-size: 0.95rem;
  }

  .visit-card form {
    margin-top: 10px;
  }
</style>

<!-- Sidebar for Desktop -->
<div class="sidebar-desktop d-none d-md-block">
  <h4>Farmer Dashboard</h4>
  <a href="farmer_dashboard.php">Dashboard</a>
  <a href="add_crop.php">Add Crop</a>
  <a href="manage_orders.php">Manage Orders</a>
  <a href="farm_visits.php" class="active">Farm Visits</a>
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
      <li class="nav-item"><a class="nav-link active" href="farm_visits.php">Farm Visits</a></li>
      <li class="nav-item"><a class="nav-link" href="view_crops.php">View Crops</a></li>
      <li class="nav-item"><a class="nav-link" href="feedback_view.php">View Feedback</a></li>
      <li class="nav-item"><a class="btn btn-danger btn-sm mt-2" href="logout.php">Logout</a></li>
    </ul>
  </div>
</nav>

<!-- Main Content -->
<div class="content-area">
  <div class="container">
    <h2 class="mb-4 text-center">Upcoming Farm Visits</h2>

    <!-- Desktop Table View -->
    <div class="visit-table">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Customer</th>
            <th>Status</th>
            <th>Update Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($visits as $visit): ?>
            <tr>
              <td><?= $visit['date']; ?></td>
              <td><?= $visit['description']; ?></td>
              <td>
                <?php
                $stmt_customer = $conn->prepare("SELECT name FROM customers WHERE id = ?");
                $stmt_customer->execute([$visit['customer_id']]);
                $customer = $stmt_customer->fetch();
                echo $customer ? $customer['name'] : 'N/A';
                ?>
              </td>
              <td><?= $visit['status']; ?></td>
              <td>
                <form method="POST">
                  <input type="hidden" name="visit_id" value="<?= $visit['id']; ?>">
                  <select name="status" class="form-control" required>
                    <option value="requested" <?= $visit['status'] == 'requested' ? 'selected' : ''; ?>>Requested</option>
                    <option value="approved" <?= $visit['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?= $visit['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                  </select>
                  <button type="submit" name="update_status" class="btn btn-primary btn-sm mt-2">Update</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Mobile Card View -->
    <div class="visit-cards">
      <?php foreach ($visits as $visit): ?>
        <div class="visit-card">
          <h5><?= $visit['description']; ?></h5>
          <p><strong>Date:</strong> <?= $visit['date']; ?></p>
          <p><strong>Customer:</strong>
            <?php
              $stmt_customer = $conn->prepare("SELECT name FROM customers WHERE id = ?");
              $stmt_customer->execute([$visit['customer_id']]);
              $customer = $stmt_customer->fetch();
              echo $customer ? $customer['name'] : 'N/A';
            ?>
          </p>
          <p><strong>Status:</strong> <?= $visit['status']; ?></p>
          <form method="POST">
            <input type="hidden" name="visit_id" value="<?= $visit['id']; ?>">
            <select name="status" class="form-control" required>
              <option value="requested" <?= $visit['status'] == 'requested' ? 'selected' : ''; ?>>Requested</option>
              <option value="approved" <?= $visit['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
              <option value="rejected" <?= $visit['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
            <button type="submit" name="update_status" class="btn btn-primary btn-sm mt-2">Update</button>
          </form>
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
