<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch customer details
$stmt = $conn->prepare("SELECT name, email, city FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $city = $_POST['city'];

    // Update customer details
    $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, city = ? WHERE id = ?");
    $stmt->execute([$name, $email, $city, $customer_id]);

    // Redirect after update
    header('Location: customer_profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        .profile-container {
            display: flex;
            justify-content: center;
        margin-top: 50px;
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 450px;
            width: 100%;
            transition: 0.3s ease-in-out;
        }

     

        .profile-icon {
            width: 120px;
            height: 120px;
           background: linear-gradient(45deg, #28a745, #218838);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 60px;
            color: white;
            margin: 0 auto 20px;
            box-shadow: 0 4px 8px hsla(118, 83%, 47%, 0.20);
        }

        .profile-info {
            font-size: 1.2rem;
            font-weight: 500;
            color: #444;
            text-align: left;
            margin: 8px 0;
            padding: 7px 10px;
            border-radius: 8px;
        }

        .profile-info span {
            font-weight: bold;
            color: rgba(104, 181, 36, 1);
        }

        .edit-btn {
          background: linear-gradient(45deg, #28a745, #218838);
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 15px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

    

     
        .form-control {
            border-radius: 8px;
        }
        .btn-dark{
            background-color: #2c503aff;
        }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="profile-container">
    <div class="profile-card">
<h2 class="mb-3" style="color: #28a745;">
    <b>PROFILE</b>
</h2><hr>
        <div class="profile-icon">
    <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
</div>

        <p class="profile-info"><span>Name:</span> <?php echo htmlspecialchars($customer['name']); ?></p>
        <p class="profile-info"><span>Email:</span> <?php echo htmlspecialchars($customer['email']); ?></p>
        <p class="profile-info"><span>City:</span> <?php echo htmlspecialchars($customer['city']); ?></p>
        <button class="edit-btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">
            <i class="fas fa-edit"></i> Edit Profile
        </button>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($customer['city']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
