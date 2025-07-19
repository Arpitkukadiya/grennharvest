<?php
include 'config.php';
session_start();

if (!isset($_SESSION['farmer_id'])) {
    header('Location: farmer_login.php');
    exit();
}

// ✅ Update order status
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    try {
        $check_stmt = $conn->prepare("SELECT o.id FROM orders o JOIN crops cr ON o.crop_id = cr.id WHERE o.id = ? AND cr.farmer_id = ?");
        $check_stmt->execute([$order_id, $_SESSION['farmer_id']]);
        $order = $check_stmt->fetch();

        if ($order) {
            $valid_statuses = ['pending', 'confirmed', 'shipped', 'delivered'];
            if (in_array($status, $valid_statuses)) {
                $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$status, $order_id]);
                $_SESSION['message'] = "Order status updated successfully!";
            } else {
                $_SESSION['error'] = "Invalid status.";
            }
        } else {
            $_SESSION['error'] = "Unauthorized order.";
        }
        header("Location: manage_orders.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Update error: " . $e->getMessage();
        header("Location: manage_orders.php");
        exit();
    }
}

// ✅ Delete order
if (isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];

    try {
        $check_stmt = $conn->prepare("SELECT o.id FROM orders o JOIN crops cr ON o.crop_id = cr.id WHERE o.id = ? AND cr.farmer_id = ?");
        $check_stmt->execute([$order_id, $_SESSION['farmer_id']]);
        $order = $check_stmt->fetch();

        if ($order) {
            // Delete payment first (if exists)
            $conn->prepare("DELETE FROM payments WHERE order_id = ?")->execute([$order_id]);
            $conn->prepare("DELETE FROM orders WHERE id = ?")->execute([$order_id]);
            $_SESSION['message'] = "Order deleted successfully!";
        } else {
            $_SESSION['error'] = "Unauthorized delete attempt.";
        }
        header("Location: manage_orders.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Delete error: " . $e->getMessage();
        header("Location: manage_orders.php");
        exit();
    }
}

// ✅ Fetch orders with payment info
try {
    $stmt = $conn->prepare("
        SELECT o.id, o.quantity, o.total_price, o.status, o.order_date,
               o.payment_method,
               p.razorpay_payment_id, p.status AS payment_status,
               u.name AS customer_name, u.id AS customer_id,
               cr.name AS crop_name
        FROM orders o
        JOIN crops cr ON o.crop_id = cr.id
        JOIN customers u ON o.customer_id = u.id
        LEFT JOIN payments p ON o.id = p.order_id
        WHERE cr.farmer_id = ?
    ");
    $stmt->execute([$_SESSION['farmer_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching orders: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Orders</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"/>
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
      margin-bottom: 20px;
      font-size: 1.5rem;
    }

    .sidebar-desktop a {
      color: #dcdcdc;
      font-size: 1rem;
      padding: 12px 20px;
      text-decoration: none;
      display: block;
      margin-bottom: 10px;
      border-radius: 5px;
      transition: background 0.3s;
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

    .btn-primary {
      background-color: #007bff;
      border: none;
    }

    .btn-primary:hover {
      background-color: #0056b3;
    }

    .btn-danger {
      background-color: #dc3545;
      border: none;
    }

    .btn-danger:hover {
      background-color: #bd2130;
    }

    .table-responsive {
      overflow-x: auto;
    }

    /* Hide card view on desktop */
.order-cards { display: none; }

@media (max-width: 767.98px) {
  /* Hide table on mobile */
  .order-table { display: none; }

  /* Show cards on mobile */
  .order-cards { display: block; }

  .order-card {
    background: #fff;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
  }

  .order-card h5 {
    font-size: 1.1rem;
    color: #007bff;
    margin-bottom: 10px;
  }

  .order-card p {
    margin-bottom: 6px;
    font-size: 0.95rem;
  }

  .order-card form {
    margin-top: 10px;
  }

  .order-card select,
  .order-card button {
    width: 100%;
    margin-bottom: 8px;
  }
}

  </style>
</head>
<body>

<!-- Sidebar (desktop only) -->
<div class="sidebar-desktop d-none d-md-block">
  <h4>Farmer Dashboard</h4>
  <a href="farmer_dashboard.php">Dashboard</a>
  <a href="add_crop.php">Add Crop</a>
  <a href="manage_orders.php" class="active">Manage Orders</a>
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
    <h2 class="text-center mb-4">Manage Orders</h2>

    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

<!-- ✅ Desktop Table -->
<div class="table-responsive order-table">
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Customer ID</th>
        <th>Customer Name</th>
        <th>Crop Name</th>
        <th>Quantity</th>
        <th>Total Price</th>
        <th>Order Date</th>
        <th>Payment Method</th>
        <th>Payment ID</th>
        <th>Status</th>
        <th>Update</th>
        <th>Delete</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $order): ?>
      <tr>
        <td><?= htmlspecialchars($order['customer_id']) ?></td>
        <td><?= htmlspecialchars($order['customer_name']) ?></td>
        <td><?= htmlspecialchars($order['crop_name']) ?></td>
        <td><?= htmlspecialchars($order['quantity']) ?> kg</td>
        <td>₹<?= htmlspecialchars($order['total_price']) ?></td>
        <td><?= htmlspecialchars($order['order_date']) ?></td>
        <td><?= strtoupper($order['payment_method']) ?></td>
        <td>
          <?= ($order['payment_method'] === 'online' && $order['razorpay_payment_id'] && $order['payment_status'] === 'success') ? htmlspecialchars($order['razorpay_payment_id']) : 'N/A'; ?>
        </td>
        <td><?= ucfirst($order['status']) ?></td>
        <td>
          <form method="POST">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <select name="status" class="form-control" required>
              <?php foreach (['pending','confirmed','shipped','delivered'] as $status): ?>
                <option value="<?= $status ?>" <?= $order['status'] == $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" name="update_status" class="btn btn-primary btn-sm mt-2">Update</button>
          </form>
        </td>
        <td>
          <form method="POST" onsubmit="return confirm('Are you sure you want to delete this order?');">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <button type="submit" name="delete_order" class="btn btn-danger btn-sm mt-2">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ✅ Mobile Cards -->
<div class="order-cards">
  <?php foreach ($orders as $order): ?>
    <div class="order-card">
      <h5><?= htmlspecialchars($order['crop_name']) ?> (<?= $order['quantity'] ?> kg)</h5>
      <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?> (ID: <?= $order['customer_id'] ?>)</p>
      <p><strong>Price:</strong> ₹<?= htmlspecialchars($order['total_price']) ?></p>
      <p><strong>Date:</strong> <?= htmlspecialchars($order['order_date']) ?></p>
      <p><strong>Payment:</strong> <?= strtoupper($order['payment_method']) ?> (<?= $order['razorpay_payment_id'] ?? 'N/A' ?>)</p>
      <p><strong>Status:</strong> <?= ucfirst($order['status']) ?></p>

      <!-- Update Form -->
      <form method="POST">
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
        <select name="status" class="form-control" required>
          <?php foreach (['pending','confirmed','shipped','delivered'] as $status): ?>
            <option value="<?= $status ?>" <?= $order['status'] == $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
      </form>

      <!-- Delete Form -->
      <form method="POST" onsubmit="return confirm('Delete this order?');">
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
        <button type="submit" name="delete_order" class="btn btn-danger btn-sm">Delete</button>
      </form>
    </div>
  <?php endforeach; ?>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
