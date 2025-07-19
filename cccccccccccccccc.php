<?php
include 'config.php';
session_start();

if (!isset($_SESSION['farmer_id'])) {
    header('Location: farmer_login.php');
    exit();
}

// Fetch farm visits from the database for the logged-in farmer
$stmt = $conn->prepare("SELECT * FROM farm_visits WHERE farmer_id = ?");
$stmt->execute([$_SESSION['farmer_id']]);
$visits = $stmt->fetchAll();

// Handle form submission for adding a new farm visit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $available_slots = $_POST['available_slots'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO farm_visits (farmer_id, date, available_slots, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['farmer_id'], $date, $available_slots, $description]);

    // Redirect to the same page to see the updated list
    header('Location: farm_visits.php');
    exit();
}
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

        .card {
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #007bff;
            color: white;
        }

        .card-body {
            background-color: #f8f9fa;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .form-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="text-center">Farmer Dashboard</h4>
        <a href="farmer_dashboard.php">Dashboard</a>
        <a href="add_crop.php">Add Crop</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="farm_visits.php">Farm Visits</a>       
        <a href="view_crops.php">View Crops</a>
        <a href="logout.php" class="btn btn-danger text-center">Logout</a>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="container">
            <h2>Upcoming Farm Visits</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Available Slots</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($visits as $visit): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($visit['date']); ?></td>
                            <td><?php echo htmlspecialchars($visit['available_slots']); ?></td>
                            <td><?php echo htmlspecialchars($visit['description']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add New Farm Visit -->
        <div class="form-container">
            <h3>Add New Farm Visit</h3>
            <form method="POST" action="farm_visits.php">
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" class="form-control" id="date" name="date" required>
                </div>
                <div class="form-group">
                    <label for="available_slots">Available Slots</label>
                    <input type="number" class="form-control" id="available_slots" name="available_slots" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Add Visit</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
