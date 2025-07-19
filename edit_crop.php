<?php
include 'config.php';
session_start();

if (!isset($_SESSION['farmer_id'])) {
    header('Location: farmer_login.php');
    exit();
}

if (isset($_GET['id'])) {
    $crop_id = $_GET['id'];

    // Get the crop details from the database
    $stmt = $conn->prepare("SELECT * FROM crops WHERE id = ? AND farmer_id = ?");
    $stmt->execute([$crop_id, $_SESSION['farmer_id']]);
    $crop = $stmt->fetch();

    if (!$crop) {
        header('Location: view_crops.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price_per_kg = $_POST['price_per_kg'];
    $season = $_POST['season'];
    $video = $crop['video']; // Default video value (if no new file uploaded)

    // Check if a new video file is uploaded
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $video_dir = 'uploads/videos/';
        $video_name = basename($_FILES['video']['name']);
        $video_path = $video_dir . $video_name;

        // Move the uploaded file to the server's directory
        if (move_uploaded_file($_FILES['video']['tmp_name'], $video_path)) {
            $video = $video_path; // Set the new video path
        } else {
            $error_message = "Failed to upload video.";
        }
    }

    // Update the crop details
    $stmt = $conn->prepare("UPDATE crops SET name = ?, description = ?, price_per_kg = ?, season = ?, video = ? WHERE id = ? AND farmer_id = ?");
    $stmt->execute([$name, $description, $price_per_kg, $season, $video, $crop_id, $_SESSION['farmer_id']]);

    header('Location: view_crops.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Crop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            padding-left: 15px;
        }

        .sidebar h4 {
            font-size: 1.5rem;
            text-align: center;
            color: white;
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            color: #ccc;
            padding: 10px;
            text-decoration: none;
            font-size: 1.1rem;
            margin-bottom: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #007bff;
            color: white;
        }

        .content {
            margin-left: 260px;
            padding: 20px;
            padding-top: 20px;
        }

        .form-group label {
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h4>Farmer Dashboard</h4>
        <a href="farmer_dashboard.php">Dashboard</a>
        <a href="add_crop.php">Add Crop</a>
        <a href="view_crops.php">View Crops</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="farm_visits.php">Farm Visits</a><a href="feedback_view.php">View Feedback</a>

        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="container">
            <h2>Edit Crop</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Crop Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($crop['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($crop['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="price_per_kg">Price per kg (₹)</label>
                    <input type="number" id="price_per_kg" name="price_per_kg" class="form-control" value="<?php echo htmlspecialchars($crop['price_per_kg']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="season">Season</label>
                    <input type="text" id="season" name="season" class="form-control" value="<?php echo htmlspecialchars($crop['season']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="video">Crop Video</label>
                    <input type="file" id="video" name="video" class="form-control">
                    <?php if ($crop['video']) : ?>
                        <p>Current Video: <a href="<?php echo $crop['video']; ?>" target="_blank">View Video</a></p>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Update Crop</button>
            </form>
        </div>
    </div>

</body>
</html>
