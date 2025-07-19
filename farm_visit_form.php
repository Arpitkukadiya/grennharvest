<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];
$farmer_id = $_GET['farmer_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO farm_visits (farmer_id, customer_id, date, description, status) VALUES (?, ?, ?, ?, 'requested')");
    if ($stmt->execute([$farmer_id, $customer_id, $date, $description])) {
        echo "<script>alert('Farm visit request sent successfully!'); window.location.href='customer_dashboard.php';</script>";
    } else {
        echo "<script>alert('Failed to send farm visit request. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Visit Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        body {
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
            font-family: 'Poppins', sans-serif;
        }

        .container-card {
            max-width: 500px;
            margin: 80px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
            transition: 0.3s ease-in-out;
        }

        h2 {
            color: #333;
            font-weight: 700;
            text-align: center;
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: #444;
        }

        .form-control {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 10px;
            transition: 0.3s ease-in-out;
        }

        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0px 0px 8px rgba(40, 167, 69, 0.3);
        }

        .btn-success {
            background: #28a745;
            border: none;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            border-radius: 6px;
            transition: 0.3s;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            border-radius: 6px;
            margin-top: 10px;
            transition: 0.3s;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>

<?php include "navbar.php" ?>

<div class="container-card">
    <h2>Request a Farm Visit</h2>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="date" class="form-label">Visit Date</label>
            <input type="date" name="date" id="date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Purpose of Visit</label>
            <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Submit Request</button>
        <a href="customer_dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<!-- JavaScript to restrict past dates -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const dateInput = document.getElementById("date");
        const today = new Date().toISOString().split("T")[0];
        dateInput.setAttribute("min", today);
    });
</script>

</body>
</html>
